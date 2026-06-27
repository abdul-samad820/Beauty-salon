<?php

namespace Tests\Feature;

use App\Jobs\LowStockAlertJob;
use App\Jobs\ReminderJob;
use App\Mail\AppointmentReminderMail;
use App\Mail\LowStockMail;
use App\Models\Product;
use App\Models\ServiceProduct;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * JOB TESTS
 *
 * Covers:
 *  12. LowStockAlertJob actually sends email to tenant owner
 *  13. ReminderJob marks reminder_sent = true after sending email
 *
 * Uses Mail::fake() so no real emails are sent.
 * Also verifies the job is dispatched when inventory crosses the threshold.
 */
class LowStockAlertJobTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 12a: LowStockAlertJob sends LowStockMail to tenant email
    // ──────────────────────────────────────────────

    public function test_low_stock_alert_job_sends_email_to_tenant_owner(): void
    {
        Mail::fake();

        $tenant = $this->createTenant(['email' => 'owner@salon.com']);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Low Dye',
            'category' => 'hair',
            'price' => 100,
            'quantity' => 2,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        // Run the job synchronously
        (new LowStockAlertJob($product))->handle();

        Mail::assertSent(LowStockMail::class, function ($mail) {
            return $mail->hasTo('owner@salon.com');
        });
    }

    // ──────────────────────────────────────────────
    // TEST 12b: LowStockAlertJob is dispatched when inventory crosses threshold
    //           (triggered via AppointmentObserver after completion)
    // ──────────────────────────────────────────────

    public function test_low_stock_alert_job_is_dispatched_when_stock_drops_below_threshold(): void
    {
        Queue::fake();

        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);

        // Stock: 3, threshold: 3 → completing appointment puts it at 1 → triggers alert
        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Near-Empty Product',
            'category' => 'hair',
            'price' => 200,
            'quantity' => 3,
            'low_stock_threshold' => 3,
            'is_active' => true,
        ]);

        ServiceProduct::create([
            'tenant_id' => $tenant->id,
            'service_id' => $service->id,
            'product_id' => $product->id,
            'quantity_used' => 2,
            'unit' => 'ml',
        ]);

        $appointment = $this->createAppointment($tenant, $customer, $staff, $service);
        $this->bindTenant($tenant);

        $this->patchJson(
            "/api/v1/owner/appointments/{$appointment->id}/status",
            ['status' => 'completed'],
            $this->ownerHeaders($owner, $tenant)
        )->assertStatus(200);

        Queue::assertPushed(LowStockAlertJob::class, function ($job) use ($product) {
            return $job->product->id === $product->id;
        });
    }

    // ──────────────────────────────────────────────
    // TEST 12c: LowStockAlertJob does NOT send email if tenant has no email
    // ──────────────────────────────────────────────

    public function test_low_stock_alert_job_skips_email_when_no_tenant_email(): void
    {
        Mail::fake();

        $tenant = $this->createTenant(['email' => '']); // no owner email

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'No-Owner Product',
            'category' => 'hair',
            'price' => 50,
            'quantity' => 1,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        // Should not throw; should simply skip mail
        (new LowStockAlertJob($product))->handle();

        Mail::assertNotSent(LowStockMail::class);
    }

    // ──────────────────────────────────────────────
    // TEST 13a: ReminderJob marks reminder_sent = true after sending email
    // ──────────────────────────────────────────────

    public function test_reminder_job_marks_reminder_sent_true_after_email(): void
    {
        Mail::fake();

        $tenant = $this->createTenant();
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'confirmed',
            'reminder_sent' => false,
        ]);

        // Run job synchronously
        (new ReminderJob($appointment))->handle();

        $appointment->refresh();
        $this->assertTrue((bool) $appointment->reminder_sent);

        Mail::assertSent(AppointmentReminderMail::class);
    }

    // ──────────────────────────────────────────────
    // TEST 13b: ReminderJob does NOT mark reminder_sent for cancelled appointment
    // ──────────────────────────────────────────────

    public function test_reminder_job_skips_cancelled_appointment(): void
    {
        Mail::fake();

        $tenant = $this->createTenant();
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'cancelled',
            'reminder_sent' => false,
        ]);

        (new ReminderJob($appointment))->handle();

        $appointment->refresh();
        $this->assertFalse((bool) $appointment->reminder_sent);

        Mail::assertNotSent(AppointmentReminderMail::class);
    }

    // ──────────────────────────────────────────────
    // TEST 13c: reminder_sent stays false if mail fails
    // ──────────────────────────────────────────────

    public function test_reminder_sent_stays_false_when_mail_throws(): void
    {
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP down'));

        $tenant = $this->createTenant();
        $customer = $this->createCustomer($tenant);
        $staff = $this->createStaff($tenant);
        $service = $this->createService($tenant);
        $appointment = $this->createAppointment($tenant, $customer, $staff, $service, [
            'status' => 'confirmed',
            'reminder_sent' => false,
        ]);

        (new ReminderJob($appointment))->handle();

        $appointment->refresh();
        // Email failed → reminder_sent must remain false
        $this->assertFalse((bool) $appointment->reminder_sent);
    }
}
