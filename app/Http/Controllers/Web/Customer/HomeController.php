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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * HomeController
 *
 * Centralized customer engagement matrix orchestrating scheduling lookups and execution pipelines.
 */
class HomeController extends Controller
{
    /**
     * Public landing page — no authentication required.
     * Displays tenant-specific landing page with dynamic information.
     */
    // Class ke andar, methods se pehle add karo
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
            'tenant', 'services', 'staff', 'todayBookings', 'subdomain'
        ));
    }

    /**
     * AJAX Endpoint compiling unreserved time slot allocation sequences.
     */
    public function slots(Request $request, $subdomain)
    {
        $tenant = app('customerTenant');

        $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('tenant_id', $tenant->id)],
            'date' => 'required|date|after_or_equal:today',
            'staff_id' => ['nullable', Rule::exists('staff', 'id')->where('tenant_id', $tenant->id)],
        ]);

        $service = Service::where('tenant_id', $tenant->id)->where('is_active', true)->findOrFail($request->service_id);
        $date = Carbon::parse($request->date);

        $settings = $tenant->settings ?? [];
        $openTime = $settings['open_time'] ?? '09:00';
        $closeTime = $settings['close_time'] ?? '18:00';
        $slotInterval = $service->duration_minutes;

        if ($request->filled('staff_id')) {
            $bookedTimes = Appointment::where('tenant_id', $tenant->id)
                ->whereDate('appointment_date', $date)
                ->where('staff_id', $request->staff_id)
                ->whereNotIn('status', ['cancelled'])
                ->pluck('start_time')
                ->map(fn ($t) => substr($t, 0, 5)) // HH:MM:SS → HH:MM
                ->flip()                            // O(1) lookup ke liye
                ->toArray();

            $eligibleStaffIds = null;
            $busyCountPerSlot = null;
        } else {
            $eligibleStaffIds = Staff::where('tenant_id', $tenant->id)
                ->where('is_available', true)
                ->pluck('id');

            $busyCountPerSlot = Appointment::where('tenant_id', $tenant->id)
                ->whereDate('appointment_date', $date)
                ->whereNotIn('status', ['cancelled'])
                ->whereIn('staff_id', $eligibleStaffIds)
                ->selectRaw('LEFT(start_time, 5) as slot, COUNT(DISTINCT staff_id) as busy_count')
                ->groupBy('slot')
                ->pluck('busy_count', 'slot')
                ->toArray();

            $bookedTimes = null;
        }
        // ───────────────────────────────────────────────────────────

        $slots = [];
        $current = Carbon::parse($date->format('Y-m-d').' '.$openTime);
        $close = Carbon::parse($date->format('Y-m-d').' '.$closeTime);

        while ($current->copy()->addMinutes($slotInterval)->lte($close)) {
            $startTime = $current->format('H:i');
            $endTime = $current->copy()->addMinutes($slotInterval)->format('H:i');

            // In-memory check — 0 DB queries inside loop
            if ($eligibleStaffIds === null) {
                $isBooked = isset($bookedTimes[$startTime]);
            } else {
                $busyCount = $busyCountPerSlot[$startTime] ?? 0;
                $isBooked = $eligibleStaffIds->isEmpty() || $busyCount >= $eligibleStaffIds->count();
            }

            if (! $isBooked) {
                $slots[] = [
                    'time' => $current->format('g:i A'),
                    'value' => $startTime,
                ];
            }

            $current->addMinutes($slotInterval);
        }

        return response()->json(['slots' => $slots]);
    }

    /**
     * Enforce atomic reservation transactional writes, preventing double bookings.
     */
    public function book(BookAppointmentRequest $request, $subdomain)
    {
        $tenant = app('customerTenant');

        $service = Service::where('tenant_id', $tenant->id)->where('is_active', true)->findOrFail($request->service_id);

        try {
            $appointment = $this->appointmentService->create([
                'tenant_id' => $tenant->id,
                'customer_id' => Auth::guard('customer')->id(),
                'service_id' => $service->id,
                'staff_id' => $request->staff_id,
                'appointment_date' => $request->appointment_date,
                'start_time' => $request->start_time,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'razorpay' ? 'pending' : 'not_required',
            ]);
        } catch (\RuntimeException $e) {
            $message = match ($e->getMessage()) {
                'STAFF_ALREADY_BOOKED' => 'The selected time slot has already been reserved. Please choose another slot.',
                'TENANT_SUBSCRIPTION_EXPIRED' => 'Online booking is currently unavailable. Please contact the salon directly.',
                'PLAN_APPOINTMENT_LIMIT_REACHED' => 'This salon has reached its monthly booking limit. Please contact them directly.',
                default => 'Booking failed. Please try again.',
            };

            return back()->withInput()->withErrors(['start_time' => $message]);
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
