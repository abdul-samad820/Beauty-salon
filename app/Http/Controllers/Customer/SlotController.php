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
                        app('currentTenant')->id
                    ),
            ],
        ]);

        $date = Carbon::parse($request->date);
        $dayName = strtolower($date->format('D')); // "mon", "tue" etc.
        $service = Service::where(
            'tenant_id',
            app('currentTenant')->id
        )->findOrFail($request->service_id);

        // Staff select kiya hai? Warna tenant ke sab available staff
        if ($request->staff_id) {
            // Ye nai line
            $staffList = Staff::with('user')
                ->where('tenant_id', app('currentTenant')->id)
                ->where('id', $request->staff_id)
                ->where('is_available', true)
                ->get();
        } else {

            $staffList = Staff::with('user')
                ->where('tenant_id', app('currentTenant')->id)
                ->where('is_available', true)->get();
        }

        if ($staffList->isEmpty()) {
            return response()->json([
                'message' => 'Koi staff available nahi is date pe',
                'slots' => [],
            ]);
        }

        $availableSlots = [];

        foreach ($staffList as $staff) {

            // Staff ke working hours check karo
            $workingHours = $staff->working_hours;

            // Us din staff kaam karta hai?
            if (empty($workingHours[$dayName])) {
                continue; // Is staff ka us din off hai
            }

            // Working hours parse karo — "09:00-20:00"
            [$startTime, $endTime] = explode('-', $workingHours[$dayName]);

            $slotStart = Carbon::parse($request->date.' '.$startTime);
            $slotEnd = Carbon::parse($request->date.' '.$endTime);
            $slotDuration = $service->duration_minutes; // Service kitne minute ki hai

            // Us staff ki us din ki saari bookings
            $bookedAppointments = Appointment::where('staff_id', $staff->id)
                ->where('appointment_date', $request->date)
                ->whereNotIn('status', ['cancelled'])
                ->get(['start_time', 'end_time']);

            // Slots generate karo — har slot service duration ka hoga
            $slots = [];
            $current = $slotStart->copy();

            while ($current->copy()->addMinutes($slotDuration)->lte($slotEnd)) {
                $thisSlotStart = $current->copy();
                $thisSlotEnd = $current->copy()->addMinutes($slotDuration);

                // Ye slot kisi booked appointment se overlap karta hai?
                $isBooked = $bookedAppointments->contains(function ($appt) use ($thisSlotStart, $thisSlotEnd) {
                    $apptStart = Carbon::parse($appt->start_time);
                    $apptEnd = Carbon::parse($appt->end_time);

                    // Overlap check karo
                    return $thisSlotStart->lt($apptEnd) && $thisSlotEnd->gt($apptStart);
                });

                $slots[] = [
                    'start_time' => $thisSlotStart->format('H:i'),
                    'end_time' => $thisSlotEnd->format('H:i'),
                    'available' => ! $isBooked,
                ];

                // Agli slot pe jao
                $current->addMinutes($slotDuration);
            }

            $availableSlots[] = [
                'staff_id' => $staff->id,
                'staff_name' => $staff->user->name,
                'slots' => $slots,
            ];
        }

        return response()->json([
            'message' => 'Slots fetched successfully',
            'date' => $request->date,
            'service' => $service->name,
            'duration' => $service->duration_minutes.' minutes',
            'data' => $availableSlots,
        ]);
    }
}
