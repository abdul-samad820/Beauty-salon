<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreTenantRequest;
use App\Http\Requests\SuperAdmin\UpdateTenantRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    /**
     * Saare tenants — filter, search, paginate
     */
    public function index(Request $request)
    {
        $query = Tenant::withCount(['users', 'services', 'staff', 'appointments']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('subdomain', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
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
     * Create form
     */
    public function create()
    {
        return view('superadmin.tenants.create');
    }

    /**
     * Store new tenant — FormRequest se validated
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
            ]);
            $owner->assignRole('owner');

            return $tenant;
        });

        return redirect()
            ->route('superadmin.tenants.show', $result)
            ->with('success', "Tenant \"{$result->name}\" successfully create ho gaya!");
    }

    /**
     * Tenant detail
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['users', 'services', 'staff', 'appointments.service', 'appointments.customer']);

        $stats = [
            'total_appointments' => $tenant->appointments->count(),
            'completed' => $tenant->appointments->where('status', 'completed')->count(),
            'cancelled' => $tenant->appointments->where('status', 'cancelled')->count(),
            'total_revenue' => $tenant->appointments->where('status', 'completed')->sum('amount'),
            'staff_count' => $tenant->staff->count(),
            'services_count' => $tenant->services->count(),
        ];

        $monthlyRevenue = $tenant->appointments()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return view('superadmin.tenants.show', compact('tenant', 'stats', 'monthlyRevenue'));
    }

    /**
     * Edit form
     */
    public function edit(Tenant $tenant)
    {
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    /**
     * Update — FormRequest se validated
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
        ]);

        return redirect()
            ->route('superadmin.tenants.show', $tenant)
            ->with('success', 'Tenant successfully update ho gaya!');
    }

    /**
     * Status change — active / suspended / inactive
     */
    public function updateStatus(Request $request, Tenant $tenant)
    {
        $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        $tenant->update(['status' => $request->status]);

        $msg = match ($request->status) {
            'active' => 'Tenant activate ho gaya.',
            'suspended' => 'Tenant suspend ho gaya.',
            'inactive' => 'Tenant inactive ho gaya.',
        };

        return back()->with('success', $msg);
    }

    /**
     * Soft delete — suspend + deactivate users
     */
    public function destroy(Tenant $tenant)
    {
        DB::transaction(function () use ($tenant) {
            User::where('tenant_id', $tenant->id)->update(['is_active' => false]);
            $tenant->update(['status' => 'suspended']);
        });

        return redirect()
            ->route('superadmin.tenants.index')
            ->with('success', "Tenant \"{$tenant->name}\" suspend ho gaya. Data preserve hai.");
    }

    /**
     * SuperAdmin Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'trial_ending' => Tenant::whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '<=', now()->addDays(3))
                ->where('trial_ends_at', '>', now())
                ->count(),
            'total_users' => User::whereNotNull('tenant_id')->count(),
            'new_this_month' => Tenant::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $planDistribution = Tenant::selectRaw('plan, COUNT(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $recentTenants = Tenant::withCount('appointments')
            ->latest()
            ->take(8)
            ->get();

        $monthlyGrowth = Tenant::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        return view('superadmin.dashboard', compact(
            'stats', 'planDistribution', 'recentTenants', 'monthlyGrowth'
        ));
    }
}
