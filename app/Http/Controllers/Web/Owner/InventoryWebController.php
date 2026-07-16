<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InventoryWebController extends Controller
{
    /**
     * Display the inventory ledger and statistics.
     */
    public function index(Request $request)
    {
        $tenant = app('currentTenant');
        if (! $tenant->canUseFeature('inventory_enabled')) {
            return redirect()->route('owner.dashboard')
                ->with('error', 'Inventory feature is not available on your current plan.');
        }

        $query = Product::where('tenant_id', $tenant->id)->where('is_active', true);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->input('tab') === 'low') {
            $query->whereRaw('quantity <= low_stock_threshold');
        }

        $products = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

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

    /**
     * Register a new inventory item.
     */
    public function store(Request $request)
    {
        $tenant = app('currentTenant');
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                return back()->withErrors(['image' => 'Uploaded file is not a valid image.']);
            }
        }
        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'cost_price' => $request->cost_price,
            'quantity' => $request->quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? 5,
            'is_active' => true,
            'image' => $request->hasFile('image') ? $request->file('image')->store('products', 'cloudinary') : null,
        ]);

        if ($request->quantity > 0) {
            InventoryTransaction::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $request->quantity,
                'reason' => 'Initial stock registration.',
            ]);
        }

        return back()->with('success', "Success: The product inventory profile for \"{$product->name}\" has been created.");
    }

    /**
     * Process incoming stock.
     */
    public function stockIn(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('tenant_id', $tenant->id),
            ],
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);
        $product = DB::transaction(function () use ($tenant, $request) {
            $product = Product::where('tenant_id', $tenant->id)
                ->lockForUpdate()
                ->findOrFail($request->product_id);

            $product->increment('quantity', $request->quantity);

            InventoryTransaction::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $request->quantity,
                'reason' => $request->reason ?? 'Manual stock restock.',
            ]);

            return $product;
        });

        return back()->with('success', "Success: {$request->quantity} units added to inventory for \"{$product->name}\".");
    }

    /**
     * Process outgoing stock.
     */
    public function stockOut(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('tenant_id', $tenant->id),
            ],
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $result = DB::transaction(function () use ($tenant, $request) {
            $product = Product::where('tenant_id', $tenant->id)
                ->lockForUpdate()
                ->findOrFail($request->product_id);

            if ($product->quantity < $request->quantity) {
                return ['error' => "Processing Blocked: Insufficient inventory. Only {$product->quantity} units remaining."];
            }

            $product->decrement('quantity', $request->quantity);

            InventoryTransaction::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => $request->quantity,
                'reason' => $request->reason ?? 'Service usage.',
            ]);

            return ['success' => "Success: {$request->quantity} units deducted from inventory for \"{$product->name}\"."];
        });

        return isset($result['error'])
            ? back()->with('error', $result['error'])
            : back()->with('success', $result['success']);
    }

    /**
     * Update existing product details.
     */
    public function update(Request $request, $id)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $product = Product::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                return back()->withErrors(['image' => 'Uploaded file is not a valid image.']);
            }
        }

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($imagePath) {
                Storage::disk('cloudinary')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'cloudinary');
        }

        $product->update([
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'cost_price' => $request->cost_price,
            'low_stock_threshold' => $request->low_stock_threshold ?? 5,
            'image' => $imagePath,
        ]);

        return back()->with('success', "Product \"{$product->name}\" updated successfully.");
    }
}
