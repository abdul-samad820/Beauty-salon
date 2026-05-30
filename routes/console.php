<?php

use App\Jobs\ReminderJob;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/*
|----------------------------------------------------------
| SCHEDULER — Har 15 minute me chalega
| Upcoming appointments check karega
| Jo 2 ghante baad hain unhe reminder bhejega
|----------------------------------------------------------
*/
Schedule::call(function () {

    Log::info('Scheduler chala — '.now()->format('d M Y H:i'));

    // 2 ghante baad ka time nikalo
    $from = Carbon::now()->addHours(2)->format('H:i');
    $to = Carbon::now()->addHours(2)->addMinutes(15)->format('H:i');
    $today = Carbon::today()->toDateString();

    Log::info("Checking slots between {$from} and {$to}");

    /*
    | Aaj ke appointments dhundo jo:
    | 1. Abhi se 2 ghante baad hain
    | 2. Cancelled nahi hain
    | 3. Reminder abhi tak nahi gaya
    */
    $appointments = Appointment::with(['customer', 'service', 'staff.user'])
        ->whereDate('appointment_date', $today)
        ->whereBetween('start_time', [$from, $to])
        ->whereNotIn('status', ['cancelled'])
        ->where('reminder_sent', false)
        ->get();

    Log::info('Appointments mili: '.$appointments->count());

    // Har appointment ke liye job queue me daal do
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
    ->withoutOverlapping(); // Ek saath 2 baar na chale
