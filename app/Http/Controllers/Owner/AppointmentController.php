<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // Aaj ki saari bookings
    public function today()
    {
        $appointments = Appointment::with(['customer', 'staff.user', 'service'])
            ->where(
                'tenant_id',
                app('currentTenant')->id
            )
            ->whereDate('appointment_date', Carbon::today())
            ->orderBy('start_time', 'asc')
            ->paginate(20);

        return response()->json([
            'message' => 'Today\'s appointments',
            'date' => Carbon::today()->format('d M Y'),
            'total' => $appointments->count(),
            'data' => $appointments,
        ]);
    }

    // Saari upcoming bookings
    public function index(Request $request)
    {
        $query = Appointment::with([
            'customer',
            'staff.user',
            'service',
        ])->where(
            'tenant_id',
            app('currentTenant')->id
        );

        // Date filter — optional
        if ($request->has('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        // Status filter — optional
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Staff filter — optional
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        $appointments = $query->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'message' => 'Appointments fetched successfully',
            'total' => $appointments->count(),
            'data' => $appointments,
        ]);
    }

    // Appointment status update karo
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:confirmed,completed,cancelled',
        ]);

        $appointment = Appointment::with(['customer', 'staff.user', 'service'])
            ->find($id);

        if (! $appointment) {
            return response()->json([
                'message' => 'Appointment not found',
            ], 404);
        }

        // Cancelled appointment ko update nahi kar sakte
       if (in_array($appointment->status, ['cancelled', 'completed'])) {
    return response()->json([
        'message' => 'Completed ya cancelled appointment ko update nahi kar sakte.'
    ], 400);
}

        $appointment->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status updated to '.$request->status,
            'data' => $appointment,
        ]);
    }
}
