<?php

namespace App\Jobs;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry the job up to 3 times if it fails
    public int $tries = 3;

    // Wait 60 seconds between retries
    public int $backoff = 60;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function handle(): void
    {
        if ($this->appointment->status === 'cancelled') {
            Log::info('Reminder skipped — appointment cancelled', [
                'appointment_id' => $this->appointment->id,
            ]);

            return;
        }

        $customer = $this->appointment->customer;
        $service = $this->appointment->service;
        $staff = $this->appointment->staff?->user;

        // Null safety check — agar koi bhi missing ho to job skip karo
        if (! $customer || ! $service) {
            Log::warning('Reminder skipped — missing customer or service', [
                'appointment_id' => $this->appointment->id,
            ]);

            return;
        }

        // STEP 1 — Send email reminder
        $emailSent = false;

        try {
            Mail::to($customer->email)->send(new AppointmentReminderMail($this->appointment));
            $emailSent = true;
            Log::info('Email reminder sent', [
                'appointment_id' => $this->appointment->id,
                'customer_email' => $customer->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Email reminder failed', [
                'appointment_id' => $this->appointment->id,
                'error' => $e->getMessage(),
            ]);
        }

        // STEP 2 — WhatsApp Mock
        $this->sendWhatsAppMock(
            phone: $customer->phone,
            message: "Reminder: Your appointment is scheduled for today at {$this->appointment->start_time}. Service: {$service->name}. Staff: ".($staff?->name ?? 'our team').'.'
        );

        // STEP 3 — Sirf tab mark karo jab email gaya ho
        if ($emailSent) {
            $this->appointment->update(['reminder_sent' => true]);
            Log::info('Reminder process completed', ['appointment_id' => $this->appointment->id]);
        }
    }

    /*
    |------------------------------------------------------
    | WhatsApp Mock — Simulation of an external API call
    | In production, this will integrate with Twilio or the WhatsApp Business API
    |------------------------------------------------------
    */
    private function sendWhatsAppMock(string $phone, string $message): void
    {
        Log::info('WhatsApp MOCK sent', [
            'to' => $phone,
            'message' => $message,
            'time' => now()->format('d M Y H:i'),
        ]);

        /*
        | Implementation Example for Production:
        |
        | $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        | $twilio->messages->create(
        |     'whatsapp:+91' . $phone,
        |     [
        |         'from' => 'whatsapp:' . env('TWILIO_WHATSAPP_FROM'),
        |         'body' => $message
        |     ]
        | );
        */
    }

    // This method is called if the job fails permanently
    public function failed(\Throwable $exception): void
    {
        Log::error('ReminderJob permanently failed', [
            'appointment_id' => $this->appointment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
