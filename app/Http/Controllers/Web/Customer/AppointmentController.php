<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(string $subdomain): View
    {
        $customerId = auth('customer')->id();

        $tenant = app('customerTenant');
        $tenantId = $tenant->id;

        $status = request('status', 'all');

        // High-Performance Paginated Ledger with Eager Loading
        $appointments = Appointment::with(['service', 'staff.user'])
            ->where('tenant_id', $tenantId) // Explicit defense-in-depth scoping
            ->where('customer_id', $customerId)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        // Optimized Single Database Multi-Aggregation Aggregator
        $statsRaw = Appointment::where('tenant_id', $tenantId) // Explicit check injected
            ->where('customer_id', $customerId)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN appointment_date >= CURRENT_DATE AND status NOT IN ('cancelled', 'completed') THEN 1 ELSE 0 END) as upcoming"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            )
            ->first();

        $stats = [
            'total' => (int) ($statsRaw->total ?? 0),
            'upcoming' => (int) ($statsRaw->upcoming ?? 0),
            'completed' => (int) ($statsRaw->completed ?? 0),
            'cancelled' => (int) ($statsRaw->cancelled ?? 0),
        ];

        $pendingCount = (int) ($statsRaw->pending ?? 0);

        return view('customer.appointments.index', compact('appointments', 'stats', 'tenant', 'subdomain', 'pendingCount'));
    }

    public function cancel(Request $request, string $subdomain, int $id): RedirectResponse
    {
        $tenantId = app('customerTenant')->id;
        $appointment = Appointment::where('tenant_id', $tenantId)
            ->where('customer_id', auth('customer')->id())
            ->findOrFail($id);

        // Fail-Safe State Guard Rules Validation Block
        if ($appointment->status === 'completed') {
            return back()->with('error', 'Action Denied: Completed appointments cannot be cancelled.');
        }

        if ($appointment->status === 'cancelled') {
            return back()->with('error', 'Action Denied: This appointment has already been cancelled.');
        }

        $appointmentDate = Carbon::parse($appointment->appointment_date);
        if ($appointmentDate->isPast() && ! $appointmentDate->isToday()) {
            return back()->with('error', 'Action Denied: Past appointments cannot be modified or altered.');
        }
        //  2-hour cancellation window enforce karo
        $appointmentDateTime = Carbon::parse(
            $appointment->appointment_date->format('Y-m-d').' '.$appointment->start_time
        );

        if (Carbon::now()->diffInHours($appointmentDateTime, false) < 2) {
            return back()->with('error', 'Action Denied: Appointments cannot be cancelled within 2 hours of the scheduled time.');
        }
        // Secure Mutation Execution Block
        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Success: Your appointment has been successfully cancelled.');
    }
}
