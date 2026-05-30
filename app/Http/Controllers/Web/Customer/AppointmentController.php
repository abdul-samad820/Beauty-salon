<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Customer ki saari appointments
     */
    public function index($subdomain)
    {
        $appointments = Appointment::with(['service', 'staff.user'])
            ->where('customer_id', auth()->id())
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        $stats = [
            'total' => Appointment::where('customer_id', auth()->id())->count(),
            'upcoming' => Appointment::where('customer_id', auth()->id())
                ->where('appointment_date', '>=', today())
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->count(),
            'completed' => Appointment::where('customer_id', auth()->id())
                ->where('status', 'completed')->count(),
            'cancelled' => Appointment::where('customer_id', auth()->id())
                ->where('status', 'cancelled')->count(),
        ];

        $tenant = app('customerTenant');

        return view('customer.appointments.index', compact('appointments', 'stats', 'tenant', 'subdomain'));
    }

    /**
     * Cancel karo — sirf future appointments
     */
    public function cancel(Request $request, $subdomain, $id)
    {
        $appointment = Appointment::where('customer_id', auth()->id())
            ->findOrFail($id);

        if ($appointment->status === 'completed') {
            return back()->with('error', 'Completed appointment cancel nahi ho sakta.');
        }

        if ($appointment->status === 'cancelled') {
            return back()->with('error', 'Ye appointment pehle se cancelled hai.');
        }

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancel ho gayi.');
    }
}
