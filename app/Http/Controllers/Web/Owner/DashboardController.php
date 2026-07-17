<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Product;
use App\Models\Review;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');
        $today = Carbon::today();
        $month = Carbon::now();

        $stats = Cache::remember("dashboard_stats_{$tenant->id}", 60, function () use ($tenant, $today, $month) {
            $nowTime = Carbon::now()->format('H:i');

            $apptAgg = Appointment::where('tenant_id', $tenant->id)
                ->selectRaw("
                    SUM(CASE WHEN DATE(appointment_date) = ? AND status NOT IN ('cancelled') THEN 1 ELSE 0 END) as today_bookings,
                    SUM(CASE WHEN status = 'completed' AND MONTH(appointment_date) = ? AND YEAR(appointment_date) = ? THEN amount ELSE 0 END) as month_revenue,
                    SUM(CASE WHEN DATE(appointment_date) = ? AND start_time > ? AND status NOT IN ('cancelled', 'completed') THEN 1 ELSE 0 END) as upcoming_today
                ", [$today->toDateString(), $month->month, $month->year, $today->toDateString(), $nowTime])
                ->first();

            $staffAgg = Staff::where('tenant_id', $tenant->id)
                ->selectRaw('
                    COUNT(*) as staff_total,
                    SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as staff_active
                ')
                ->first();

            return [
                'today_bookings' => (int) $apptAgg->today_bookings,
                'month_revenue' => (float) $apptAgg->month_revenue,

                'total_customers' => User::where('tenant_id', $tenant->id)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
                    ->count(),

                'staff_active' => (int) $staffAgg->staff_active,
                'staff_total' => (int) $staffAgg->staff_total,

                'pending_commissions' => Commission::where('tenant_id', $tenant->id)
                    ->where('status', 'pending')
                    ->sum('commission_amount'),

                'low_stock_alerts' => Product::where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->whereRaw('quantity <= low_stock_threshold')
                    ->count(),

                'upcoming_today' => (int) $apptAgg->upcoming_today,

                'pending_reviews' => Review::where('tenant_id', $tenant->id)
                    ->where('status', 'pending')
                    ->count(),
            ];
        });

        // ── TODAY'S APPOINTMENTS ──
        $todayAppointments = Appointment::with(['customer', 'staff.user', 'service'])
            ->where('tenant_id', $tenant->id)
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->take(8)
            ->get();

        // ── MONTHLY REVENUE CHART (last 6 months) ──
        $monthlyRevenue = Cache::remember("dashboard_revenue_{$tenant->id}", 60, function () use ($tenant) {
            $rows = Appointment::where('tenant_id', $tenant->id)
                ->where('status', 'completed')
                ->where('appointment_date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
                ->selectRaw("DATE_FORMAT(appointment_date, '%b') as month, DATE_FORMAT(appointment_date, '%Y%m') as sort_key, SUM(amount) as total")
                ->groupBy('month', 'sort_key')
                ->orderBy('sort_key')
                ->pluck('total', 'month');

            $result = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = Carbon::now()->subMonths($i)->format('M');
                $result[$m] = $rows[$m] ?? 0;
            }

            return $result;
        });

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
            ->withCount([
                'appointments as completed_count' => function ($q) use ($month) {
                    $q->where('status', 'completed')
                        ->whereMonth('appointment_date', $month->month)
                        ->whereYear('appointment_date', $month->year);
                },
            ])
            ->get()
            ->map(function ($s) {
                return [
                    'name' => $s->user?->name ?? '—',
                    'completed' => $s->completed_count,
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

        // ── SPARKLINES — last 3 months (cached 60s) ──
        // Each metric used to run one query per month (9 queries total for 3 metrics
        // + 3 more for reviews = 12 queries every cache miss). Grouping by month in a
        // single query per metric brings that down to 4 queries total.
        $sparklines = Cache::remember("dashboard_sparklines_{$tenant->id}", 60, function () use ($tenant, $stats) {
            $months = collect([2, 1, 0])->map(fn ($i) => now()->subMonths($i)->format('Y-m'));

            $fillMonths = function ($rows) use ($months) {
                return $months->map(fn ($key) => $rows[$key] ?? 0)->values();
            };

            $bookingsByMonth = Appointment::where('tenant_id', $tenant->id)
                ->whereNotIn('status', ['cancelled'])
                ->where('appointment_date', '>=', now()->subMonths(2)->startOfMonth())
                ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $revenueByMonth = Appointment::where('tenant_id', $tenant->id)
                ->where('status', 'completed')
                ->where('appointment_date', '>=', now()->subMonths(2)->startOfMonth())
                ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as ym, SUM(amount) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $customersByMonth = User::where('tenant_id', $tenant->id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
                ->where('created_at', '>=', now()->subMonths(2)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $reviewsByMonth = Review::where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->where('created_at', '>=', now()->subMonths(2)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym');

            return [
                'bookings' => $fillMonths($bookingsByMonth),
                'revenue' => $fillMonths($revenueByMonth),
                'customers' => $fillMonths($customersByMonth),
                'lowstock' => collect([
                    $stats['low_stock_alerts'],
                    $stats['low_stock_alerts'],
                    $stats['low_stock_alerts'],
                ])->values(),
                'reviews' => $fillMonths($reviewsByMonth),
            ];
        });

        return view('owner.dashboard.index', compact(
            'stats', 'todayAppointments', 'monthlyRevenue',
            'topServices', 'staffPerformance', 'lowStockProducts',
            'recentAppointments', 'sparklines'
        ));
    }

    public function newBookingsCount()
    {
        $tenant = app('currentTenant');
        $count = Appointment::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->whereDate('created_at', today())
            ->count();

        return response()->json(['count' => $count]);
    }
}
