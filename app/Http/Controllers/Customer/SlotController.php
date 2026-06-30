<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SlotController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('customerTenant');
        $tenantId = $tenant->id;

        $tenantTz = $tenant->settings['timezone'] ?? config('app.timezone', 'UTC');
        $tenantTodayDate = Carbon::now($tenantTz)->toDateString();

        $request->validate([
            'date' => [
                'required',
                'date',
                'after_or_equal:'.$tenantTodayDate,
            ],
            'service_id' => [
                'required',

                Rule::exists('services', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)
                        ->where('is_active', true);
                }),
            ],
            'staff_id' => [
                'nullable',
                Rule::exists('staff', 'id')->where('tenant_id', $tenantId),
            ],
        ]);

        $date = Carbon::parse($request->date, $tenantTz);
        $dayName = strtolower($date->format('D')); // mon, tue, wed...

        $tenantWorkingHours = $tenant->settings['working_hours'][$dayName] ?? null;

        if (empty($tenantWorkingHours)) {
            return response()->json([
                'message' => 'The salon is closed on this day.',
                'data' => [],
            ]);
        }

        [$tenantOpen, $tenantClose] = explode('-', $tenantWorkingHours);
        $tenantOpenTime = Carbon::parse($date->format('Y-m-d').' '.$tenantOpen, $tenantTz);
        $tenantCloseTime = Carbon::parse($date->format('Y-m-d').' '.$tenantClose, $tenantTz);

        $service = Service::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->findOrFail($request->service_id);
        $duration = $service->duration_minutes;

        // ── Staff list ────────────────────────────────────────────────
        $staffQuery = Staff::with('user')
            ->where('tenant_id', $tenantId)
            ->where('is_available', true);

        if ($request->filled('staff_id')) {
            $staffQuery->where('id', $request->staff_id);
        }

        $staffList = $staffQuery->get();

        if ($staffList->isEmpty()) {
            return response()->json([
                'message' => 'No staff available for this date.',
                'data' => [],
            ]);
        }

        $availableSlots = [];

        foreach ($staffList as $staff) {

            // ── Working hours check ───────────────────────────────────

            $workingHours = $staff->working_hours ?? [];

            // Staff is off on this day
            if (empty($workingHours[$dayName])) {
                continue;
            }

            [$workStart, $workEnd] = explode('-', $workingHours[$dayName]);

            $shiftStart = Carbon::parse($date->format('Y-m-d').' '.$workStart, $tenantTz);
            $shiftEnd = Carbon::parse($date->format('Y-m-d').' '.$workEnd, $tenantTz);

            $shiftStart = $shiftStart->max($tenantOpenTime);
            $shiftEnd = $shiftEnd->min($tenantCloseTime);

            if ($shiftStart->gte($shiftEnd)) {
                continue;
            }

            // ── Booked appointments fetch ─────────────────────────────

            $bookedAppointments = Appointment::where('tenant_id', $tenantId)
                ->where('staff_id', $staff->id)
                ->whereDate('appointment_date', $date->toDateString())
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($q) {
                    $q->where('payment_method', '!=', 'razorpay')
                        ->orWhere('payment_status', 'paid');
                })
                ->get(['start_time', 'end_time']);

            // ── Slot generation ───────────────────────────────────────
            $slots = [];
            $current = $shiftStart->copy();

            while ($current->copy()->addMinutes($duration)->lte($shiftEnd)) {

                $thisSlotStart = $current->copy();
                $thisSlotEnd = $current->copy()->addMinutes($duration);

                // Past slot check — today ke liye

                $isPast = $date->isToday()
                    && $thisSlotStart->lt(Carbon::now($tenantTz));

                if (! $isPast) {

                    $isBooked = $bookedAppointments->contains(function ($appt) use ($thisSlotStart, $thisSlotEnd, $date, $tenantTz) {
                        $apptStart = Carbon::parse($date->format('Y-m-d').' '.$appt->start_time, $tenantTz);
                        $apptEnd = Carbon::parse($date->format('Y-m-d').' '.$appt->end_time, $tenantTz);

                        return $thisSlotStart->lt($apptEnd) && $thisSlotEnd->gt($apptStart);
                    });

                    if (! $isBooked) {
                        $slots[] = [
                            'start' => $thisSlotStart->format('H:i'),
                            'end' => $thisSlotEnd->format('H:i'),
                            'display' => $thisSlotStart->format('h:i A'),
                            'available' => true,
                        ];
                    }
                }

                $current->addMinutes($duration);
            }

            if (! empty($slots)) {
                $availableSlots[] = [
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->user->name,
                    'slots' => $slots,
                ];
            }
        }

        return response()->json([
            'message' => 'Slots fetched successfully.',
            'date' => $request->date,
            'service' => $service->name,
            'duration' => $duration.' minutes',
            'data' => $availableSlots,
        ]);
    }
}
