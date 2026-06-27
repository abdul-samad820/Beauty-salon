<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Tests\TestCase;

class RazorpayWebhookTest extends TestCase
{
    private string $webhookSecret = 'test_webhook_secret_123';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.razorpay.webhook_secret' => $this->webhookSecret]);
    }

    private function makeSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->webhookSecret);
    }

    private function makeUniqueTenant(): Tenant
    {
        $uid = uniqid();

        return $this->createTenant([
            'name' => 'Test Salon '.$uid,
            'slug' => 'test-salon-'.$uid,
            'subdomain' => 'test-salon-'.$uid,
            'email' => 'salon-'.$uid.'@test.com',
        ]);
    }

    private function createPlan(array $overrides = []): Plan
    {
        return Plan::create(array_merge([
            'name' => 'Pro',
            'slug' => 'pro-'.uniqid(),
            'price_monthly' => 999,
            'price_yearly' => 9999,
            'max_staff' => 10,
            'max_services' => 50,
            'max_appointments_per_month' => 500,
            'features' => [],
            'is_active' => true,
        ], $overrides));
    }

    private function capturedPayload(int $tenantId, int $planId, string $paymentId = 'pay_test123'): array
    {
        return [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'order_id' => 'order_test456',
                        'notes' => [
                            'tenant_id' => $tenantId,
                            'plan_id' => $planId,
                            'billing_cycle' => 'monthly',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_valid_webhook_activates_subscription(): void
    {
        $tenant = $this->makeUniqueTenant(); // FIX: unique tenant
        $plan = $this->createPlan();

        $payload = json_encode($this->capturedPayload($tenant->id, $plan->id));
        $sig = $this->makeSignature($payload);

        $response = $this->postJson('/razorpay/webhook', json_decode($payload, true), [
            'X-Razorpay-Signature' => $sig,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('subscription_payments', [
            'tenant_id' => $tenant->id,
            'razorpay_payment_id' => 'pay_test123',
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'plan' => $plan->slug,
        ]);
    }

    public function test_invalid_webhook_signature_returns_400(): void
    {
        $tenant = $this->makeUniqueTenant(); // FIX: unique tenant
        $plan = $this->createPlan();

        $payload = json_encode($this->capturedPayload($tenant->id, $plan->id));

        $response = $this->postJson('/razorpay/webhook', json_decode($payload, true), [
            'X-Razorpay-Signature' => 'fake_signature_hacker_ne_bheji',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid signature']);

        $this->assertDatabaseMissing('subscriptions', [
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_duplicate_webhook_is_ignored(): void
    {
        $tenant = $this->makeUniqueTenant();
        $plan = $this->createPlan();

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'amount' => 999,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $tenant->id,
            'amount' => 999,
            'payment_method' => 'razorpay',
            'status' => 'paid',
            'transaction_id' => 'pay_duplicate123',
            'razorpay_payment_id' => 'pay_duplicate123',
            'paid_at' => now(),
        ]);

        $payload = json_encode($this->capturedPayload($tenant->id, $plan->id, 'pay_duplicate123'));
        $sig = $this->makeSignature($payload);

        $response = $this->postJson('/razorpay/webhook', json_decode($payload, true), [
            'X-Razorpay-Signature' => $sig,
        ]);

        $response->assertStatus(200);

        $this->assertCount(
            1,
            SubscriptionPayment::where('razorpay_payment_id', 'pay_duplicate123')->get()
        );
    }

    public function test_webhook_with_missing_notes_is_ignored_gracefully(): void
    {
        $payload = json_encode([
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_no_notes',
                        'order_id' => 'order_no_notes',
                        'notes' => [],
                    ],
                ],
            ],
        ]);

        $sig = $this->makeSignature($payload);

        $response = $this->postJson('/razorpay/webhook', json_decode($payload, true), [
            'X-Razorpay-Signature' => $sig,
        ]);

        $response->assertStatus(200);

        $this->assertEquals(0, Subscription::count());
    }

    public function test_payment_failed_event_returns_ok(): void
    {
        $payload = json_encode([
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_failed_xyz',
                    ],
                ],
            ],
        ]);

        $sig = $this->makeSignature($payload);

        $response = $this->postJson('/razorpay/webhook', json_decode($payload, true), [
            'X-Razorpay-Signature' => $sig,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }

    public function test_refund_processed_expires_subscription(): void
    {
        $tenant = $this->makeUniqueTenant(); // FIX: unique tenant
        $plan = $this->createPlan();

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'amount' => 999,
            'starts_at' => now()->subMonth(),
            'expires_at' => now()->addMonth(),
        ]);

        $payment = SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $tenant->id,
            'amount' => 999,
            'payment_method' => 'razorpay',
            'status' => 'paid',
            'transaction_id' => 'pay_refund_test',
            'razorpay_payment_id' => 'pay_refund_test',
            'paid_at' => now()->subMonth(),
        ]);

        $payload = json_encode([
            'event' => 'refund.processed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'payment_id' => 'pay_refund_test',
                    ],
                ],
            ],
        ]);

        $sig = $this->makeSignature($payload);

        $response = $this->postJson('/razorpay/webhook', json_decode($payload, true), [
            'X-Razorpay-Signature' => $sig,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscription_payments', [
            'id' => $payment->id,
            'status' => 'refunded',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'expired',
        ]);
    }
}
