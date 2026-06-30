<?php

namespace App\Observers;

use App\Jobs\LowStockAlertJob;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\CommissionTier;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ServiceProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AppointmentObserver
 *
 * Enforces transactional safety bounds and mitigates high-concurrency race condition anomalies.
 */
class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        AuditLog::record(
            'appointment.booked',
            Appointment::class,
            $appointment->id,
            [
                'customer_name' => $appointment->customer?->name,
                'service_name' => $appointment->service?->name,
                'staff_id' => $appointment->staff_id,
            ],
            $appointment->tenant_id,
            'booking'
        );
    }

    public function updated(Appointment $appointment): void
    {
        // Operate exclusively on explicit state changes
        if (! $appointment->wasChanged('status')) {
            return;
        }

        if ($appointment->status === 'cancelled') {
            AuditLog::record(
                'appointment.cancelled',
                Appointment::class,
                $appointment->id,
                [
                    'customer_name' => $appointment->customer?->name,
                    'service_name' => $appointment->service?->name,
                ],
                $appointment->tenant_id,
                'booking'
            );

            return;
        }

        if ($appointment->status === 'no_show') {
            AuditLog::record(
                'appointment.no_show',
                Appointment::class,
                $appointment->id,
                [
                    'customer_name' => $appointment->customer?->name,
                    'service_name' => $appointment->service?->name,
                    'staff_id' => $appointment->staff_id,
                ],
                $appointment->tenant_id,
                'booking'
            );

            return;
        }

        if ($appointment->status !== 'completed') {
            return;
        }

        DB::transaction(function () use ($appointment) {
            $lockedAppointment = Appointment::where('id', $appointment->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedAppointment) {
                return;
            }
            $alreadyExists = Commission::where('appointment_id', $lockedAppointment->id)
                ->lockForUpdate()
                ->exists();

            if (! $alreadyExists) {
                $this->calculateCommission($lockedAppointment);
                $this->deductInventory($lockedAppointment);

                AuditLog::record(
                    'appointment.completed',
                    Appointment::class,
                    $lockedAppointment->id,
                    [
                        'customer_name' => $lockedAppointment->customer?->name,
                        'service_name' => $lockedAppointment->service?->name,
                        'amount' => $lockedAppointment->amount,
                    ],
                    $lockedAppointment->tenant_id,
                    'booking'
                );
            }

        });
    }

    // ── Commission ──────────────────────────────────────────────────

    private function calculateCommission(Appointment $appointment): void
    {
        $service = $appointment->service;
        $staff = $appointment->staff;

        if (! $service || ! $staff) {
            Log::warning('Commission skipped — missing relation structures.', [
                'appointment_id' => $appointment->id,
            ]);

            return;
        }

        $monthlyRevenue = Commission::withoutGlobalScopes()
            ->where('staff_id', $staff->id)
            ->where('tenant_id', $appointment->tenant_id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', 'pending') // only settled commissions count toward the tier threshold
            ->sum('service_price');

        $monthlyRevenue += (float) $service->price;

        $tieredRate = CommissionTier::rateForStaff($staff->id, (float) $monthlyRevenue, $appointment->tenant_id);
        $basePercent = $tieredRate ?? (float) $staff->commission_percent;
        $effectivePercent = min($basePercent, 50.0);
        $commissionAmount = ($service->price * $effectivePercent) / 100;

        Commission::create([
            'tenant_id' => $appointment->tenant_id,
            'staff_id' => $staff->id,
            'appointment_id' => $appointment->id,
            'service_price' => $service->price,
            'commission_percent' => $effectivePercent,
            'commission_amount' => $commissionAmount,
            'status' => 'pending',
        ]);

        Log::info('Operational ledger: Commission calculated successfully.', [
            'staff_id' => $staff->id,
            'commission_amount' => '₹'.number_format($commissionAmount, 2),
            'appointment_id' => $appointment->id,
        ]);
    }

    // ── Inventory Deduction ────────────────────────────────────

    private function deductInventory(Appointment $appointment): void
    {
        $mappings = ServiceProduct::with('product')
            ->where('service_id', $appointment->service_id)
            ->where('tenant_id', $appointment->tenant_id)
            ->get();

        if ($mappings->isEmpty()) {
            return;
        }

        foreach ($mappings as $mapping) {
            $product = Product::lockForUpdate()->find($mapping->product_id);

            // Apply strict pessimistic database lock over raw target inventory product rows within thread
            if (! $product || ! $product->is_active) {
                continue;
            }

            // FIXED SEC-023: Idempotency check verified within active row lock isolation context safely
            $alreadyDeducted = InventoryTransaction::where('product_id', $product->id)
                ->where('type', 'appointment_deduct')
                ->where('reference_id', $appointment->id)
                ->exists();

            if ($alreadyDeducted) {
                continue;
            }

            $qtyToDeduct = $mapping->quantity_used;

            if ($product->quantity >= $qtyToDeduct) {

                // Perform dynamic column calculations safely within active memory
                $product->decrement('quantity', $qtyToDeduct);

                InventoryTransaction::create([
                    'tenant_id' => $appointment->tenant_id,
                    'product_id' => $product->id,
                    'type' => 'appointment_deduct',
                    'quantity' => $qtyToDeduct,
                    'reference_id' => $appointment->id,
                    'reason' => "Auto-deducted: Appointment #{$appointment->id} allocated tracking profile.",
                ]);

                Log::info('Operational Ledger: Inventory row decrement verified.', [
                    'product_id' => $product->id,
                    'qty_deducted' => $qtyToDeduct,
                    'appointment_id' => $appointment->id,
                ]);

                // Performance Fix: Evaluate directly over database mutation values without reloading whole schema models
                if ($product->quantity <= $product->low_stock_threshold) {
                    dispatch(new LowStockAlertJob($product));

                    Log::info('System Alert Notification: Low stock state threshold triggered.', [
                        'product_id' => $product->id,
                        'remaining' => $product->quantity,
                    ]);
                }

            } else {
                Log::warning('Process Warning: Insufficient inventory bounds to satisfy business automation requirement.', [
                    'product_id' => $product->id,
                    'required' => $qtyToDeduct,
                    'available' => $product->quantity,
                    'appointment_id' => $appointment->id,
                ]);

                InventoryTransaction::create([
                    'tenant_id' => $appointment->tenant_id,
                    'product_id' => $product->id,
                    'type' => 'appointment_deduct',
                    'quantity' => 0,
                    'reference_id' => $appointment->id,
                    'reason' => "SHORTFALL: Appointment #{$appointment->id} required {$qtyToDeduct} units, only {$product->quantity} available. No stock was actually deducted.",
                ]);

                dispatch(new LowStockAlertJob($product));
            }
        }
    }
}
