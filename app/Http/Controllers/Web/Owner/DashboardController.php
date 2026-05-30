<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Product;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');
        $today = Carbon::today();
        $month = Carbon::now();

        // ── KPI STATS ──
        $stats = [
            'today_bookings' => Appointment::where('tenant_id', $tenant->id)
                ->whereDate('appointment_date', $today)
                ->whereNotIn('status', ['cancelled'])
                ->count(),

            'month_revenue' => Appointment::where('tenant_id', $tenant->id)
                ->where('status', 'completed')
                ->whereMonth('appointment_date', $month->month)
                ->whereYear('appointment_date', $month->year)
                ->with('service')
                ->get()
                ->sum(fn ($a) => $a->service?->price ?? 0),

            'total_customers' => User::where('tenant_id', $tenant->id)
                ->role('customer')
                ->count(),

            'staff_active' => Staff::where('tenant_id', $tenant->id)
                ->where('is_available', true)
                ->count(),

            'staff_total' => Staff::where('tenant_id', $tenant->id)->count(),

            'pending_commissions' => Commission::where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->sum('commission_amount'),

            'low_stock' => Product::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->whereRaw('quantity <= low_stock_threshold')
                ->count(),

            'upcoming_today' => Appointment::where('tenant_id', $tenant->id)
                ->whereDate('appointment_date', $today)
                ->where('start_time', '>', Carbon::now()->format('H:i'))
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->count(),
        ];

        // ── TODAY'S APPOINTMENTS ──
        $todayAppointments = Appointment::with(['customer', 'staff.user', 'service'])
            ->where('tenant_id', $tenant->id)
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->take(8)
            ->get();

        // ── MONTHLY REVENUE CHART (last 6 months) ──
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $rev = Appointment::where('tenant_id', $tenant->id)
                ->where('status', 'completed')
                ->whereYear('appointment_date', $m->year)
                ->whereMonth('appointment_date', $m->month)
                ->with('service')
                ->get()
                ->sum(fn ($a) => $a->service?->price ?? 0);
            $monthlyRevenue[$m->format('M')] = $rev;
        }

        // ── TOP SERVICES ──
        $topServices = Appointment::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->selectRaw('service_id, COUNT(*) as total')
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->take(5)
            ->with('service')
            ->get()
            ->map(fn ($a) => [
                'name' => $a->service?->name ?? 'Unknown',
                'total' => $a->total,
            ]);

        // ── STAFF PERFORMANCE (this month) ──
        $staffPerformance = Staff::where('tenant_id', $tenant->id)
            ->with('user')
            ->get()
            ->map(function ($s) use ($month) {
                $completed = Appointment::where('tenant_id', $s->tenant_id)
                    ->where('staff_id', $s->id)
                    ->where('status', 'completed')
                    ->whereMonth('appointment_date', $month->month)
                    ->whereYear('appointment_date', $month->year)
                    ->count();

                return [
                    'name' => $s->user?->name ?? '—',
                    'completed' => $completed,
                    'initials' => strtoupper(substr($s->user?->name ?? 'S', 0, 2)),
                ];
            })
            ->sortByDesc('completed')
            ->take(5)
            ->values();

        // ── LOW STOCK PRODUCTS ──
        $lowStockProducts = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->whereRaw('quantity <= low_stock_threshold')
            ->take(5)
            ->get();

        // ── RECENT APPOINTMENTS (last 10) ──
        $recentAppointments = Appointment::with(['customer', 'staff.user', 'service'])
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->take(10)
            ->get();

        return view('owner.dashboard', compact(
            'stats', 'todayAppointments', 'monthlyRevenue',
            'topServices', 'staffPerformance', 'lowStockProducts', 'recentAppointments'
        ));
    }
}
