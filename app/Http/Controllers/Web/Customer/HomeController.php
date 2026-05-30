<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Customer home — services browse + quick book
     */
    public function index($subdomain)
    {
        $tenant = app('customerTenant');

        $services = Service::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('category')
            ->get()
            ->groupBy('category');

        $staff = Staff::with('user')
            ->where('tenant_id', $tenant->id)
            ->where('is_available', true)
            ->get();

        // Customer ki aaj ki upcoming bookings
        $todayBookings = Appointment::with(['service', 'staff.user'])
            ->where('customer_id', auth()->id())
            ->whereDate('appointment_date', today())
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_time')
            ->get();

        return view('customer.home.index', compact('tenant', 'services', 'staff', 'todayBookings'));
    }

    /**
     * Available slots fetch karo — AJAX
     */
    public function slots(Request $request, $subdomain)
    {
        $tenant = app('customerTenant');

        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:staff,id',
        ]);

        $date = Carbon::parse($request->date);
        $dayKey = strtolower($date->format('D')); // mon, tue...
        $service = Service::where('tenant_id', $tenant->id)->findOrFail($request->service_id);

        $staffQuery = Staff::with('user')
            ->where('tenant_id', $tenant->id)
            ->where('is_available', true);

        if ($request->filled('staff_id')) {
            $staffQuery->where('id', $request->staff_id);
        }

        $staffList = $staffQuery->get();

        $result = [];

        foreach ($staffList as $s) {
            $hours = $s->working_hours[$dayKey] ?? null;

            if (! $hours) {
                continue;
            } // Us din off hai

            [$open, $close] = explode('-', $hours);
            $slotStart = Carbon::parse($request->date.' '.$open);
            $slotEnd = Carbon::parse($request->date.' '.$close);
            $duration = $service->duration_minutes;

            $booked = Appointment::where('staff_id', $s->id)
                ->where('appointment_date', $request->date)
                ->whereNotIn('status', ['cancelled'])
                ->get(['start_time', 'end_time']);

            $slots = [];
            $current = $slotStart->copy();

            while ($current->copy()->addMinutes($duration)->lte($slotEnd)) {
                $sStart = $current->copy();
                $sEnd = $current->copy()->addMinutes($duration);

                $isBooked = $booked->contains(function ($a) use ($sStart, $sEnd) {
                    return $sStart->lt(Carbon::parse($a->end_time)) &&
                           $sEnd->gt(Carbon::parse($a->start_time));
                });

                // Past slot — aaj ka date aur time already guzar gaya?
                $isPast = $date->isToday() && $sStart->lt(Carbon::now()->addMinutes(30));

                $slots[] = [
                    'start' => $sStart->format('H:i'),
                    'end' => $sEnd->format('H:i'),
                    'display' => $sStart->format('h:i A'),
                    'available' => ! $isBooked && ! $isPast,
                ];

                $current->addMinutes($duration);
            }

            $result[] = [
                'staff_id' => $s->id,
                'staff_name' => $s->user?->name,
                'initials' => strtoupper(substr($s->user?->name ?? 'S', 0, 2)),
                'slots' => $slots,
            ];
        }

        return response()->json([
            'service' => $service->name,
            'duration' => $duration,
            'date' => $date->format('d M Y'),
            'data' => $result,
        ]);
    }

    /**
     * Appointment book karo — race condition safe
     */
    public function book(Request $request, $subdomain)
    {
        $tenant = app('customerTenant');

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'required|exists:staff,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $service = Service::where('tenant_id', $tenant->id)->findOrFail($request->service_id);
        $endTime = Carbon::parse($request->start_time)
            ->addMinutes($service->duration_minutes)
            ->format('H:i');

        try {
            $appointment = DB::transaction(function () use ($request, $endTime, $tenant) {

                // Race condition fix — SELECT FOR UPDATE
                $conflict = Appointment::lockForUpdate()
                    ->where('staff_id', $request->staff_id)
                    ->where('appointment_date', $request->appointment_date)
                    ->whereNotIn('status', ['cancelled'])
                    ->where(function ($q) use ($request, $endTime) {
                        $q->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $request->start_time);
                    })
                    ->first();

                if ($conflict) {
                    throw new \Exception('SLOT_TAKEN');
                }

                return Appointment::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => auth()->id(),
                    'staff_id' => $request->staff_id,
                    'service_id' => $request->service_id,
                    'appointment_date' => $request->appointment_date,
                    'start_time' => $request->start_time,
                    'end_time' => $endTime,
                    'status' => 'confirmed',
                    'notes' => $request->notes,
                    'reminder_sent' => false,
                ]);
            });

            return redirect()
                ->route('customer.appointments', $subdomain)
                ->with('success', "Booking confirmed! {$service->name} — ".Carbon::parse($request->start_time)->format('h:i A'));

        } catch (\Exception $e) {
            if ($e->getMessage() === 'SLOT_TAKEN') {
                return back()->with('error', 'Ye slot abhi book ho gaya! Koi aur slot choose karo.');
            }

            return back()->with('error', 'Booking fail ho gayi. Dobara try karo.');
        }
    }
}
