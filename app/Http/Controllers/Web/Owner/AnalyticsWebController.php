<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;

class AnalyticsWebController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');
        $now = Carbon::now();

        // ── HEADER STATS ──
        $stats = [
            'total_revenue' => Appointment::where('tenant_id', $tenant->id)->where('status', 'completed')->with('service')->get()->sum(fn ($a) => $a->service?->price ?? 0),
            'this_month_rev' => Appointment::where('tenant_id', $tenant->id)->where('status', 'completed')->whereMonth('appointment_date', $now->month)->whereYear('appointment_date', $now->year)->with('service')->get()->sum(fn ($a) => $a->service?->price ?? 0),
            'total_appts' => Appointment::where('tenant_id', $tenant->id)->count(),
            'total_customers' => User::where('tenant_id', $tenant->id)->role('customer')->count(),
            'retention_rate' => $this->getRetentionRate($tenant->id),
        ];

        // ── MONTHLY REVENUE (6 months) ──
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $rev = Appointment::where('tenant_id', $tenant->id)
                ->where('status', 'completed')
                ->whereYear('appointment_date', $m->year)
                ->whereMonth('appointment_date', $m->month)
                ->with('service')->get()
                ->sum(fn ($a) => $a->service?->price ?? 0);
            $monthlyRevenue[$m->format('M')] = round($rev);
        }

        // ── MONTHLY CUSTOMERS (6 months) ──
        $monthlyCustomers = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $cnt = User::where('tenant_id', $tenant->id)->role('customer')
                ->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count();
            $monthlyCustomers[$m->format('M')] = $cnt;
        }

        // ── TOP SERVICES ──
        $topServices = Appointment::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->selectRaw('service_id, COUNT(*) as total')
            ->groupBy('service_id')->orderByDesc('total')
            ->with('service')->take(6)->get()
            ->map(fn ($a) => ['name' => $a->service?->name ?? '—', 'total' => $a->total]);

        // ── STAFF PERFORMANCE ──
        $staffPerf = Staff::where('tenant_id', $tenant->id)->with('user')->get()
            ->map(function ($s) use ($now) {
                $comps = Appointment::where('tenant_id', $s->tenant_id)->where('staff_id', $s->id)
                    ->where('status', 'completed')
                    ->whereMonth('appointment_date', $now->month)
                    ->whereYear('appointment_date', $now->year)
                    ->with('service')->get();

                return [
                    'name' => $s->user?->name ?? '—',
                    'services' => $comps->count(),
                    'revenue' => round($comps->sum(fn ($a) => $a->service?->price ?? 0)),
                    'initials' => strtoupper(substr($s->user?->name ?? 'S', 0, 2)),
                ];
            })->sortByDesc('revenue')->values();

        // ── POPULAR TIME SLOTS ──
        $popularSlots = Appointment::where('tenant_id', $tenant->id)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('SUBSTRING(start_time,1,5) as slot, COUNT(*) as cnt')
            ->groupBy('slot')->orderByDesc('cnt')
            ->take(8)->pluck('cnt', 'slot');

        // ── AI INSIGHTS (rule-based) ──
        $insights = $this->buildInsights($stats, $monthlyRevenue, $staffPerf);

        return view('owner.analytics.index', compact(
            'stats', 'monthlyRevenue', 'monthlyCustomers',
            'topServices', 'staffPerf', 'popularSlots', 'insights'
        ));
    }

    private function getRetentionRate($tenantId): float
    {
        $thirty = Carbon::now()->subDays(30);
        $total = User::where('tenant_id', $tenantId)->role('customer')->count();
        if ($total === 0) {
            return 0;
        }
        $returning = Appointment::where('tenant_id', $tenantId)
            ->where('status', '!=', 'cancelled')
            ->where('appointment_date', '>=', $thirty)
            ->selectRaw('customer_id, COUNT(*) as cnt')
            ->groupBy('customer_id')->havingRaw('cnt >= 2')
            ->count();

        return round(($returning / $total) * 100, 1);
    }

    private function buildInsights(array $stats, array $monthlyRevenue, $staffPerf): array
    {
        $insights = [];
        $revArr = array_values($monthlyRevenue);
        if (count($revArr) >= 2 && end($revArr) > prev($revArr)) {
            $insights[] = ['icon' => 'bi-arrow-up-right', 'color' => 'emerald', 'title' => 'Revenue Growing', 'desc' => 'Is mahine ka revenue pichle mahine se zyada hai. Great work!'];
        }
        if ($stats['retention_rate'] > 50) {
            $insights[] = ['icon' => 'bi-heart-fill', 'color' => 'gold', 'title' => 'High Retention', 'desc' => "Retention rate {$stats['retention_rate']}% hai — customers wapas aa rahe hain."];
        }
        $top = $staffPerf->first();
        if ($top) {
            $insights[] = ['icon' => 'bi-star-fill', 'color' => 'gold', 'title' => 'Top Performer', 'desc' => "{$top['name']} ne is mahine ₹".number_format($top['revenue']).' ka revenue diya.'];
        }

        return $insights;
    }
}
