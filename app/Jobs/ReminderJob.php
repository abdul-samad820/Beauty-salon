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

    // Agar job fail ho toh 3 baar retry karo
    public int $tries = 3;

    // Har retry ke beech 60 second wait karo
    public int $backoff = 60;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function handle(): void
    {
        // Appointment cancel toh nahi ho gayi beech me?
        if ($this->appointment->status === 'cancelled') {
            Log::info('Reminder skip — appointment cancelled', [
                'appointment_id' => $this->appointment->id,
            ]);

            return;
        }

        $customer = $this->appointment->customer;
        $service = $this->appointment->service;
        $staff = $this->appointment->staff->user;

        /*
        |--------------------------------------------------
        | STEP 1 — Email reminder bhejo
        |--------------------------------------------------
        */
        try {
            Mail::to($customer->email)
                ->send(new AppointmentReminderMail($this->appointment));

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

        /*
        |--------------------------------------------------
        | STEP 2 — WhatsApp Mock API call
        |--------------------------------------------------
        */
        $this->sendWhatsAppMock(
            phone: $customer->phone,
            message: "Reminder: Aapka appointment aaj {$this->appointment->start_time} pe hai. Service: {$service->name}. Staff: {$staff->name}."
        );

        /*
        |--------------------------------------------------
        | STEP 3 — reminder_sent flag true karo
        | Dobara reminder na jaaye isliye
        |--------------------------------------------------
        */
        $this->appointment->update(['reminder_sent' => true]);

        Log::info('Reminder completed', [
            'appointment_id' => $this->appointment->id,
        ]);
    }

    /*
    |------------------------------------------------------
    | WhatsApp Mock — Real API ki jagah fake call
    | Production me Twilio ya WhatsApp Business API lagega
    |------------------------------------------------------
    */
    private function sendWhatsAppMock(string $phone, string $message): void
    {
        // Seedha log me save karo — fake WhatsApp call
        Log::info('WhatsApp MOCK sent', [
            'to' => $phone,
            'message' => $message,
            'time' => now()->format('d M Y H:i'),
        ]);

        /*
        | Production me yahan aayega:
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

    // Job fail ho jaaye toh ye chalega
    public function failed(\Throwable $exception): void
    {
        Log::error('ReminderJob permanently failed', [
            'appointment_id' => $this->appointment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
