<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceProductController extends Controller
{
    /**
     * Display service → product mapping matrix.
     */
    public function index()
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

        $mappings = ServiceProduct::with(['product'])
            ->where('tenant_id', $tenant->id)
            ->get()
            ->groupBy('service_id');

        return view('owner.inventory.service-mapping', compact('services', 'products', 'mappings'));
    }

    /**
     * Store a new service → product mapping.
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
            'unit' => 'nullable|string|max:50',
        ]);

        // Prevent duplicate mapping for same service + product combo
        $exists = ServiceProduct::where('tenant_id', $tenant->id)
            ->where('service_id', $request->service_id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Error: This service → product mapping already exists. Update the quantity instead.');
        }

        ServiceProduct::create([
            'tenant_id' => $tenant->id,
            'service_id' => $request->service_id,
            'product_id' => $request->product_id,
            'quantity_used' => $request->quantity_used,
            'unit' => $request->unit,
        ]);

        return back()->with('success', 'Success: Service product mapping profile created successfully.');
    }

    /**
     * Delete a service → product mapping.
     */
    public function destroy($id)
    {
        $tenant = app('currentTenant');

        $mapping = ServiceProduct::where('tenant_id', $tenant->id)->findOrFail($id);
        $mapping->delete();

        return back()->with('success', 'Success: Mapping dependency layer removed successfully.');
    }

    /**
     * Return products mapped to a given service (AJAX / JSON).
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
                'product_name' => $m->product?->name,
                'quantity_used' => $m->quantity_used,
                'unit' => $m->unit,
            ]);

        return response()->json($mappings);
    }
}
