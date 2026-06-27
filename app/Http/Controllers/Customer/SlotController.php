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
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'required|exists:services,id',
            'staff_id' => [
                'nullable',
                Rule::exists('staff', 'id')
                    ->where(
                        'tenant_id',
                        app('customerTenant')->id
                    ),
            ],
        ]);

        $date = Carbon::parse($request->date);
        $dayName = strtolower($date->format('D')); // e.g., "mon", "tue"
        $service = Service::where(
            'tenant_id',
            app('customerTenant')->id
        )->findOrFail($request->service_id);

        // Retrieve available staff list
        if ($request->staff_id) {
            $staffList = Staff::with('user')
                ->where('tenant_id', app('customerTenant')->id)
                ->where('id', $request->staff_id)
                ->where('is_available', true)
                ->get();
        } else {
            $staffList = Staff::with('user')
                ->where('tenant_id', app('customerTenant')->id)
                ->where('is_available', true)
                ->get();
        }

        if ($staffList->isEmpty()) {
            return response()->json([
                'message' => 'No staff available for this date.',
                'slots' => [],
            ]);
        }

        $availableSlots = [];

        foreach ($staffList as $staff) {
            // Check staff working hours
            $workingHours = $staff->working_hours;

            // Check if staff is off on this day
            if (empty($workingHours[$dayName])) {
                continue;
            }

            // Parse working hours format "09:00-20:00"
            [$startTime, $endTime] = explode('-', $workingHours[$dayName]);

            $slotStart = Carbon::parse($request->date.' '.$startTime);
            $slotEnd = Carbon::parse($request->date.' '.$endTime);
            $slotDuration = $service->duration_minutes;

            // Retrieve all existing appointments for the staff on this day
            $bookedAppointments = Appointment::where('staff_id', $staff->id)
                ->where('appointment_date', $request->date)
                ->whereNotIn('status', ['cancelled'])
                ->get(['start_time', 'end_time']);

            // Generate time slots based on service duration
            $slots = [];
            $current = $slotStart->copy();

            while ($current->copy()->addMinutes($slotDuration)->lte($slotEnd)) {
                $thisSlotStart = $current->copy();
                $thisSlotEnd = $current->copy()->addMinutes($slotDuration);

                // Check for overlapping appointments
                $isBooked = $bookedAppointments->contains(function ($appt) use ($thisSlotStart, $thisSlotEnd) {
                    $apptStart = Carbon::parse($appt->start_time);
                    $apptEnd = Carbon::parse($appt->end_time);

                    return $thisSlotStart->lt($apptEnd) && $thisSlotEnd->gt($apptStart);
                });
                $isPast = $date->isToday() && $thisSlotStart->lt(Carbon::now(config('app.timezone')));

                $slots[] = [
                    'start' => $thisSlotStart->format('H:i'),
                    'end' => $thisSlotEnd->format('H:i'),
                    'display' => $thisSlotStart->format('h:i A'),
                    'available' => ! $isBooked && ! $isPast,
                ];

                $current->addMinutes($slotDuration);
            }

            $availableSlots[] = [
                'staff_id' => $staff->id,
                'staff_name' => $staff->user->name,
                'slots' => $slots,
            ];
        }

        return response()->json([
            'message' => 'Slots fetched successfully.',
            'date' => $request->date,
            'service' => $service->name,
            'duration' => $service->duration_minutes.' minutes',
            'data' => $availableSlots,
        ]);
    }
}
