<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Product $product
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Low Stock Alert: '.$this->product->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.low-stock-alert',
            with: [
                'ownerName' => $this->product->tenant->name,
                'parlourName' => $this->product->tenant->name,
                'productName' => $this->product->name,
                'currentStock' => $this->product->quantity,
                'threshold' => $this->product->low_stock_threshold,
                'unit' => $this->product->unit ?? null,
            ]
        );
    }
}
