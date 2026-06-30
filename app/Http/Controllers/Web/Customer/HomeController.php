<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\BookAppointmentRequest;
use App\Mail\AppointmentBookedMail;
use App\Models\Appointment;
use App\Models\GalleryImage;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use App\Models\Staff;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    public function __construct(
        private AppointmentService $appointmentService
    ) {}

    public function landing($subdomain)
    {
        $tenant = app('customerTenant');

        $services = Cache::remember(
            "landing_services_{$tenant->id}", 600,
            fn () => Service::where('tenant_id', $tenant->id)->where('is_active', true)->get()
        );

        $staff = Cache::remember(
            "landing_staff_{$tenant->id}", 600,
            fn () => Staff::with('user')->where('tenant_id', $tenant->id)->where('is_available', true)->get()
        );

        $products = Cache::remember(
            "landing_products_{$tenant->id}", 600,
            fn () => Product::where('tenant_id', $tenant->id)->where('is_active', true)->take(4)->get()
        );

        // Stats
        $totalAppointments = Appointment::where('tenant_id', $tenant->id)
            ->whereIn('status', ['completed'])
            ->count();

        $totalStaff = $staff->count();

        // Working hours
        $settings = $tenant->settings ?? [];
        $openTime = $settings['open_time'] ?? '09:00';
        $closeTime = $settings['close_time'] ?? '18:00';

        // Find the next available slot for today
        $tenantTimezone = $settings['timezone'] ?? config('app.timezone', 'UTC');
        $today = Carbon::now($tenantTimezone)->toDateString();
        $nowTime = Carbon::now($tenantTimezone)->format('H:i');

        $nextSlot = null;
        $firstService = $services->first();

        if ($firstService) {
            $slotInterval = $firstService->duration_minutes;
            $current = Carbon::parse($today.' '.$openTime);
            $close = Carbon::parse($today.' '.$closeTime);

            $bookedSlots = Appointment::where('tenant_id', $tenant->id)
                ->whereDate('appointment_date', $today)
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($q) {
                    $q->where('payment_method', '!=', 'razorpay')
                        ->orWhere('payment_status', 'paid');
                })
                ->pluck('start_time')
                ->toArray();

            while ($current->copy()->addMinutes($slotInterval)->lte($close)) {
                $slotTime = $current->format('H:i');
                if ($slotTime > $nowTime && ! in_array($slotTime, $bookedSlots)) {
                    $nextSlot = $current->format('g:i A');
                    break;
                }
                $current->addMinutes($slotInterval);
            }

            // Review statistics
            $totalReviews = Review::where('tenant_id', $tenant->id)
                ->where('status', 'approved')
                ->count();

            $avgRating = Review::where('tenant_id', $tenant->id)
                ->where('status', 'approved')
                ->avg('rating') ?? 0;
        }

        $reviews = Cache::remember(
            "landing_reviews_{$tenant->id}", 600,
            fn () => Review::with('customer')->where('tenant_id', $tenant->id)->where('status', 'approved')->latest()->take(3)->get()
        );

        $gallery = Cache::remember(
            "landing_gallery_{$tenant->id}", 600,
            fn () => GalleryImage::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('sort_order')->take(7)->get()
        );

        return view('customer.landing', compact(
            'tenant', 'subdomain', 'services', 'staff',
            'products', 'totalAppointments', 'totalStaff',
            'openTime', 'closeTime', 'nextSlot', 'totalReviews', 'avgRating', 'reviews', 'gallery'
        ));
    }

    /**
     * Display customer portal entry lounge matching tenant settings.
     */
    public function index($subdomain)
    {
        $tenant = app('customerTenant');
        $customerId = Auth::guard('customer')->id();

        $tenantTimezone = $tenant->settings['timezone'] ?? config('app.timezone', 'UTC');
        $tenantTodayDate = Carbon::now($tenantTimezone)->toDateString();

        $services = Service::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('category')
            ->get()
            ->groupBy('category');

        $staff = Staff::with('user')
            ->where('tenant_id', $tenant->id)
            ->where('is_available', true)
            ->get();

        $todayBookings = Appointment::with(['service', 'staff.user'])
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customerId)
            ->whereDate('appointment_date', $tenantTodayDate)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_time')
            ->get();

        return view('customer.home.index', compact(
            'tenant', 'services', 'staff', 'todayBookings', 'subdomain', 'tenantTodayDate'
        ));
    }

    public function book(BookAppointmentRequest $request, $subdomain)
    {
        $tenant = app('customerTenant');

        $service = Service::where('tenant_id', $tenant->id)->where('is_active', true)->findOrFail($request->service_id);

        if (empty($request->staff_id)) {
            return back()->withInput()->withErrors([
                'staff_id' => 'Please select a staff member to proceed.',
            ]);
        }

        try {
            $appointment = $this->appointmentService->create([
                'tenant_id' => $tenant->id,
                'customer_id' => Auth::guard('customer')->id(),
                'service_id' => $service->id,
                'staff_id' => $request->staff_id,
                'appointment_date' => $request->appointment_date,
                'start_time' => $request->start_time,
                'notes' => $request->notes,
                'status' => $request->payment_method === 'razorpay' ? 'pending' : 'confirmed',
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'razorpay' ? 'pending' : 'not_required',
            ]);
        } catch (\RuntimeException $e) {
            $knownErrors = [
                'STAFF_ALREADY_BOOKED' => 'The selected time slot has already been reserved. Please choose another slot.',
                'STAFF_NOT_WORKING_THIS_DAY' => 'This staff member does not work on the selected day.',
                'SALON_CLOSED_THIS_DAY' => 'The salon is closed on the selected day. Please choose another date.',
                'SLOT_OUTSIDE_WORKING_HOURS' => 'The selected time is outside this staff member\'s working hours.',
                'NO_STAFF_SELECTED' => 'Please select a staff member to proceed.',
                'TENANT_SUBSCRIPTION_EXPIRED' => 'Online booking is currently unavailable. Please contact the salon directly.',
                'PLAN_APPOINTMENT_LIMIT_REACHED' => 'This salon has reached its monthly booking limit. Please contact them directly.',
            ];

            if (isset($knownErrors[$e->getMessage()])) {
                return back()->withInput()->withErrors(['start_time' => $knownErrors[$e->getMessage()]]);
            }

            Log::error('Unexpected booking exception', [
                'message' => $e->getMessage(),
                'tenant_id' => $tenant->id,
                'customer_id' => Auth::guard('customer')->id(),
            ]);

            return back()->withInput()->withErrors(['start_time' => 'Booking failed due to an unexpected error. Please try again.']);
        }

        try {
            Mail::to(Auth::guard('customer')->user()->email)
                ->queue(new AppointmentBookedMail(
                    $appointment->load(['customer', 'service', 'staff.user', 'tenant'])
                ));
        } catch (\Exception $e) {
            Log::error('Notification dispatch failed for appointment ID: '.$appointment->id, [
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('customer.book.confirmed', [$subdomain, $appointment->id])
            ->with('success', 'Your reservation has been successfully confirmed.');
    }

    /**
     * Render verified confirmation summary.
     */
    public function bookingConfirmed($subdomain, $id)
    {
        $tenant = app('customerTenant');

        $appointment = Appointment::with(['service', 'staff.user'])
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', Auth::guard('customer')->id())
            ->findOrFail($id);

        return view('customer.home.confirm', compact('appointment', 'tenant', 'subdomain'));
    }

    public function services($subdomain)
    {
        $tenant = app('customerTenant');
        $services = Service::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get()
            ->groupBy('category');

        return view('customer.Services.index', compact('tenant', 'subdomain', 'services'));
    }

    public function products($subdomain)
    {
        $tenant = app('customerTenant');
        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        return view('customer.products.index', compact('tenant', 'subdomain', 'products'));
    }

    public function gallery($subdomain)
    {
        $tenant = app('customerTenant');
        $gallery = GalleryImage::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('customer.gallery.index', compact('tenant', 'subdomain', 'gallery'));
    }
}
