<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $tenant,
        public $customer,
        public int $rating,
        public string $comment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Review Received — '.$this->tenant->name);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-review',
            with: [
                'tenantName' => $this->tenant->name,
                'customerName' => $this->customer->name,
                'rating' => $this->rating,
                'comment' => $this->comment,
            ]
        );
    }
}
