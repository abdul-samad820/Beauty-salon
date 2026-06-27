<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    // Retrieve all bookings for the authenticated customer
    public function index(Request $request)
    {
        $appointments = Appointment::with(['service', 'staff.user'])
            ->where('customer_id', $request->user()->id)
            ->orderBy('appointment_date', 'desc')
            ->paginate(20);

        return response()->json([
            'message' => 'Appointments fetched successfully',
            'data' => $appointments,
        ]);
    }

    // Store a new appointment with race condition handling
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => [
                'required',
                Rule::exists('services', 'id')
                    ->where(
                        'tenant_id',
                        app('currentTenant')->id
                    ),
            ],
            'staff_id' => [
                'required',
                Rule::exists('staff', 'id')
                    ->where(
                        'tenant_id',
                        app('currentTenant')->id
                    ),
            ],
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $service = Service::where(
            'tenant_id',
            app('currentTenant')->id
        )->findOrFail($request->service_id);

        $endTime = Carbon::parse($request->start_time)
            ->addMinutes($service->duration_minutes)
            ->format('H:i');

        try {
            $appointment = DB::transaction(function () use ($request, $endTime) {

                $conflict = Appointment::lockForUpdate()
                    ->where('staff_id', $request->staff_id)
                    ->where('appointment_date', $request->appointment_date)
                    ->whereNotIn('status', ['cancelled'])
                    ->where(function ($query) use ($request, $endTime) {
                        // Check for overlapping existing bookings
                        // that conflict with the requested time slot
                        $query->where(function ($q) use ($request, $endTime) {
                            $q->where('start_time', '<', $endTime)
                                ->where('end_time', '>', $request->start_time);
                        });
                    })
                    ->first();

                // Conflict found: The slot is already booked
                if ($conflict) {
                    throw new \Exception('SLOT_TAKEN');
                }

                // Slot is available: Proceed with booking creation
                $appointment = Appointment::create([
                    'tenant_id' => app('currentTenant')->id,
                    'customer_id' => request()->user()->id,
                    'staff_id' => request()->staff_id,
                    'service_id' => request()->service_id,
                    'appointment_date' => request()->appointment_date,
                    'start_time' => request()->start_time,
                    'end_time' => $endTime,
                    'status' => 'pending',
                    'notes' => request()->notes,
                    'reminder_sent' => false,
                ]);

                return $appointment->load(['service', 'staff.user']);
            });

            return response()->json([
                'message' => 'Appointment booked successfully!',
                'data' => $appointment,
            ], 201);

        } catch (\Exception $e) {

            // Handle slot taken error
            if ($e->getMessage() === 'SLOT_TAKEN') {
                return response()->json([
                    'message' => 'This time slot is already booked. Please choose a different time.',
                ], 409); // 409 = Conflict
            }

            // Handle general errors
            return response()->json([
                'message' => 'Booking failed. Please try again later.',
            ], 500);
        }
    }

    // Cancel an existing appointment
    public function cancel($id)
    {
        $appointment = Appointment::where(
            'tenant_id',
            app('currentTenant')->id
        )
            ->where(
                'customer_id',
                auth()->id()
            )
            ->where('id', $id)
            ->first();

        if (! $appointment) {
            return response()->json([
                'message' => 'Appointment not found.',
            ], 404);
        }

        // Cannot cancel an already completed appointment
        if ($appointment->status === 'completed') {
            return response()->json([
                'message' => 'A completed appointment cannot be cancelled.',
            ], 400);
        }

        // Prevent cancelling an already cancelled appointment
        if ($appointment->status === 'cancelled') {
            return response()->json([
                'message' => 'This appointment is already cancelled.',
            ], 400);
        }

        $appointmentDateTime = Carbon::parse(
            Carbon::parse($appointment->appointment_date)->toDateString().' '.$appointment->start_time
        );

        if (Carbon::now()->diffInHours($appointmentDateTime, false) < 2) {
            return response()->json([
                'message' => 'Appointments cannot be cancelled within 2 hours of the scheduled time.',
            ], 422);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Appointment cancelled successfully.',
            'data' => $appointment,
        ]);
    }
}
