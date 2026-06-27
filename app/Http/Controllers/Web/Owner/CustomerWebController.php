<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerWebController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('currentTenant');
        $search = trim((string) $request->query('search', ''));

        $customersQuery = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'customer'))
            ->withCount([
                'appointments as visit_count' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->withSum([
                'appointments as lifetime_revenue' => fn ($q) => $q->where('status', 'completed'),
            ], 'amount')
            ->withMax(['appointments as last_visit_date' => fn ($q) => $q->where('status', 'completed')], 'appointment_date');

        if ($search !== '') {
            $customersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $customersQuery
            ->orderByDesc('lifetime_revenue')
            ->paginate(15)
            ->withQueryString();

        $stats = Cache::remember("customer_stats_{$tenant->id}", 300, function () use ($tenant) {
            $base = User::where('tenant_id', $tenant->id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'customer'));

            return [
                'total_customers' => (clone $base)->count(),
                'repeat_customers' => (clone $base)->whereHas('appointments', function ($q) {
                    $q->where('status', 'completed');
                }, '>=', 2)->count(),
                'total_lifetime_revenue' => Appointment::where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->sum('amount'),
            ];
        });

        return view('owner.customers.index', compact('customers', 'stats', 'search'));
    }

    public function show($id)
    {
        $tenant = app('currentTenant');

        $customer = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'customer'))
            ->findOrFail($id);

        $appointments = Appointment::with(['staff.user', 'service'])
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate(10);

        $completedAppointments = Appointment::where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->where('status', 'completed');

        $stats = [
            'total_visits' => (clone $completedAppointments)->count(),
            'lifetime_revenue' => (clone $completedAppointments)->sum('amount'),
            'no_show_count' => Appointment::where('tenant_id', $tenant->id)
                ->where('customer_id', $customer->id)
                ->where('status', 'no_show')
                ->count(),
            'cancelled_count' => Appointment::where('tenant_id', $tenant->id)
                ->where('customer_id', $customer->id)
                ->where('status', 'cancelled')
                ->count(),
            'first_visit_date' => Appointment::where('tenant_id', $tenant->id)
                ->where('customer_id', $customer->id)
                ->oldest('appointment_date')
                ->value('appointment_date'),
            'last_visit_date' => Appointment::where('tenant_id', $tenant->id)
                ->where('customer_id', $customer->id)
                ->where('status', 'completed')
                ->latest('appointment_date')
                ->value('appointment_date'),
        ];

        $stats['avg_spend'] = $stats['total_visits'] > 0
            ? round($stats['lifetime_revenue'] / $stats['total_visits'])
            : 0;

        $preferredStaff = Appointment::with('staff.user')
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->selectRaw('staff_id, COUNT(*) as visit_count')
            ->groupBy('staff_id')
            ->orderByDesc('visit_count')
            ->first();

        $preferredService = Appointment::with('service')
            ->where('tenant_id', $tenant->id)
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->selectRaw('service_id, COUNT(*) as booking_count')
            ->groupBy('service_id')
            ->orderByDesc('booking_count')
            ->first();

        return view('owner.customers.show', compact(
            'customer',
            'appointments',
            'stats',
            'preferredStaff',
            'preferredService'
        ));
    }
}
