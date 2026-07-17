<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceProductController extends Controller
{
    /**
     * Fetch relational asset matrix configuration frameworks map screen.
     */
    public function index(Request $request)
    {
        $tenant = app('currentTenant');

        $services = Service::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // All mappings structured around explicit tenant identification keys
        $mappings = ServiceProduct::with(['service', 'product'])
            ->where('tenant_id', $tenant->id)
            ->get()
            ->groupBy('service_id');

        return view('owner.inventory.service-mapping', compact('services', 'products', 'mappings'));
    }

    /**
     * Build secure dependency configuration bounds mappings over tenant assets.
     */
    public function store(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where('tenant_id', $tenant->id),
            ],
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('tenant_id', $tenant->id),
            ],
            'quantity_used' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:20',
        ]);

        $service = Service::where('tenant_id', $tenant->id)->findOrFail($request->service_id);
        $product = Product::where('tenant_id', $tenant->id)->findOrFail($request->product_id);

        $existing = ServiceProduct::where('tenant_id', $tenant->id) // Security boundary mapping link reinforced
            ->where('service_id', $service->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->update([
                'quantity_used' => $request->quantity_used,
                'unit' => $request->unit,
            ]);

            return back()->with('success', "Success: Asset mapping metrics for \"{$service->name}\" updated successfully.");
        }

        ServiceProduct::create([
            'tenant_id' => $tenant->id,
            'service_id' => $service->id,
            'product_id' => $product->id,
            'quantity_used' => $request->quantity_used,
            'unit' => $request->unit,
        ]);

        return back()->with('success', "Success: Resource tracking configuration for \"{$product->name}\" bound to service profile \"{$service->name}\".");
    }

    /**
     * Delete tracking connections records securely.
     */
    public function destroy($id)
    {
        $tenant = app('currentTenant');

        $mapping = ServiceProduct::where('tenant_id', $tenant->id)->findOrFail($id);
        $mapping->delete();

        return back()->with('success', 'Success: Asset consumption mapping profile deleted successfully.');
    }

    /**
     * AJAX Endpoint fetching localized mapping dependencies for asynchronous operations rendering.
     */
    public function forService(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where('tenant_id', $tenant->id),
            ],
        ]);

        $mappings = ServiceProduct::with('product')
            ->where('tenant_id', $tenant->id)
            ->where('service_id', $request->service_id)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'product_id' => $m->product_id,
                'product_name' => $m->product?->name ?? 'Unknown Asset',
                'quantity_used' => $m->quantity_used,
                'unit' => $m->unit,
                'current_stock' => $m->product?->quantity ?? 0,
            ]);

        return response()->json($mappings);
    }
}
