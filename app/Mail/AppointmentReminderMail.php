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

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Appointment $appointment
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: Your appointment is in 2 hours!',
        );
    }

    /**
     * Get the message content definition.
     */
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
