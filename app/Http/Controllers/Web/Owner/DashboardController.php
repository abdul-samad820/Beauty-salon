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
            return [
                'today_bookings' => Appointment::where('tenant_id', $tenant->id)
                    ->whereDate('appointment_date', $today)
                    ->whereNotIn('status', ['cancelled'])
                    ->count(),

                'month_revenue' => Appointment::where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->whereMonth('appointment_date', $month->month)
                    ->whereYear('appointment_date', $month->year)
                    ->sum('amount'),

                'total_customers' => User::where('tenant_id', $tenant->id)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
                    ->count(),

                'staff_active' => Staff::where('tenant_id', $tenant->id)
                    ->where('is_available', true)
                    ->count(),

                'staff_total' => Staff::where('tenant_id', $tenant->id)->count(),

                'pending_commissions' => Commission::where('tenant_id', $tenant->id)
                    ->where('status', 'pending')
                    ->sum('commission_amount'),

                'low_stock_alerts' => Product::where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->whereRaw('quantity <= low_stock_threshold')
                    ->count(),

                'upcoming_today' => Appointment::where('tenant_id', $tenant->id)
                    ->whereDate('appointment_date', $today)
                    ->where('start_time', '>', Carbon::now()->format('H:i'))
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->count(),

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
        $sparklines = Cache::remember("dashboard_sparklines_{$tenant->id}", 60, function () use ($tenant, $stats) {
            return [
                'bookings' => collect([2, 1, 0])->map(fn ($i) => Appointment::where('tenant_id', $tenant->id)
                    ->whereNotIn('status', ['cancelled'])
                    ->whereMonth('appointment_date', now()->subMonths($i)->month)
                    ->whereYear('appointment_date', now()->subMonths($i)->year)
                    ->count()
                )->values(),

                'revenue' => collect([2, 1, 0])->map(fn ($i) => Appointment::where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->whereMonth('appointment_date', now()->subMonths($i)->month)
                    ->whereYear('appointment_date', now()->subMonths($i)->year)
                    ->sum('amount')
                )->values(),

                'customers' => collect([2, 1, 0])->map(fn ($i) => User::where('tenant_id', $tenant->id)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
                    ->whereMonth('created_at', now()->subMonths($i)->month)
                    ->whereYear('created_at', now()->subMonths($i)->year)
                    ->count()
                )->values(),

                'lowstock' => collect([
                    $stats['low_stock_alerts'],
                    $stats['low_stock_alerts'],
                    $stats['low_stock_alerts'],
                ])->values(),

                'reviews' => collect([2, 1, 0])->map(fn ($i) => Review::where('tenant_id', $tenant->id)
                    ->where('status', 'pending')
                    ->whereMonth('created_at', now()->subMonths($i)->month)
                    ->whereYear('created_at', now()->subMonths($i)->year)
                    ->count()
                )->values(),
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
