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
    // Customer ki saari bookings
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

    // Booking karo — RACE CONDITION yahan handle hogi
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

        $service = Service::findOrFail($request->service_id);
        $endTime = Carbon::parse($request->start_time)
            ->addMinutes($service->duration_minutes)
            ->format('H:i');

        try {
            $appointment = DB::transaction(function () use ($request, $endTime) {

                /*
                |-----------------------------------------------
                | RACE CONDITION FIX — SELECT FOR UPDATE
                |-----------------------------------------------
                | lockForUpdate() — ye row ko lock kar deta hai
                | Matlab ek saath 2 log same slot book karein
                | toh sirf ek ka transaction complete hoga
                | dusre ko wait karna padega — phir conflict milega
                |-----------------------------------------------
                */
                $conflict = Appointment::lockForUpdate()
                    ->where('staff_id', $request->staff_id)
                    ->where('appointment_date', $request->appointment_date)
                    ->whereNotIn('status', ['cancelled'])
                    ->where(function ($query) use ($request, $endTime) {
                        // Overlap check — koi bhi existing booking
                        // is naye slot se overlap karti hai?
                        $query->where(function ($q) use ($request, $endTime) {
                            $q->where('start_time', '<', $endTime)
                                ->where('end_time', '>', $request->start_time);
                        });
                    })
                    ->first();

                // Conflict mila — slot already booked hai
                if ($conflict) {
                    throw new \Exception('SLOT_TAKEN');
                }

                // Slot available hai — booking create karo
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

            // Slot taken error
            if ($e->getMessage() === 'SLOT_TAKEN') {
                return response()->json([
                    'message' => 'Sorry! Ye slot already book ho gaya. Koi aur slot choose karo.',
                ], 409); // 409 = Conflict
            }

            // Koi aur error
            return response()->json([
                'message' => 'Booking failed. Dobara try karo.',
            ], 500);
        }
    }

    // Booking cancel karo
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
                'message' => 'Appointment not found',
            ], 404);
        }

        // Already completed hai toh cancel nahi hoga
        if ($appointment->status === 'completed') {
            return response()->json([
                'message' => 'Completed appointment cancel nahi ho sakta.',
            ], 400);
        }

        // Already cancelled hai
        if ($appointment->status === 'cancelled') {
            return response()->json([
                'message' => 'Ye appointment pehle se cancelled hai.',
            ], 400);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Appointment cancelled successfully',
            'data' => $appointment,
        ]);
    }
}
