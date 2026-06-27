<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayController extends Controller
{
    private Api $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    // ── Billing Page — Show plans to owner ───────────────────────
    public function billing()
    {
        $tenant = app('currentTenant');

        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $currentSubscription = Subscription::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'trial'])
            ->latest()
            ->first();

        return view('owner.subscription.billing', compact(
            'plans',
            'currentSubscription',
            'tenant'
        ));
    }

    // ── Create Razorpay Order ─────────────────────────────────────
    public function createOrder(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $tenant = app('currentTenant');
        $plan = Plan::findOrFail($request->plan_id);

        $amount = $request->billing_cycle === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        if ($amount == 0) {
            Subscription::where('tenant_id', $tenant->id)
                ->whereIn('status', ['active', 'trial'])
                ->update(['status' => 'expired']);

            $startsAt = now();
            $expiresAt = $request->billing_cycle === 'yearly'
                ? $startsAt->copy()->addYear()
                : $startsAt->copy()->addMonth();

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $request->billing_cycle,
                'status' => 'active',
                'amount' => 0,
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
            ]);
            Cache::forget("tenant_plan_{$tenant->id}");

            return response()->json([
                'success' => true,
                'free_plan' => true,
                'redirect' => route('owner.dashboard'),
            ]);

        }
        // Razorpay amount is in paise (1 INR = 100 paise)
        $amountInPaise = (int) ($amount * 100);

        try {
            $order = $this->razorpay->order->create([
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => 'lumiere_'.$tenant->id.'_'.time(),
                'notes' => [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'billing_cycle' => $request->billing_cycle,
                ],
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'key_id' => config('services.razorpay.key_id'),
                'tenant_name' => $tenant->name,
                'tenant_email' => auth()->user()->email,
                'plan_name' => $plan->name,
                'billing_cycle' => $request->billing_cycle,
            ]);

        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed. Please try again.',
            ], 500);
        }
    }

    // ── Verify Payment & Activate Subscription ────────────────────
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $tenant = app('currentTenant');

        // ── Signature Verification ────────────────────────────────
        try {
            $this->razorpay->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay signature verification failed', [
                'tenant_id' => $tenant->id,
                'order_id' => $request->razorpay_order_id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('owner.billing')
                ->with('error', 'Payment verification failed. Please contact support if amount was deducted.');
        }

        // ── Duplicate Payment Guard ───────────────────────────────
        $alreadyProcessed = SubscriptionPayment::where('razorpay_payment_id', $request->razorpay_payment_id)->exists();

        if ($alreadyProcessed) {
            return redirect()->route('owner.dashboard')
                ->with('success', 'Subscription already activated.');
        }

        // ── Activate Subscription ─────────────────────────────────
        $plan = Plan::findOrFail($request->plan_id);
        $startsAt = now();
        $expiresAt = $request->billing_cycle === 'yearly'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        $amount = $request->billing_cycle === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        // Expire any existing active subscription
        Subscription::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'expired']);

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $request->billing_cycle,
            'status' => 'active',
            'amount' => $amount,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);

        SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $tenant->id,
            'amount' => $amount,
            'payment_method' => 'razorpay',
            'status' => 'paid',
            'transaction_id' => $request->razorpay_payment_id,
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => hash('sha256', $request->razorpay_signature),
            'paid_at' => now(),
        ]);

        // Update tenant plan slug
        $tenant->update(['plan' => $plan->slug]);

        Cache::forget("tenant_plan_{$tenant->id}");

        Log::info('Subscription activated via Razorpay', [
            'tenant_id' => $tenant->id,
            'plan' => $plan->slug,
            'billing_cycle' => $request->billing_cycle,
            'payment_id' => $request->razorpay_payment_id,
        ]);

        return redirect()->route('owner.dashboard')
            ->with('success', "Plan \"{$plan->name}\" activated successfully! Valid till ".$expiresAt->format('d M Y').'.');
    }
}
