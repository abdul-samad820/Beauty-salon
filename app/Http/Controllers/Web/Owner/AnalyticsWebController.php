<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AnalyticsWebController extends Controller
{
    public function index()
    {

        $tenant = app('currentTenant');
        if (! $tenant->canUseFeature('analytics_enabled')) {
            return redirect()->route('owner.dashboard')
                ->with('error', 'Analytics feature is not available on your current plan.');
        }
        $period = (int) request('period', 30);

        $payload = Cache::remember(
            "owner_analytics_{$tenant->id}_{$period}",
            300, // 5 minutes
            function () use ($tenant, $period) {

                $now = Carbon::now();
                $from = $now->copy()->subDays($period)->startOfDay();

                // ── HEADER KPI STATS ──
                $totalRevenue = Appointment::where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->sum('amount');

                $periodRevenue = Appointment::where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->where('appointment_date', '>=', $from)
                    ->sum('amount');

                $prevFrom = $from->copy()->subDays($period)->startOfDay();
                $prevRevenue = Appointment::where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->whereBetween('appointment_date', [$prevFrom, $from])
                    ->sum('amount');

                $revenueChange = $prevRevenue > 0
                    ? round((($periodRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
                    : 0;

                $totalAppts = Appointment::where('tenant_id', $tenant->id)->count();
                $periodAppts = Appointment::where('tenant_id', $tenant->id)
                    ->where('appointment_date', '>=', $from)->count();
                $prevAppts = Appointment::where('tenant_id', $tenant->id)
                    ->whereBetween('appointment_date', [$prevFrom, $from])->count();
                $bookingsChange = $prevAppts > 0
                    ? round((($periodAppts - $prevAppts) / $prevAppts) * 100, 1)
                    : 0;

                $totalCustomers = User::where('tenant_id', $tenant->id)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'customer')
                        ->where('guard_name', 'customer'))
                    ->count();

                $stats = [
                    'total_revenue' => $totalRevenue,
                    'this_month_rev' => $periodRevenue,
                    'total_appts' => $totalAppts,
                    'total_customers' => $totalCustomers,
                    'retention_rate' => $this->getRetentionRate($tenant->id, $from),
                ];

                $from6Months = $now->copy()->subMonths(5)->startOfMonth();

                $revenueRows = Appointment::where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->where('appointment_date', '>=', $from)
                    ->selectRaw("DATE_FORMAT(appointment_date, '%d %b') as day_label, SUM(amount) as total")
                    ->groupByRaw("DATE_FORMAT(appointment_date, '%d %b'), DATE(appointment_date)")
                    ->orderByRaw('DATE(appointment_date)')
                    ->pluck('total', 'day_label');

                $monthlyRevenue = [];
                for ($i = $period - 1; $i >= 0; $i--) {
                    $label = $now->copy()->subDays($i)->format('d M');
                    $monthlyRevenue[$label] = round($revenueRows[$label] ?? 0);
                }

                $customerRows = User::where('tenant_id', $tenant->id)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
                    ->where('created_at', '>=', $from6Months)
                    ->selectRaw("DATE_FORMAT(created_at, '%b') as month, COUNT(*) as total")
                    ->groupByRaw("DATE_FORMAT(created_at, '%b'), YEAR(created_at), MONTH(created_at)")
                    ->orderByRaw('YEAR(created_at), MONTH(created_at)')
                    ->pluck('total', 'month');

                $monthlyCustomers = [];
                for ($i = 5; $i >= 0; $i--) {
                    $label = $now->copy()->subMonths($i)->format('M');
                    $monthlyCustomers[$label] = (int) ($customerRows[$label] ?? 0);
                }

                $topServices = Appointment::where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->where('appointment_date', '>=', $from)
                    ->selectRaw('service_id, COUNT(*) as total, SUM(amount) as revenue_sum')
                    ->groupBy('service_id')
                    ->orderByDesc('revenue_sum')
                    ->with('service')
                    ->take(6)
                    ->get()
                    ->map(fn ($a) => [
                        'name' => $a->service?->name ?? '—',
                        'total' => (int) $a->total,
                        'revenue' => round($a->revenue_sum ?? 0),
                    ]);

                // ✅ AFTER — single query with groupBy
                $staffPerf = Staff::where('tenant_id', $tenant->id)
                    ->with('user')
                    ->withSum([
                        'appointments as revenue' => fn ($q) => $q
                            ->where('status', 'completed')
                            ->where('appointment_date', '>=', $from),
                    ], 'amount')
                    ->withCount([
                        'appointments as completed_count' => fn ($q) => $q
                            ->where('status', 'completed')
                            ->where('appointment_date', '>=', $from),
                        'appointments as cancelled_count' => fn ($q) => $q
                            ->where('status', 'cancelled')
                            ->where('appointment_date', '>=', $from),
                    ])
                    ->get()
                    ->map(fn ($s) => [
                        'name' => $s->user?->name ?? '—',
                        'services' => $s->completed_count,
                        'cancelled' => $s->cancelled_count,
                        'revenue' => round($s->revenue ?? 0),
                        'initials' => strtoupper(substr($s->user?->name ?? 'S', 0, 2)),
                    ])
                    ->sortByDesc('revenue')
                    ->values();

                // ── POPULAR TIME SLOTS ──
                $popularSlots = Appointment::where('tenant_id', $tenant->id)
                    ->where('status', '!=', 'cancelled')
                    ->where('appointment_date', '>=', $from)
                    ->selectRaw('SUBSTRING(start_time,1,5) as slot, COUNT(*) as cnt')
                    ->groupBy('slot')
                    ->orderByDesc('cnt')
                    ->take(8)
                    ->pluck('cnt', 'slot');

                // ── AI INSIGHTS ──
                $insights = $this->buildInsights($stats, $monthlyRevenue, $staffPerf);

                // ── ANALYTICS ARRAY FOR BLADE ──
                $analytics = [
                    'revenue' => $stats['total_revenue'],
                    'revenue_change' => $revenueChange,
                    'bookings' => $stats['total_appts'],
                    'bookings_change' => $bookingsChange,
                    'new_customers' => $stats['total_customers'],
                    'avg_value' => $totalAppts > 0
                        ? round($totalRevenue / $totalAppts)
                        : 0,

                    'daily_revenue' => $monthlyRevenue,

                    'status_breakdown' => [
                        'completed' => Appointment::where('tenant_id', $tenant->id)->where('status', 'completed')->where('appointment_date', '>=', $from)->count(),
                        'pending' => Appointment::where('tenant_id', $tenant->id)->where('status', 'pending')->where('appointment_date', '>=', $from)->count(),
                        'cancelled' => Appointment::where('tenant_id', $tenant->id)->where('status', 'cancelled')->where('appointment_date', '>=', $from)->count(),
                        'no_show' => Appointment::where('tenant_id', $tenant->id)->where('status', 'no_show')->where('appointment_date', '>=', $from)->count(),
                    ],

                    'top_services' => $topServices->map(fn ($item) => [
                        'name' => $item['name'],
                        'revenue' => $item['revenue'],   // ← real rupee amount
                        'count' => $item['total'],     // ← number of bookings
                    ]),

                    'staff_performance' => $staffPerf->map(fn ($item) => [
                        'name' => $item['name'],
                        'completed' => $item['services'],
                        'cancelled' => $item['cancelled'],
                    ]),
                ];

                return [
                    'analytics' => $analytics,
                    'stats' => $stats,
                    'monthlyRevenue' => $monthlyRevenue,
                    'monthlyCustomers' => $monthlyCustomers,
                    'topServices' => $topServices,
                    'staffPerf' => $staffPerf,
                    'popularSlots' => $popularSlots,
                    'insights' => $insights,
                ];
            }
        );

        return view('owner.analytics.index', $payload);
    }

    private function getRetentionRate(int $tenantId, Carbon $from): float
    {
        $total = User::where('tenant_id', $tenantId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'customer')
                ->where('guard_name', 'customer'))
            ->count();

        if ($total === 0) {
            return 0;
        }

        $returning = Appointment::where('tenant_id', $tenantId)
            ->where('status', '!=', 'cancelled')
            ->where('appointment_date', '>=', $from)
            ->selectRaw('customer_id, COUNT(*) as cnt')
            ->groupBy('customer_id')
            ->havingRaw('cnt >= 2')
            ->count();

        return round(($returning / $total) * 100, 1);
    }

    private function buildInsights(array $stats, array $monthlyRevenue, $staffPerf): array
    {
        $insights = [];
        $revArr = array_values($monthlyRevenue);

        if (count($revArr) >= 2 && end($revArr) > prev($revArr)) {
            $insights[] = [
                'icon' => 'bi-arrow-up-right',
                'color' => 'emerald',
                'title' => 'Revenue Growing',
                'desc' => 'Revenue for this month is higher than last month. Great work!',
            ];
        }

        if ($stats['retention_rate'] > 50) {
            $insights[] = [
                'icon' => 'bi-heart-fill',
                'color' => 'gold',
                'title' => 'High Retention',
                'desc' => "Retention rate {$stats['retention_rate']}% — customers are returning.",
            ];
        }

        $top = $staffPerf->first();
        if ($top && $top['revenue'] > 0) {
            $insights[] = [
                'icon' => 'bi-star-fill',
                'color' => 'gold',
                'title' => 'Top Performer',
                'desc' => "{$top['name']} generated ₹".number_format($top['revenue']).' in revenue during the selected period.',
            ];
        }

        return $insights;
    }
}
