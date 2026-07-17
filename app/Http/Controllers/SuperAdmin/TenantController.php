<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreTenantRequest;
use App\Http\Requests\SuperAdmin\UpdateTenantRequest;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;

class TenantController extends Controller
{
    /**
     * Display a paginated directory of platform tenants with server-side filtering.
     */
    public function index(Request $request)
    {
        $query = Tenant::withCount(['users', 'services', 'staff', 'appointments'])->with('owner');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('subdomain', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'trial') {
                $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('plan') && $request->plan !== 'all') {
            $query->where('plan', $request->plan);
        }

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        if (in_array($sort, ['created_at', 'name', 'plan', 'status'])) {
            $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
        }

        $tenants = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'active')->count(),
            'trial' => Tenant::whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now())->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
        ];

        return view('superadmin.tenants.index', compact('tenants', 'stats'));
    }

    /**
     * Show the form for creating a new tenant business profile.
     */
    public function create()
    {
        return view('superadmin.tenants.create');
    }

    /**
     * Store a newly created tenant partition and bind its initial owner profile.
     */
    public function store(StoreTenantRequest $request)
    {
        $result = DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name' => $request->business_name,
                'slug' => Str::slug($request->subdomain),
                'subdomain' => strtolower($request->subdomain),
                'email' => $request->owner_email,
                'phone' => $request->phone,
                'address' => $request->address,
                'plan' => $request->plan,
                'status' => 'active',
                'settings' => [
                    'working_hours' => [
                        'mon' => '09:00-20:00', 'tue' => '09:00-20:00',
                        'wed' => '09:00-20:00', 'thu' => '09:00-20:00',
                        'fri' => '09:00-20:00', 'sat' => '09:00-20:00',
                        'sun' => null,
                    ],
                    'timezone' => 'Asia/Kolkata',
                ],
                'trial_ends_at' => $request->plan === 'free' ? now()->addDays(14) : null,
            ]);

            $owner = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'phone' => $request->phone,
                'password' => Hash::make($request->owner_password),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $owner->forceFill(['email_verified_at' => now()])->save();
            $owner->assignRole('owner');

            AuditLog::record('tenant.created', Tenant::class, $tenant->id, [
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'plan' => $tenant->plan,
            ]);

            $plan = Plan::where('slug', $request->plan)->first();
            if ($plan) {
                $startsAt = now();
                $expiresAt = $request->plan === 'free'
                    ? $startsAt->copy()->addDays(14)
                    : $startsAt->copy()->addYear();

                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'billing_cycle' => 'monthly',
                    'status' => $request->plan === 'free' ? 'trial' : 'active',
                    'amount' => $plan->price_monthly,
                    'starts_at' => $startsAt,
                    'expires_at' => $expiresAt,
                ]);
            }

            return $tenant;
        });

        return redirect()
            ->route('superadmin.tenants.show', $result)
            ->with('success', "Success: Tenant business profile for \"{$result->name}\" has been provisioned successfully.");
    }

    /**
     * Display comprehensive details for a specific tenant utilizing optimized memory bounds.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load([
            'users:id,tenant_id,name,email,phone,is_active,created_at',
            'services:id,tenant_id,name,category,duration_minutes,price,is_active',
            'staff:id,tenant_id,user_id,commission_percent,is_available',
        ]);

        $appointments = $tenant->appointments()
            ->with(['service:id,name', 'customer:id,name'])
            ->latest()
            ->paginate(10, ['*'], 'appointments_page')
            ->withQueryString();

        $apptStats = $tenant->appointments()
            ->selectRaw("
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as revenue
    ")
            ->first();

        $stats = [
            'total_appointments' => $apptStats->total ?? 0,
            'completed' => $apptStats->completed ?? 0,
            'cancelled' => $apptStats->cancelled ?? 0,
            'total_revenue' => $apptStats->revenue ?? 0,
            'staff_count' => $tenant->staff->count(),
            'services_count' => $tenant->services->count(),
        ];

        $monthlyRevenue = $tenant->appointments()
            ->where('status', 'completed')
            ->where('appointment_date', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return view('superadmin.tenants.show', compact('tenant', 'appointments', 'stats', 'monthlyRevenue'));
    }

    /**
     * Show the form for editing specific configuration fields of a tenant instance.
     */
    public function edit(Tenant $tenant)
    {
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    /**
     * Process updates over tenant metadata structures securely.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $tenant->update([
            'name' => $request->business_name,
            'subdomain' => strtolower($request->subdomain),
            'slug' => Str::slug($request->subdomain),
            'phone' => $request->phone,
            'address' => $request->address,
            'plan' => $request->plan,
            'status' => $request->status ?? $tenant->status,
        ]);

        if ($tenant->wasChanged('plan')) {
            $plan = Plan::where('slug', $request->plan)->first();
            if ($plan) {
                $tenant->subscriptions()
                    ->whereIn('status', ['active', 'trial'])
                    ->update([
                        'plan_id' => $plan->id,
                        'amount' => $plan->price_monthly,
                    ]);
            }
        }

        return redirect()
            ->route('superadmin.tenants.show', $tenant)
            ->with('success', 'Success: Tenant record configurations updated successfully.');
    }

    /**
     * Update runtime operational state status rules safely over targeted workspace blocks.
     */
    public function updateStatus(Request $request, Tenant $tenant)
    {
        $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);
        AuditLog::record('tenant.status_updated', Tenant::class, $tenant->id, [
            'old_status' => $tenant->status,
            'new_status' => $request->status,
        ]);

        $tenant->update(['status' => $request->status]);

        $msg = match ($request->status) {
            'active' => 'Success: Tenant workspace activated successfully.',
            'suspended' => 'Notification: Tenant instance has been safely suspended.',
            'inactive' => 'Notification: Tenant instance marked as inactive.',
            default => 'Notification: Tenant status updated successfully.',

        };

        return back()->with('success', $msg);
    }

    /**
     * Soft delete/suspend localized scopes and enforce strict user de-activation cascading.
     */
    public function destroy(Tenant $tenant)
    {
        DB::transaction(function () use ($tenant) {
            PersonalAccessToken::whereIn(
                'tokenable_id',
                $tenant->users()->pluck('id')
            )
                ->where('tokenable_type', User::class)
                ->delete();

            User::where('tenant_id', $tenant->id)->update(['is_active' => false]);
            $tenant->update(['status' => 'suspended']);

            AuditLog::record('tenant.deleted', Tenant::class, $tenant->id, [
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
            ]);
        });

        return redirect()
            ->route('superadmin.tenants.index')
            ->with('success', "Success: Tenant \"{$tenant->name}\" has been suspended. Associated users deactivated while telemetry matrices are preserved.");
    }

    /**
     * Process centralized calculations for the core systems management monitoring dashboards.
     */
    public function dashboard()
    {
        $stats = Cache::remember('superadmin_dashboard_stats', 60, function () {
            $now = now();

            $tenantAgg = Tenant::selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
                    SUM(CASE WHEN trial_ends_at IS NOT NULL AND trial_ends_at <= ? AND trial_ends_at > ? THEN 1 ELSE 0 END) as trial_ending,
                    SUM(CASE WHEN trial_ends_at IS NOT NULL AND trial_ends_at > ? THEN 1 ELSE 0 END) as trial_tenants,
                    SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as new_this_month,
                    SUM(CASE WHEN plan = 'free' THEN 1 ELSE 0 END) as free_plan,
                    SUM(CASE WHEN plan = 'basic' THEN 1 ELSE 0 END) as basic_plan,
                    SUM(CASE WHEN plan = 'premium' THEN 1 ELSE 0 END) as premium_plan
                ", [
                $now->copy()->addDays(3), $now,
                $now,
                $now->month, $now->year,
            ])->first();

            $apptAgg = Appointment::selectRaw("
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN DATE(appointment_date) = ? THEN 1 ELSE 0 END) as bookings_today,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN status = 'completed' AND MONTH(appointment_date) = ? AND YEAR(appointment_date) = ? THEN amount ELSE 0 END) as platform_revenue_month
                ", [$now->toDateString(), $now->month, $now->year])->first();

            return [
                'total_tenants' => (int) $tenantAgg->total,
                'active_tenants' => (int) $tenantAgg->active,
                'suspended' => (int) $tenantAgg->suspended,
                'trial_ending' => (int) $tenantAgg->trial_ending,
                'trial_tenants' => (int) $tenantAgg->trial_tenants,
                'total_users' => User::whereNotNull('tenant_id')->count(),
                'new_this_month' => (int) $tenantAgg->new_this_month,
                'total_bookings_today' => (int) $apptAgg->bookings_today,
                'total_bookings' => (int) $apptAgg->total_bookings,
                'total_revenue' => (float) $apptAgg->total_revenue,
                'platform_revenue_month' => (float) $apptAgg->platform_revenue_month,
                'free_tenants' => (int) $tenantAgg->free_plan,
                'basic_tenants' => (int) $tenantAgg->basic_plan,
                'premium_tenants' => (int) $tenantAgg->premium_plan,
            ];
        });
        // Sparkline data — last 3 months
        $sparklines = Cache::remember('superadmin_dashboard_sparklines', 60, function () {
            $rangeStart = now()->subMonths(2)->startOfMonth();

            $tenantMonthly = Tenant::where('created_at', '>=', $rangeStart)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
                ->groupBy('ym')->pluck('cnt', 'ym');

            $bookingMonthly = Appointment::where('created_at', '>=', $rangeStart)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
                ->groupBy('ym')->pluck('cnt', 'ym');

            $activeMonthly = Tenant::where('status', 'active')
                ->where('created_at', '>=', $rangeStart)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
                ->groupBy('ym')->pluck('cnt', 'ym');

            $trialMonthly = Tenant::whereNotNull('trial_ends_at')
                ->where('created_at', '>=', $rangeStart)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
                ->groupBy('ym')->pluck('cnt', 'ym');

            $months = collect([2, 1, 0])->map(fn ($i) => now()->subMonths($i)->format('Y-m'));

            return [
                'tenants' => $months->map(fn ($ym) => (int) ($tenantMonthly[$ym] ?? 0))->values(),
                'bookings' => $months->map(fn ($ym) => (int) ($bookingMonthly[$ym] ?? 0))->values(),
                'active' => $months->map(fn ($ym) => (int) ($activeMonthly[$ym] ?? 0))->values(),
                'trials' => $months->map(fn ($ym) => (int) ($trialMonthly[$ym] ?? 0))->values(),
            ];
        });
        $planDistribution = Tenant::selectRaw('plan, COUNT(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $recentTenants = Tenant::withCount('appointments')
            ->with('owner')
            ->latest()
            ->take(8)
            ->get();

        $monthlyGrowth = Tenant::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $recentActivity = collect();

        // Today  bookings
        $todayBookings = Appointment::with('tenant')
            ->whereDate('appointment_date', today())
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($a) => [
                'title' => ($a->tenant->name ?? 'A salon').' received a new booking.',
                'time' => $a->created_at->diffForHumans(),
                'sort_time' => $a->created_at,
                'icon' => 'bi-calendar-check',
                'bg' => 'rgba(16,185,129,.12)',
                'color' => 'var(--emerald)',
            ]);

        $newSubscriptions = Subscription::with(['tenant', 'plan'])
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($s) => [
                'title' => ($s->tenant->name ?? 'A tenant').' activated '.ucfirst($s->plan->name ?? '').' plan.',
                'time' => $s->created_at->diffForHumans(),
                'sort_time' => $s->created_at,
                'icon' => 'bi-credit-card',
                'bg' => 'rgba(139,92,246,.12)',
                'color' => 'var(--purple)',
            ]);

        // Suspended tenants
        $suspended = Tenant::where('status', 'suspended')
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(fn ($t) => [
                'title' => $t->name.' account suspended.',
                'time' => $t->updated_at->diffForHumans(),
                'sort_time' => $t->updated_at,
                'icon' => 'bi-slash-circle',
                'bg' => 'rgba(239,68,68,.12)',
                'color' => 'var(--red, #ef4444)',
            ]);

        $newTenants = Tenant::latest()->take(3)->get()->map(fn ($t) => [
            'title' => $t->name.' joined the platform.',
            'time' => $t->created_at->diffForHumans(),
            'sort_time' => $t->created_at,
            'icon' => 'bi-shop',
            'bg' => 'rgba(201,169,110,.12)',
            'color' => 'var(--gold)',
        ]);

        $recentActivity = $recentActivity
            ->merge($newTenants)
            ->merge($todayBookings)
            ->merge($newSubscriptions)
            ->merge($suspended)
            ->sortByDesc('sort_time')
            ->take(8)
            ->values()
            ->toArray();

        $activeTenants = $stats['active_tenants'];

        return view('superadmin.dashboard.index', compact(
            'stats', 'planDistribution', 'recentTenants',
            'monthlyGrowth', 'recentActivity', 'activeTenants', 'sparklines'
        ));
    }

    public function liveStats()
    {
        $data = Cache::remember('superadmin_live_stats', 30, fn () => [
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_bookings_today' => Appointment::whereDate('appointment_date', today())->count(),
            'platform_revenue_month' => Appointment::where('status', 'completed')
                ->whereMonth('appointment_date', now()->month)
                ->whereYear('appointment_date', now()->year)
                ->sum('amount'),
        ]);

        return response()->json($data);
    }

    public function notifications()
    {
        $notifications = AuditLog::select('id', 'action', 'payload', 'created_at', 'is_read')
            ->whereNull('tenant_id')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'title' => $this->getNotificationTitle($log->action, $log->payload),
                'time' => $log->created_at->diffForHumans(),
                'icon' => $this->getNotificationIcon($log->action),
                'color' => $this->getNotificationColor($log->action),
                'is_read' => $log->is_read,
            ]);

        $unreadCount = AuditLog::whereNull('tenant_id')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markNotificationsRead()
    {
        AuditLog::whereNull('tenant_id')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    private function getNotificationTitle(string $action, ?array $payload): string
    {
        return match ($action) {
            'tenant.created' => ($payload['name'] ?? 'A salon').' joined the platform.',
            'tenant.status_updated' => ($payload['name'] ?? 'A tenant').' status changed to '.($payload['new_status'] ?? ''),
            'tenant.deleted' => ($payload['name'] ?? 'A tenant').' was suspended.',
            default => ucfirst(str_replace('.', ' ', $action)),
        };
    }

    private function getNotificationIcon(string $action): string
    {
        return match ($action) {
            'tenant.created' => 'bi-shop',
            'tenant.status_updated' => 'bi-arrow-repeat',
            'tenant.deleted' => 'bi-slash-circle',
            default => 'bi-bell',
        };
    }

    private function getNotificationColor(string $action): string
    {
        return match ($action) {
            'tenant.created' => 'var(--gold)',
            'tenant.status_updated' => 'var(--teal)',
            'tenant.deleted' => 'var(--rose, #f43f5e)',
            default => 'var(--text-3)',
        };
    }
}
