<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LowStockAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public Product $product
    ) {}

    public function handle(): void
    {
        $product = $this->product;

        Log::warning('LOW STOCK ALERT', [
            'product' => $product->name,
            'current_quantity' => $product->quantity,
            'threshold' => $product->low_stock_threshold,
            'tenant_id' => $product->tenant_id,
        ]);

        /*
        | Production me yahan owner ko email jaayegi:
        |
        | Mail::to($product->tenant->email)
        |     ->send(new LowStockMail($product));
        */
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('LowStockAlertJob failed', [
            'tenant_id' => $this->product->tenant_id,
            'product' => $this->product->name,
            'error' => $exception->getMessage(),
        ]);
    }
}
