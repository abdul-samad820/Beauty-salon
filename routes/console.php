<?php

use App\Jobs\ReminderJob;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {

    Log::info('Scheduler executed — '.now()->format('d M Y H:i'));

    // Define the time window (2 hours from now)
    $from = Carbon::now()->addHours(2)->format('H:i');
    $to = Carbon::now()->addHours(2)->addMinutes(15)->format('H:i');
    $today = Carbon::today()->toDateString();

    Log::info("Checking slots between {$from} and {$to}");

    $appointments = Appointment::withoutGlobalScopes()
        ->with(['customer', 'service', 'staff.user'])
        ->whereDate('appointment_date', $today)
        ->whereBetween('start_time', [$from, $to])
        ->whereNotIn('status', ['cancelled'])
        ->where('reminder_sent', false)
        ->get();

    Log::info('Appointments found: '.$appointments->count());

    // Dispatch a job for each appointment
    foreach ($appointments as $appointment) {
        ReminderJob::dispatch($appointment);

        Log::info('ReminderJob dispatched', [
            'appointment_id' => $appointment->id,
            'customer' => $appointment->customer->name,
            'time' => $appointment->start_time,
        ]);
    }

})->everyFifteenMinutes()
    ->name('send-appointment-reminders')
    ->withoutOverlapping(); // Prevents overlapping executions

Schedule::command('backup:run')->daily()->at('02:00');

Schedule::call(function () {

    $expired = Subscription::withoutGlobalScopes()
        ->where('status', 'active')
        ->where('expires_at', '<', now())
        ->get();

    foreach ($expired as $subscription) {
        $subscription->update(['status' => 'expired']);

        Log::info('Subscription expired', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'expired_at' => $subscription->expires_at,
        ]);
    }

    Log::info('Subscription expiry check complete', [
        'expired_count' => $expired->count(),
        'checked_at' => now()->format('d M Y H:i'),
    ]);

})->daily()
    ->name('expire-subscriptions')
    ->withoutOverlapping();

Schedule::call(function () {
    AuditLog::where('is_read', true)
        ->where('created_at', '<', now()->subDays(30))
        ->delete();

    Log::info('AuditLog cleanup complete — old read notifications purged');
})->weekly()
    ->name('prune-audit-logs')
    ->withoutOverlapping();
