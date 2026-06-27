<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * SuperAdmin Appointments Controller
 * Monitor all appointments across all tenants.
 *
 * File: app/Http/Controllers/SuperAdmin/AppointmentMonitorController.php
 */
class AppointmentMonitorController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['tenant', 'customer', 'staff.user', 'service'])
            ->latest('appointment_date');

        // ── Filters ────────────────────────────────────────────────
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->whereHas('customer', fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
            );
        }

        $appointments = $query->paginate(10)->withQueryString();

        // ── Status Stats ───────────────────────────────────────────
        $stats = [
            'total' => Appointment::count(),
            'today' => Appointment::whereDate('appointment_date', today())->count(),
            'pending' => Appointment::whereIn('status', ['pending', 'confirmed'])->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'cancelled' => Appointment::where('status', 'cancelled')->count(),
            'revenue' => Appointment::where('status', 'completed')->sum('amount'),
        ];

        // ── Tenant list for filter dropdown ───────────────────────
        $tenants = Tenant::orderBy('name')->get(['id', 'name', 'subdomain']);

        return view('superadmin.appointments.index', compact(
            'appointments', 'stats', 'tenants'
        ));
    }
}
