<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentService $appointmentService
    ) {}

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

    public function store(Request $request)
    {
        $tenant = app('currentTenant');
        $tenantTz = $tenant->settings['timezone'] ?? config('app.timezone', 'UTC');

        $tenantToday = Carbon::now($tenantTz)->toDateString();

        $request->validate([
            'service_id' => [
                'required',
                Rule::exists('services', 'id')
                    ->where('tenant_id', $tenant->id)
                    ->where('is_active', true),
            ],
            'staff_id' => [
                'required',
                Rule::exists('staff', 'id')
                    ->where('tenant_id', $tenant->id),
            ],
            'appointment_date' => ['required', 'date', 'after_or_equal:'.$tenantToday],
            'start_time' => 'required|date_format:H:i',
            'payment_method' => 'nullable|in:cash,razorpay',
            'notes' => 'nullable|string|max:500',
        ]);

        $paymentMethod = $request->payment_method ?? 'cash';

        try {

            $appointment = $this->appointmentService->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $request->user()->id,
                'service_id' => $request->service_id,
                'staff_id' => $request->staff_id,
                'appointment_date' => $request->appointment_date,
                'start_time' => $request->start_time,
                'notes' => $request->notes,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentMethod === 'razorpay' ? 'pending' : 'not_required',
                'status' => $paymentMethod === 'razorpay' ? 'pending' : 'confirmed',
            ]);

            return response()->json([
                'message' => 'Appointment booked successfully!',
                'data' => $appointment->load(['service', 'staff.user']),
            ], 201);

        } catch (\RuntimeException $e) {
            $errorMessages = [
                'STAFF_ALREADY_BOOKED' => ['message' => 'This time slot is already booked. Please choose a different time.', 'code' => 409],
                'STAFF_NOT_WORKING_THIS_DAY' => ['message' => 'This staff member does not work on the selected day.', 'code' => 422],
                'SLOT_OUTSIDE_WORKING_HOURS' => ['message' => 'The selected time is outside working hours.', 'code' => 422],
                'TENANT_SUBSCRIPTION_EXPIRED' => ['message' => 'Online booking is currently unavailable.', 'code' => 403],
                'PLAN_APPOINTMENT_LIMIT_REACHED' => ['message' => 'This salon has reached its monthly booking limit.', 'code' => 403],
            ];

            if (isset($errorMessages[$e->getMessage()])) {
                return response()->json([
                    'message' => $errorMessages[$e->getMessage()]['message'],
                ], $errorMessages[$e->getMessage()]['code']);
            }

            return response()->json([
                'message' => 'Booking failed. Please try again later.',
            ], 500);
        }
    }

    // Cancel an existing appointment
    public function cancel($id)
    {
        $tenant = app('currentTenant');

        $appointment = Appointment::where('tenant_id', $tenant->id)
            ->where('customer_id', auth()->id())
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

        $tenantTz = $tenant->settings['timezone'] ?? config('app.timezone', 'UTC');

        $appointmentDateTime = Carbon::parse(
            $appointment->appointment_date->format('Y-m-d').' '.$appointment->start_time,
            $tenantTz
        );

        if (Carbon::now($tenantTz)->diffInHours($appointmentDateTime, false) < 2) {
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
