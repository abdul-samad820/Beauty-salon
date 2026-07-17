<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * SuperAdmin Revenue Controller
 * Platform-wide revenue breakdown.
 */
class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', 'all');

        // ── KPI ────────────────────────────────────────────────────
        $baseQ = Appointment::where('status', 'completed')
            ->whereYear('appointment_date', $year);

        if ($month !== 'all') {
            $baseQ->whereMonth('appointment_date', $month);
        }

        $totalRevenue = (clone $baseQ)->sum('amount');
        $totalBookings = (clone $baseQ)->count();
        $avgBookingValue = $totalBookings > 0
            ? round($totalRevenue / $totalBookings, 2)
            : 0;

        // Last period comparison (same period last year)
        $lastYearQ = Appointment::where('status', 'completed')
            ->whereYear('appointment_date', $year - 1);
        if ($month !== 'all') {
            $lastYearQ->whereMonth('appointment_date', $month);
        }
        $lastYearRevenue = $lastYearQ->sum('amount');
        $revenueGrowth = $lastYearRevenue > 0
            ? round((($totalRevenue - $lastYearRevenue) / $lastYearRevenue) * 100, 1)
            : 0;

        $stats = [
            'total_revenue' => $totalRevenue,
            'total_bookings' => $totalBookings,
            'avg_booking_value' => $avgBookingValue,
            'revenue_growth' => $revenueGrowth,
            'active_tenants' => Tenant::where('status', 'active')->count(),
        ];

        // ── MONTHLY REVENUE (12 months of selected year) ───────────
        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[now()->setMonth($m)->format('M')] = Appointment::where('status', 'completed')
                ->whereYear('appointment_date', $year)
                ->whereMonth('appointment_date', $m)
                ->sum('amount');
        }

        // ── TENANT-WISE REVENUE ─────────────────────────────────────
        $tenantRevenue = Tenant::with(['appointments' => function ($q) use ($year, $month) {
            $q->where('status', 'completed')->whereYear('appointment_date', $year);
            if ($month !== 'all') {
                $q->whereMonth('appointment_date', $month);
            }
        }])
            ->get()
            ->map(function ($t) {
                $rev = $t->appointments->sum('amount');

                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'subdomain' => $t->subdomain,
                    'plan' => $t->plan,
                    'status' => $t->status,
                    'revenue' => $rev,
                    'bookings' => $t->appointments->count(),
                    'avg' => $t->appointments->count() > 0
                        ? round($rev / $t->appointments->count(), 2) : 0,
                ];
            })
            ->filter(fn ($t) => $t['revenue'] > 0)
            ->sortByDesc('revenue')
            ->values();

        // ── PLAN-WISE REVENUE ──────────────────────────────────────
        $planRevenue = $tenantRevenue->groupBy('plan')->map(fn ($g) => [
            'count' => $g->count(),
            'revenue' => $g->sum('revenue'),
        ]);

        // ── AVAILABLE YEARS for filter ─────────────────────────────
        $years = Appointment::selectRaw('YEAR(appointment_date) as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();

        if (empty($years)) {
            $years = [now()->year];
        }

        return view('superadmin.revenue.index', compact(
            'stats', 'monthlyRevenue', 'tenantRevenue',
            'planRevenue', 'years', 'year', 'month'
        ));
    }
}
