<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryValuationController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('currentTenant');
        $days = (int) $request->query('days', 30);
        $from = now()->subDays($days)->startOfDay();

        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $totalRetailValue = $products->sum(fn ($p) => $p->price * $p->quantity);
        $productsWithCost = $products->whereNotNull('cost_price');
        $totalCostValue = $productsWithCost->sum(fn ($p) => $p->cost_price * $p->quantity);
        $missingCostCount = $products->count() - $productsWithCost->count();

        $potentialProfit = $totalRetailValue - $totalCostValue;
        $marginPercent = $totalRetailValue > 0
            ? round(($potentialProfit / $totalRetailValue) * 100, 1)
            : 0;

        // Stock movement for the selected period, per product.
        $movements = InventoryTransaction::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $from)
            ->selectRaw("
                product_id,
                SUM(CASE WHEN type = 'in' THEN quantity ELSE 0 END) as stock_in,
                SUM(CASE WHEN type = 'out' THEN quantity ELSE 0 END) as stock_out
            ")
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $valuationRows = $products->map(function ($product) use ($movements) {
            $movement = $movements->get($product->id);
            $stockIn = $movement->stock_in ?? 0;
            $stockOut = $movement->stock_out ?? 0;
            // Closing stock is the current quantity; opening is derived by
            // reversing this period's net movement against it.
            $openingStock = $product->quantity - $stockIn + $stockOut;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'opening_stock' => max(0, $openingStock),
                'stock_in' => $stockIn,
                'stock_out' => $stockOut,
                'closing_stock' => $product->quantity,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->price,
                'cost_value' => $product->cost_price !== null
                    ? round($product->cost_price * $product->quantity, 2)
                    : null,
                'retail_value' => round($product->price * $product->quantity, 2),
                'is_low_stock' => $product->isLowStock(),
            ];
        });

        $stats = [
            'total_products' => $products->count(),
            'total_units' => $products->sum('quantity'),
            'total_cost_value' => round($totalCostValue, 2),
            'total_retail_value' => round($totalRetailValue, 2),
            'potential_profit' => round($potentialProfit, 2),
            'margin_percent' => $marginPercent,
            'missing_cost_count' => $missingCostCount,
        ];

        return view('owner.inventory.valuation', compact('valuationRows', 'stats', 'days'));
    }
}
