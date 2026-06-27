<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * SuperAdmin Subscription Management Controller.
 */
class SubscriptionController extends Controller
{
    // ── Plans List ──────────────────────────────────────────────

    public function plans()
    {
        $plans = Plan::orderBy('sort_order')->get();

        $stats = [
            'total_plans' => $plans->count(),
            'active_plans' => $plans->where('is_active', true)->count(),
            'total_subscriptions' => Subscription::where('status', 'active')->count(),
            'monthly_revenue' => SubscriptionPayment::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
        ];

        return view('superadmin.subscriptions.plans', compact('plans', 'stats'));
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|unique:plans,slug|alpha_dash',
            'description' => 'nullable|string|max:500',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'max_staff' => 'required|integer|min:1',
            'max_services' => 'required|integer|min:1',
            'max_appointments_per_month' => 'required|integer|min:1',
        ]);

        Plan::create([
            ...$request->only([
                'name', 'slug', 'description',
                'price_monthly', 'price_yearly',
                'max_staff', 'max_services', 'max_appointments_per_month',
            ]),
            'inventory_enabled' => $request->boolean('inventory_enabled'),
            'analytics_enabled' => $request->boolean('analytics_enabled'),
            'commission_enabled' => $request->boolean('commission_enabled'),
            'is_active' => true,
            'sort_order' => Plan::max('sort_order') + 1,
        ]);

        Cache::forget('plan_slugs');

        return back()->with('success', "Plan \"{$request->name}\" created successfully.");
    }

    public function updatePlan(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'max_staff' => 'required|integer|min:1',
            'max_services' => 'required|integer|min:1',
            'max_appointments_per_month' => 'required|integer|min:1',
        ]);

        $plan->update([
            ...$request->only([
                'name', 'description', 'price_monthly', 'price_yearly',
                'max_staff', 'max_services', 'max_appointments_per_month',
            ]),
            'inventory_enabled' => $request->boolean('inventory_enabled'),
            'analytics_enabled' => $request->boolean('analytics_enabled'),
            'commission_enabled' => $request->boolean('commission_enabled'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        Cache::forget('plan_slugs');

        return back()->with('success', 'Plan updated successfully.');
    }

    // ── Subscriptions List ──────────────────────────────────────

    public function index(Request $request)
    {
        $query = Subscription::with(['tenant', 'plan'])->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }
        if ($request->filled('search')) {
            $query->whereHas('tenant', fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('subdomain', 'like', "%{$request->search}%")
            );
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'trial' => Subscription::where('status', 'trial')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
            'revenue' => SubscriptionPayment::where('status', 'paid')->sum('amount'),
            'expiring_soon' => Subscription::where('status', 'active')
                ->where('expires_at', '<=', now()->addDays(7))
                ->where('expires_at', '>', now())
                ->count(),
        ];

        $plans = Plan::where('is_active', true)->get();
        $tenants = Tenant::orderBy('name')->get(['id', 'name', 'subdomain']);

        return view('superadmin.subscriptions.index', compact(
            'subscriptions', 'stats', 'plans', 'tenants'
        ));
    }

    // ── Assign / Create Subscription ───────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'starts_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $startsAt = Carbon::parse($request->starts_at);
        $expiresAt = $request->billing_cycle === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        $amount = $request->billing_cycle === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        $subscription = Subscription::create([
            'tenant_id' => $request->tenant_id,
            'plan_id' => $plan->id,
            'billing_cycle' => $request->billing_cycle,
            'status' => 'active',
            'amount' => $amount,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'notes' => $request->notes,
        ]);

        // Update tenant's plan slug
        $tenant = Tenant::find($request->tenant_id);
        $tenant->update(['plan' => $plan->slug]);
        Cache::forget("tenant_plan_{$tenant->id}");
        // Create payment record if applicable
        if ($amount > 0) {
            SubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'tenant_id' => $request->tenant_id,
                'amount' => $amount,
                'payment_method' => $request->get('payment_method', 'manual'),
                'status' => 'paid',
                'transaction_id' => $request->get('transaction_id'),
                'paid_at' => now(),
            ]);
        }

        return redirect()->route('superadmin.subscriptions.index')
            ->with('success', 'Subscription assigned successfully.');
    }

    // ── Cancel Subscription ─────────────────────────────────────

    public function cancel(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Subscription cancelled successfully.');
    }

    // ── Renew Subscription ──────────────────────────────────────

    public function renew(Request $request, Subscription $subscription)
    {
        $request->validate([
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        $expiresAt = $request->billing_cycle === 'yearly'
            ? now()->addYear()
            : now()->addMonth();

        $amount = $request->billing_cycle === 'yearly'
            ? $subscription->plan->price_yearly
            : $subscription->plan->price_monthly;

        $newSub = Subscription::create([
            'tenant_id' => $subscription->tenant_id,
            'plan_id' => $subscription->plan_id,
            'billing_cycle' => $request->billing_cycle,
            'status' => 'active',
            'amount' => $amount,
            'starts_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        if ($amount > 0) {
            SubscriptionPayment::create([
                'subscription_id' => $newSub->id,
                'tenant_id' => $subscription->tenant_id,
                'amount' => $amount,
                'payment_method' => 'manual',
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        // Mark previous subscription as expired
        $subscription->update(['status' => 'expired']);

        return back()->with('success', 'Subscription renewed successfully.');
    }
}
