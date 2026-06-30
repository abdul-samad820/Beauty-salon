<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // ── Signature Verify karo ─────────────────────────────
        $webhookSecret = config('services.razorpay.webhook_secret');
        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('Razorpay webhook signature mismatch');

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('payload.payment.entity');

        Log::info('Razorpay webhook received', ['event' => $event]);

        match ($event) {
            'payment.captured' => $this->handlePaymentCaptured($data),
            'payment.failed' => $this->handlePaymentFailed($data),
            'refund.processed' => $this->handleRefundProcessed($data),
            default => Log::info('Unhandled webhook event: '.$event),
        };

        return response()->json(['status' => 'ok']);
    }

    // ── Payment Success ───────────────────────────────────────
    private function handlePaymentCaptured(array $data): void
    {
        $paymentId = $data['id'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $notes = $data['notes'] ?? [];

        if (! empty($notes['appointment_id'])) {
            $appointmentId = (int) $notes['appointment_id'];

            DB::transaction(function () use ($appointmentId, $paymentId) {
                $appointment = Appointment::lockForUpdate()->find($appointmentId);

                if (! $appointment || $appointment->payment_status === 'paid') {
                    return; // Already processed — idempotent exit
                }

                $appointment->update([
                    'payment_status' => 'paid',
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => 'webhook',
                    'status' => 'pending',
                ]);

                Log::info('Webhook: appointment payment reconciled', [
                    'appointment_id' => $appointmentId,
                    'payment_id' => $paymentId,
                ]);
            });

            return;
        }

        // ── PAY-02: Subscription payment via webhook ──────────
        $tenantId = $notes['tenant_id'] ?? null;
        $planId = $notes['plan_id'] ?? null;
        $billing = $notes['billing_cycle'] ?? 'monthly';

        if (! $tenantId || ! $planId) {
            Log::warning('Webhook: missing notes data', ['data' => $data]);

            return;
        }

        $tenant = Tenant::find($tenantId);
        $plan = Plan::find($planId);

        if (! $tenant || ! $plan) {
            return;
        }

        DB::transaction(function () use ($paymentId, $orderId, $tenant, $plan, $billing) {
            $alreadyProcessed = SubscriptionPayment::lockForUpdate()
                ->where('razorpay_payment_id', $paymentId)
                ->exists();

            if ($alreadyProcessed) {
                Log::info('Webhook: subscription payment already processed', ['payment_id' => $paymentId]);

                return;
            }

            $startsAt = now();
            $expiresAt = $billing === 'yearly'
                ? $startsAt->copy()->addYear()
                : $startsAt->copy()->addMonth();

            $amount = $billing === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

            Subscription::where('tenant_id', $tenant->id)
                ->whereIn('status', ['active', 'trial'])
                ->update(['status' => 'expired']);

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billing,
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
                'transaction_id' => $paymentId,
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => 'webhook',
                'paid_at' => now(),
            ]);

            $tenant->update(['plan' => $plan->slug]);
            Cache::forget("tenant_plan_{$tenant->id}");

            Log::info('Webhook: subscription activated', [
                'tenant_id' => $tenant->id,
                'plan' => $plan->slug,
            ]);
        });
    }

    // ── Payment Failed ────────────────────────────────────────
    private function handlePaymentFailed(array $data): void
    {
        $paymentId = $data['id'] ?? null;
        Log::warning('Webhook: payment failed', ['payment_id' => $paymentId]);
    }

    // ── Refund Processed ──────────────────────────────────────
    private function handleRefundProcessed(array $data): void
    {
        $paymentId = $data['payment_id'] ?? null;

        $payment = SubscriptionPayment::where('razorpay_payment_id', $paymentId)->first();

        if ($payment) {
            $payment->update(['status' => 'refunded']);
            Subscription::find($payment->subscription_id)?->update(['status' => 'expired']);

            Log::info('Webhook: refund processed, subscription expired', [
                'payment_id' => $paymentId,
            ]);
        }
    }
}
