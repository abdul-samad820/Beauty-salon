<?php

namespace App\Jobs;

use App\Mail\LowStockMail;
use App\Models\AuditLog;
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

        $ownerEmail = $product->tenant->email ?? null;

        if ($ownerEmail) {
            try {
                Mail::to($ownerEmail)->send(new LowStockMail($product));
            } catch (\Exception $e) {
                Log::error('LowStockAlertJob: Mail failed', [
                    'tenant_id' => $product->tenant_id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('LowStockAlertJob: No owner email found for tenant', [
                'tenant_id' => $product->tenant_id,
            ]);
        }
        AuditLog::record(
            'stock.low',
            Product::class,
            $product->id,
            [
                'product_name' => $product->name,
                'quantity' => $product->quantity,
                'threshold' => $product->low_stock_threshold,
            ],
            $product->tenant_id,
            'stock'
        );
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
