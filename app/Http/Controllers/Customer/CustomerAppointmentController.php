<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index($subdomain)
    {
        $customerId = Auth::guard('customer')->id();
        $tenant = app('customerTenant');

        $appointments = Appointment::with(['service', 'staff.user'])
            ->where('customer_id', $customerId)
            ->where('tenant_id', $tenant->id)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        $stats = [
            'total' => Appointment::where('customer_id', $customerId)->where('tenant_id', $tenant->id)->count(),
            'upcoming' => Appointment::where('customer_id', $customerId)->where('tenant_id', $tenant->id)
                ->where('appointment_date', '>=', today())
                ->whereNotIn('status', ['cancelled', 'completed'])->count(),
            'completed' => Appointment::where('customer_id', $customerId)->where('tenant_id', $tenant->id)
                ->where('status', 'completed')->count(),
            'cancelled' => Appointment::where('customer_id', $customerId)->where('tenant_id', $tenant->id)
                ->where('status', 'cancelled')->count(),
        ];

        return view('customer.appointments.index', compact(
            'appointments', 'stats', 'tenant', 'subdomain'
        ));
    }

    public function cancel(Request $request, $subdomain, $id)
    {
        $customerId = Auth::guard('customer')->id();
        $tenant = app('customerTenant');

        // Validate both customer_id and tenant_id to prevent IDOR
        $appointment = Appointment::where('customer_id', $customerId)
            ->where('tenant_id', $tenant->id)
            ->findOrFail($id);

        if ($appointment->status === 'completed') {
            return back()->with('error', 'Completed appointments cannot be cancelled.');
        }

        if ($appointment->status === 'cancelled') {
            return back()->with('error', 'This appointment has already been cancelled.');
        }

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancelled successfully.');
    }
}
