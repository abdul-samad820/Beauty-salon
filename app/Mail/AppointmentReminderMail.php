<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: Aapka appointment 2 ghante baad hai!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment-reminder',
            with: [
                'customerName' => $this->appointment->customer->name,
                'serviceName' => $this->appointment->service->name,
                'staffName' => $this->appointment->staff->user->name,
                'appointmentDate' => $this->appointment->appointment_date->format('d M Y'),
                'startTime' => $this->appointment->start_time,
                'endTime' => $this->appointment->end_time,
            ]
        );
    }
}
