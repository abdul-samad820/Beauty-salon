<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryWebController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('currentTenant');

        $query = Product::where('tenant_id', $tenant->id)->where('is_active', true);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filter === 'low') {
            $query->whereRaw('quantity <= low_stock_threshold');
        }

        $products = $query->orderBy('name')->get();

        $stats = [
            'total' => Product::where('tenant_id', $tenant->id)->where('is_active', true)->count(),
            'low_stock' => Product::where('tenant_id', $tenant->id)->where('is_active', true)->whereRaw('quantity <= low_stock_threshold')->count(),
            'total_value' => Product::where('tenant_id', $tenant->id)->where('is_active', true)->selectRaw('SUM(quantity * price) as val')->value('val') ?? 0,
        ];

        $recentTransactions = InventoryTransaction::with('product')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->take(10)
            ->get();

        return view('owner.inventory.index', compact('products', 'stats', 'recentTransactions'));
    }

    public function store(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
        ]);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? 5,
            'is_active' => true,
        ]);

        if ($request->quantity > 0) {
            InventoryTransaction::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $request->quantity,
                'reason' => 'Initial stock',
            ]);
        }

        return back()->with('success', "Product \"{$product->name}\" add ho gaya!");
    }

    public function stockIn(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = Product::where('tenant_id', $tenant->id)->findOrFail($request->product_id);
        $product->increment('quantity', $request->quantity);

        InventoryTransaction::create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => $request->quantity,
            'reason' => $request->reason ?? 'Stock added',
        ]);

        return back()->with('success', "{$request->quantity} units add ho gaye — {$product->name}");
    }

    public function stockOut(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = Product::where('tenant_id', $tenant->id)->findOrFail($request->product_id);

        if ($product->quantity < $request->quantity) {
            return back()->with('error', "Insufficient stock! Sirf {$product->quantity} units available hain.");
        }

        $product->decrement('quantity', $request->quantity);

        InventoryTransaction::create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => $request->quantity,
            'reason' => $request->reason ?? 'Stock used',
        ]);

        return back()->with('success', "{$request->quantity} units use ho gaye — {$product->name}");
    }
}
