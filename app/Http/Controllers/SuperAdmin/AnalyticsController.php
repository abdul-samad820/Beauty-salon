<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Display the platform analytics dashboard.
     */
    public function index(Request $request)
    {
        $period = $request->get('period', '30'); // Options: 7, 30, 90, 365 days

        $from = match ($period) {
            '7' => now()->subDays(7),
            '90' => now()->subDays(90),
            '365' => now()->subDays(365),
            default => now()->subDays(30),
        };

        // ── KPI STATISTICS ──────────────────────────────────────────────
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'new_tenants' => Tenant::where('created_at', '>=', $from)->count(),
            'total_revenue' => Appointment::where('status', 'completed')
                ->where('created_at', '>=', $from)
                ->sum('amount'),
            'total_bookings' => Appointment::where('created_at', '>=', $from)->count(),
            'completed_bookings' => Appointment::where('status', 'completed')
                ->where('created_at', '>=', $from)->count(),
            'cancelled_bookings' => Appointment::where('status', 'cancelled')
                ->where('created_at', '>=', $from)->count(),
            'total_customers' => User::whereHas('roles', function ($q) {
                $q->where('name', 'customer')->where('guard_name', 'customer');
            })->where('created_at', '>=', $from)->count(),
        ];

        $stats['completion_rate'] = $stats['total_bookings'] > 0
            ? round(($stats['completed_bookings'] / $stats['total_bookings']) * 100, 1)
            : 0;

        // ── TENANT GROWTH (Last 12 months) ──────────────────────────────
        $twelveMonthsAgo = now()->subMonths(11)->startOfMonth();
        $tenantGrowthRaw = Tenant::where('created_at', '>=', $twelveMonthsAgo)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as count")
            ->groupBy('ym')
            ->pluck('count', 'ym');

        $tenantGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $tenantGrowth[$m->format('M y')] = (int) ($tenantGrowthRaw[$m->format('Y-m')] ?? 0);
        }

        // ── REVENUE GROWTH (Last 12 months) ─────────────────────────────
        $revenueGrowthRaw = Appointment::where('status', 'completed')
            ->where('appointment_date', '>=', $twelveMonthsAgo)
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $revenueGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $revenueGrowth[$m->format('M y')] = (float) ($revenueGrowthRaw[$m->format('Y-m')] ?? 0);
        }

        // ── TOP TENANTS by revenue ──────────────────────────────────────
        $topParlours = Tenant::withCount(['appointments as appointments_count' => function ($q) use ($from) {
            $q->where('created_at', '>=', $from);
        }])
            ->withSum(['appointments as revenue' => function ($q) use ($from) {
                $q->where('status', 'completed')
                    ->where('created_at', '>=', $from);
            }], 'amount')
            ->where('status', 'active')
            ->get()
            ->map(function ($t) {
                return [
                    'name' => $t->name,
                    'subdomain' => $t->subdomain,
                    'plan' => $t->plan,
                    'bookings' => $t->appointments_count,
                    'revenue' => round($t->revenue ?? 0),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        // ── PLAN DISTRIBUTION ──────────────────────────────────────────
        $planDistribution = Tenant::selectRaw('plan, COUNT(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        // ── BOOKING STATUS DISTRIBUTION ────────────────────────────────
        $bookingStatus = Appointment::where('created_at', '>=', $from)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── DAILY BOOKINGS (Period ke hisaab se) ──────────────────────────────
        $days = (int) $period;
        $periodStart = now()->subDays($days - 1)->startOfDay();
        $dailyBookingsRaw = Appointment::where('appointment_date', '>=', $periodStart)
            ->selectRaw('DATE(appointment_date) as d, COUNT(*) as count')
            ->groupBy('d')
            ->pluck('count', 'd');

        $dailyBookings = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $dailyBookings[$d->format('d M')] = (int) ($dailyBookingsRaw[$d->format('Y-m-d')] ?? 0);
        }

        return view('superadmin.analytics.index', compact(
            'stats', 'tenantGrowth', 'revenueGrowth',
            'topParlours', 'planDistribution', 'bookingStatus',
            'dailyBookings', 'period'
        ));
    }
}
