<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentBookedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmed! #'.$this->appointment->id.' — '.($this->appointment->tenant?->name ?? 'LUMIÈRE'),
        );
    }

    public function content(): Content
    {
        $appointment = $this->appointment;

        return new Content(
            view: 'emails.appointment-booked',
            with: [
                'customerName' => $appointment->customer?->name ?? 'Valued Client',
                'appointmentId' => $appointment->id,
                'serviceName' => $appointment->service?->name ?? 'Service',
                'staffName' => $appointment->staff?->user?->name ?? 'Our Stylist',
                'appointmentDate' => $appointment->appointment_date?->format('d M Y'),
                'startTime' => $appointment->start_time,
                'endTime' => $appointment->end_time,
                'parlourName' => $appointment->tenant?->name ?? 'Salon',
                'amount' => number_format($appointment->amount, 2),
            ],
        );
    }
}
