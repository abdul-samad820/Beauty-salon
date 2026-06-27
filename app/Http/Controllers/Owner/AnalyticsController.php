<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    /**
     * Retrieve revenue analytics.
     */
    public function revenue(Request $request)
    {
        $tenantId = app('currentTenant')->id;
        $year = $request->year ?? Carbon::now()->year;

        // FIX: 6 loop queries → 1 grouped query, PHP me distribute karo
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $monthlyRows = Appointment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->where('appointment_date', '>=', $sixMonthsAgo)
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->selectRaw("DATE_FORMAT(appointment_date, '%b %Y') as label, SUM(services.price) as total")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%b %Y'), YEAR(appointment_date), MONTH(appointment_date)")
            ->orderByRaw('YEAR(appointment_date), MONTH(appointment_date)')
            ->pluck('total', 'label');

        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $label = $month->format('M Y');
            $monthlyRevenue[] = [
                'month' => $label,
                'revenue' => (float) ($monthlyRows[$label] ?? 0),
            ];
        }

        // FIX: 30 loop queries → 1 grouped query, PHP me distribute karo
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $todayDate = Carbon::now()->toDateString();
        $dailyRows = Appointment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('appointment_date', [$monthStart, $todayDate])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->selectRaw("DATE_FORMAT(appointment_date, '%d %b') as label, DATE(appointment_date) as sort_date, SUM(services.price) as total")
            ->groupByRaw("DATE(appointment_date), DATE_FORMAT(appointment_date, '%d %b')")
            ->orderBy('sort_date')
            ->pluck('total', 'label');

        $dailyRevenue = [];
        $daysInMonth = Carbon::now()->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::now()->day($day);
            if ($date->isFuture()) {
                break;
            }
            $label = $date->format('d M');
            $dailyRevenue[] = [
                'date' => $label,
                'revenue' => (float) ($dailyRows[$label] ?? 0),
            ];
        }

        $totalRevenue = Appointment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');

        $thisMonthRevenue = Appointment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereMonth('appointment_date', Carbon::now()->month)
            ->whereYear('appointment_date', Carbon::now()->year)
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');

        return response()->json([
            'message' => 'Revenue analytics retrieved successfully.',
            'total_revenue' => '₹'.number_format($totalRevenue, 2),
            'this_month_revenue' => '₹'.number_format($thisMonthRevenue, 2),
            'monthly_chart' => $monthlyRevenue,
            'daily_chart' => $dailyRevenue,
        ]);
    }

    /**
     * Retrieve service analytics.
     */
    public function services()
    {
        $tenantId = app('currentTenant')->id;

        // FIX: ->get()->groupBy() (saari rows memory me) → DB-level groupBy
        $topServices = Appointment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->selectRaw('services.id as service_id, services.name as service_name, services.category, COUNT(*) as total_bookings, SUM(services.price) as total_revenue_raw')
            ->groupBy('services.id', 'services.name', 'services.category')
            ->orderByDesc('total_bookings')
            ->take(5)
            ->get()
            ->map(fn ($row) => [
                'service_name' => $row->service_name,
                'category' => $row->category,
                'total_bookings' => (int) $row->total_bookings,
                'total_revenue' => '₹'.number_format((float) $row->total_revenue_raw, 2),
            ])
            ->values();

        // FIX: ->get()->groupBy() (saari rows memory me) → DB-level groupBy
        $popularSlots = Appointment::where('tenant_id', $tenantId)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('SUBSTRING(start_time, 1, 5) as slot, COUNT(*) as bookings')
            ->groupBy('slot')
            ->orderByDesc('bookings')
            ->take(10)
            ->get()
            ->map(fn ($row) => [
                'time' => $row->slot,
                'bookings' => (int) $row->bookings,
            ])
            ->values();

        return response()->json([
            'message' => 'Service analytics retrieved successfully.',
            'top_services' => $topServices,
            'popular_slots' => $popularSlots,
        ]);
    }

    /**
     * Retrieve customer statistics and analytics.
     */
    public function customers()
    {
        $currentTenant = app('currentTenant');
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $sixtyDaysAgo = Carbon::now()->subDays(60);

        $totalCustomers = User::where('tenant_id', $currentTenant->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
            ->count();

        $newCustomers = User::where('tenant_id', $currentTenant->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        // Returning customers: Customers with 2+ bookings in the last 30 days
        $returningCustomers = Appointment::where('tenant_id', $currentTenant->id)
            ->where('status', '!=', 'cancelled')
            ->where('appointment_date', '>=', $thirtyDaysAgo)
            ->get()
            ->groupBy('customer_id')
            ->filter(fn ($bookings) => $bookings->count() >= 2)
            ->count();

        // Churned customers: No activity in the last 60 days
        $churnedCustomers = User::where('tenant_id', $currentTenant->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
            ->whereDoesntHave('appointments', function ($q) use ($sixtyDaysAgo) {
                $q->where('appointment_date', '>=', $sixtyDaysAgo);
            })
            ->count();

        $monthlyNewCustomers = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $count = User::where('tenant_id', $currentTenant->id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $monthlyNewCustomers[] = [
                'month' => $month->format('M Y'),
                'count' => $count,
            ];
        }

        $retentionRate = $totalCustomers > 0
            ? round(($returningCustomers / $totalCustomers) * 100, 1)
            : 0;

        return response()->json([
            'message' => 'Customer analytics retrieved successfully.',
            'total_customers' => $totalCustomers,
            'new_customers_30days' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'churned_customers' => $churnedCustomers,
            'retention_rate' => $retentionRate.'%',
            'monthly_new_customers' => $monthlyNewCustomers,
        ]);
    }

    /**
     * Retrieve dashboard summary.
     */
    public function summary()
    {
        $tenantId = app('currentTenant')->id;

        $data = Cache::remember(
            "tenant_{$tenantId}_dashboard_summary",
            now()->addMinutes(5),
            function () use ($tenantId) {
                $today = Carbon::today();

                $todayBookings = Appointment::where('tenant_id', $tenantId)
                    ->whereDate('appointment_date', $today)
                    ->whereNotIn('status', ['cancelled'])
                    ->count();

                $todayRevenue = Appointment::where('tenant_id', $tenantId)
                    ->where('status', 'completed')
                    ->whereDate('appointment_date', $today)
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->sum('services.price');

                $monthRevenue = Appointment::where('tenant_id', $tenantId)
                    ->where('status', 'completed')
                    ->whereMonth('appointment_date', $today->month)
                    ->whereYear('appointment_date', $today->year)
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->sum('services.price');

                $totalCustomers = User::where('tenant_id', $tenantId)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
                    ->count();

                $pendingCommissions = Commission::where('tenant_id', $tenantId)
                    ->where('status', 'pending')
                    ->sum('commission_amount');

                $lowStockCount = Product::where('tenant_id', $tenantId)
                    ->where('is_active', true)
                    ->whereRaw('quantity <= low_stock_threshold')
                    ->count();

                $upcomingToday = Appointment::where('tenant_id', $tenantId)
                    ->whereDate('appointment_date', $today)
                    ->where('start_time', '>', Carbon::now()->format('H:i'))
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->count();

                return [
                    'today_bookings' => $todayBookings,
                    'upcoming_today' => $upcomingToday,
                    'today_revenue' => '₹'.number_format($todayRevenue, 2),
                    'month_revenue' => '₹'.number_format($monthRevenue, 2),
                    'total_customers' => $totalCustomers,
                    'pending_commissions' => '₹'.number_format($pendingCommissions, 2),
                    'low_stock_alerts' => $lowStockCount,
                ];
            }
        );

        return response()->json([
            'message' => 'Dashboard summary retrieved successfully.',
            'data' => $data,
        ]);
    }
}
