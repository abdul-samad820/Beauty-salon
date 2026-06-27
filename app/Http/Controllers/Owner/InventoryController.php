<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Jobs\LowStockAlertJob;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    /**
     * Add stock to inventory.
     *
     * @return JsonResponse
     */
    public function stockIn(Request $request)
    {
        $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')
                    ->where('tenant_id', app('currentTenant')->id),
            ],
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        $product = null;

        DB::transaction(function () use ($request, &$product) {

            $product = Product::where(
                'tenant_id',
                app('currentTenant')->id
            )
                ->lockForUpdate()
                ->findOrFail($request->product_id);

            $product->increment(
                'quantity',
                $request->quantity
            );

            InventoryTransaction::create([
                'tenant_id' => app('currentTenant')->id,
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $request->quantity,
                'reason' => $request->reason ?? 'Stock added',
            ]);
        });

        return response()->json([
            'message' => 'Stock added successfully',
            'product' => $product->name,
            'added' => $request->quantity,
            'total_stock' => $product->fresh()->quantity,
        ]);
    }

    /**
     * Deduct stock (e.g., used in service).
     *
     * @return JsonResponse
     */
    public function stockOut(Request $request)
    {
        $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')
                    ->where('tenant_id', app('currentTenant')->id),
            ],
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);

        try {

            $product = null;

            DB::transaction(function () use ($request, &$product) {

                $product = Product::where(
                    'tenant_id',
                    app('currentTenant')->id
                )
                    ->lockForUpdate()
                    ->findOrFail($request->product_id);

                if ($product->quantity < $request->quantity) {

                    throw new \RuntimeException(
                        'Insufficient stock!'
                    );
                }

                $product->decrement(
                    'quantity',
                    $request->quantity
                );

                InventoryTransaction::create([
                    'tenant_id' => app('currentTenant')->id,
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $request->quantity,
                    'reason' => $request->reason ?? 'Service use',
                    'appointment_id' => $request->appointment_id,
                ]);
            });

        } catch (\RuntimeException $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }

        $updatedProduct = $product->fresh();

        // Trigger low stock alert if the threshold is reached
        if (
            $updatedProduct->quantity ==
            $updatedProduct->low_stock_threshold
        ) {
            LowStockAlertJob::dispatch(
                $updatedProduct
            );
        }

        return response()->json([
            'message' => 'Stock deducted successfully',
            'product' => $updatedProduct->name,
            'used' => $request->quantity,
            'remaining_stock' => $updatedProduct->quantity,
            'is_low_stock' => $updatedProduct->isLowStock(),
        ]);
    }
}
