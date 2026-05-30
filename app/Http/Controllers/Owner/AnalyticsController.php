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
    /*
    |----------------------------------------------------------
    | STEP 1 — Revenue Analytics
    | Monthly revenue last 6 months
    | Daily revenue current month
    |----------------------------------------------------------
    */
    public function revenue(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;

        /*
        | Last 6 months ka revenue
        | Sirf completed appointments count honge
        */
        $monthlyRevenue = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $revenue = Appointment::with('service')
                ->where('tenant_id', app('currentTenant')->id)
                ->where('status', 'completed')
                ->whereYear('appointment_date', $month->year)
                ->whereMonth('appointment_date', $month->month)
                ->get()
                ->sum(fn ($a) => $a->service->price ?? 0);

            $monthlyRevenue[] = [
                'month' => $month->format('M Y'),  // "Jan 2026"
                'revenue' => (float) $revenue,
            ];
        }

        /*
        | Current month ka daily revenue
        */
        $dailyRevenue = [];
        $daysInMonth = Carbon::now()->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::now()->day($day);

            // Future days skip karo
            if ($date->isFuture()) {
                break;
            }

            $revenue = Appointment::with('service')
                ->where('status', 'completed')
                ->whereDate('appointment_date', $date->toDateString())
                ->get()
                ->sum(fn ($a) => $a->service->price ?? 0);

            $dailyRevenue[] = [
                'date' => $date->format('d M'),  // "22 May"
                'revenue' => (float) $revenue,
            ];
        }

        // Total revenue — all time
        $totalRevenue = Appointment::with('service')
            ->where('tenant_id', app('currentTenant')->id)
            ->where('status', 'completed')
            ->get()
            ->sum(fn ($a) => $a->service->price ?? 0);

        // Is month ka revenue
        $thisMonthRevenue = Appointment::with('service')
            ->where('tenant_id', app('currentTenant')->id)
            ->where('status', 'completed')
            ->whereMonth('appointment_date', Carbon::now()->month)
            ->whereYear('appointment_date', Carbon::now()->year)
            ->get()
            ->sum(fn ($a) => $a->service->price ?? 0);

        return response()->json([
            'message' => 'Revenue analytics',
            'total_revenue' => '₹'.number_format($totalRevenue, 2),
            'this_month_revenue' => '₹'.number_format($thisMonthRevenue, 2),
            'monthly_chart' => $monthlyRevenue,   // Bar chart ke liye
            'daily_chart' => $dailyRevenue,     // Line chart ke liye
        ]);
    }

    /*
    |----------------------------------------------------------
    | STEP 2 — Service Analytics
    | Top services by bookings
    | Popular time slots
    |----------------------------------------------------------
    */
    public function services()
    {
        // Top 5 services by bookings
        $topServices = Appointment::with('service')
            ->where('tenant_id', app('currentTenant')->id)
            ->where('status', 'completed')
            ->get()
            ->groupBy('service_id')
            ->map(function ($appointments) {
                $service = $appointments->first()->service;

                return [
                    'service_name' => $service->name,
                    'category' => $service->category,
                    'total_bookings' => $appointments->count(),
                    'total_revenue' => '₹'.number_format(
                        $appointments->sum(fn ($a) => $a->service->price), 2
                    ),
                ];
            })
            ->sortByDesc('total_bookings')
            ->take(5)
            ->values();

        // Popular time slots — heatmap ke liye
        $popularSlots = Appointment::where(
            'tenant_id',
            app('currentTenant')->id
        )
            ->where('status', '!=', 'cancelled')
            ->get()
            ->groupBy(fn ($a) => substr($a->start_time, 0, 5))
            ->map(fn ($group, $time) => [
                  'time' => $time,
                  'bookings' => $group->count(),
              ])
            ->sortByDesc('bookings')
            ->take(10)
            ->values();

        return response()->json([
            'message' => 'Service analytics',
            'top_services' => $topServices,    // Pie chart ke liye
            'popular_slots' => $popularSlots,   // Heatmap ke liye
        ]);
    }

    /*
    |----------------------------------------------------------
    | STEP 3 — Customer Analytics
    | New vs Returning customers
    | Retention rate
    |----------------------------------------------------------
    */
    public function customers()
    {
        $currentTenant = app('currentTenant');
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $sixtyDaysAgo = Carbon::now()->subDays(60);

        // Total customers
        $totalCustomers = User::where('tenant_id', $currentTenant->id)
            ->role('customer')
            ->count();

        // New customers — last 30 days
        $newCustomers = User::where('tenant_id', $currentTenant->id)
            ->role('customer')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        // Returning customers — last 30 days me 2+ bookings ki hain
        $returningCustomers = Appointment::where(
            'tenant_id',
            $currentTenant->id
        )
            ->where('status', '!=', 'cancelled')
            ->where('appointment_date', '>=', $thirtyDaysAgo)
            ->get()
            ->groupBy('customer_id')
            ->filter(fn ($bookings) => $bookings->count() >= 2)
            ->count();

        // Churned customers — 60 days se nahi aaye
        $churnedCustomers = User::where('tenant_id', $currentTenant->id)
            ->role('customer')
            ->whereDoesntHave('appointments', function ($q) use ($sixtyDaysAgo) {
                // Yahan appointments relation User model me add karna hoga
                $q->where('appointment_date', '>=', $sixtyDaysAgo);
            })
            ->count();

        // Monthly new customers — last 6 months
        $monthlyNewCustomers = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $count = User::where('tenant_id', $currentTenant->id)
                ->role('customer')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $monthlyNewCustomers[] = [
                'month' => $month->format('M Y'),
                'count' => $count,
            ];
        }

        // Retention rate calculate karo
        $retentionRate = $totalCustomers > 0
            ? round(($returningCustomers / $totalCustomers) * 100, 1)
            : 0;

        return response()->json([
            'message' => 'Customer analytics',
            'total_customers' => $totalCustomers,
            'new_customers_30days' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'churned_customers' => $churnedCustomers,
            'retention_rate' => $retentionRate.'%',
            'monthly_new_customers' => $monthlyNewCustomers, // Chart ke liye
        ]);
    }

    /*
    |----------------------------------------------------------
    | STEP 4 — Dashboard Summary
    | Ek API me sab important numbers
    |----------------------------------------------------------
    */

    public function summary()
    {
        $tenantId = app('currentTenant')->id;

        $data = Cache::remember(
            "tenant_{$tenantId}_dashboard_summary",
            now()->addMinutes(5),
            function () {

                $today = Carbon::today();
                $currentTenant = app('currentTenant');

                $todayBookings = Appointment::where(
                    'tenant_id',
                    $currentTenant->id
                )
                    ->whereDate('appointment_date', $today)
                    ->whereNotIn('status', ['cancelled'])
                    ->count();

                $todayRevenue = Appointment::with('service')
                    ->where('status', 'completed')
                    ->whereDate('appointment_date', $today)
                    ->get()
                    ->sum(fn ($a) => $a->service->price ?? 0);

                $monthRevenue = Appointment::with('service')
                    ->where('status', 'completed')
                    ->whereMonth('appointment_date', $today->month)
                    ->whereYear('appointment_date', $today->year)
                    ->get()
                    ->sum(fn ($a) => $a->service->price ?? 0);

                $totalCustomers = User::where('tenant_id', $currentTenant->id)
                    ->role('customer')
                    ->count();

                $pendingCommissions = Commission::where(
                    'tenant_id',
                    $currentTenant->id
                )
                    ->where('status', 'pending')
                    ->sum('commission_amount');

                $lowStockCount = Product::where(
                    'tenant_id',
                    $currentTenant->id
                )
                    ->where('is_active', true)
                    ->whereRaw('quantity <= low_stock_threshold')
                    ->count();

                $upcomingToday = Appointment::whereDate(
                    'appointment_date',
                    $today
                )
                    ->where('start_time', '>', Carbon::now()->format('H:i'))
                    ->whereNotIn('status', [
                        'cancelled',
                        'completed',
                    ])
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
            'message' => 'Dashboard summary',
            'data' => $data,
        ]);
    }
}
