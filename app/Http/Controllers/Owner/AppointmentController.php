<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Fetch all appointments scheduled for today based on tenant timezone.
     */
    public function today()
    {
        $tenant = app('currentTenant');

        $tenantTimezone = $tenant->settings['timezone'] ?? config('app.timezone', 'UTC');
        $tenantToday = Carbon::now($tenantTimezone)->toDateString();

        $appointments = Appointment::with(['customer', 'staff.user', 'service'])
            ->where('tenant_id', $tenant->id) // Explicit defense-in-depth scoping rule
            ->whereDate('appointment_date', $tenantToday)
            ->orderBy('start_time', 'asc')
            ->paginate(20);

        return response()->json([
            'message' => 'Today\'s appointments fetched successfully.',
            'date' => Carbon::parse($tenantToday)->format('d M Y'),
            'total' => $appointments->total(),
            'data' => $appointments->items(),
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
            ],
        ]);
    }

    /**
     * Fetch all upcoming or filtered appointments under strict tenant scope.
     */
    public function index(Request $request)
    {
        $tenantId = app('currentTenant')->id;

        $query = Appointment::with([
            'customer',
            'staff.user',
            'service',
        ])->where('tenant_id', $tenantId);

        // Date filter — optional
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        // Status filter — optional
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Staff filter — optional
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Bound the result set: without this, a salon with years of history and
        // no filters applied would load every appointment row in one request.
        $perPage = min((int) $request->input('per_page', 50), 100);

        $appointments = $query->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Appointments fetched successfully.',
            'total' => $appointments->total(),
            'data' => $appointments->items(),
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
            ],
        ]);
    }

    /**
     * Secure status mutation pipeline protecting against cross-tenant IDOR access.
     */
    public function updateStatus(Request $request, $id)
    {
        $tenantId = app('currentTenant')->id;

        $request->validate([
            'status' => 'required|in:confirmed,checked_in,completed,cancelled,no_show',
        ]);

        $appointment = Appointment::with(['customer', 'staff.user', 'service'])
            ->where('tenant_id', $tenantId)
            ->find($id);

        if (! $appointment) {
            return response()->json([
                'message' => 'Appointment not found or access unauthorized.',
            ], 404);
        }

        // State machine integrity check: terminal states must be locked
        if (in_array($appointment->status, ['cancelled', 'completed', 'no_show'])) {
            return response()->json([
                'message' => 'Closed, cancelled, or no-show appointments cannot be modified.',
            ], 400);
        }

        $appointment->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Appointment status updated to '.$request->status,
            'data' => $appointment,
        ]);
    }
}
