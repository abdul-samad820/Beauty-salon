<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Retrieve all products.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $products = Product::where(
            'tenant_id',
            app('currentTenant')->id
        )
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'low_stock_threshold' => $product->low_stock_threshold,
                'is_low_stock' => $product->isLowStock(),
            ];
        });

        return response()->json([
            'message' => 'Products fetched successfully',
            'total' => $products->count(),
            'data' => $products,
        ]);
    }

    /**
     * Store a new product.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
        ]);

        $product = DB::transaction(function () use ($request) {

            $product = Product::create([
                'tenant_id' => app('currentTenant')->id,
                'name' => $request->name,
                'category' => $request->category,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'low_stock_threshold' => $request->low_stock_threshold ?? 5,
                'is_active' => true,
            ]);

            if ($request->quantity > 0) {
                InventoryTransaction::create([
                    'tenant_id' => app('currentTenant')->id,
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $request->quantity,
                    'reason' => 'Initial stock',
                ]);
            }

            return $product;
        });

        return response()->json([
            'message' => 'Product added successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Retrieve a specific product and its transaction history.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $product = Product::where(
            'tenant_id',
            app('currentTenant')->id
        )->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        // Retrieve product transaction history
        $transactions = InventoryTransaction::where(
            'tenant_id',
            app('currentTenant')->id
        )
            ->where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Product fetched successfully',
            'data' => $product,
            'history' => $transactions,
        ]);
    }

    /**
     * Update an existing product.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::where(
            'tenant_id',
            app('currentTenant')->id
        )->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:1',
        ]);

        $product->update($request->only([
            'name', 'category', 'price', 'low_stock_threshold',
        ]));

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    /**
     * Remove (soft-delete) a product.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $product = Product::where(
            'tenant_id',
            app('currentTenant')->id
        )->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        $product->update(['is_active' => false]);

        return response()->json([
            'message' => 'Product removed successfully',
        ]);
    }

    /**
     * Retrieve products that have reached low stock levels.
     *
     * @return JsonResponse
     */
    public function lowStock()
    {
        $products = Product::where(
            'tenant_id',
            app('currentTenant')->id
        )
            ->where('is_active', true)
            ->whereRaw('quantity <= low_stock_threshold')
            ->get();

        return response()->json([
            'message' => 'Low stock products',
            'total' => $products->count(),
            'data' => $products,
        ]);
    }
}
