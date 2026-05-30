<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceWebController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('currentTenant');

        $query = Service::where('tenant_id', $tenant->id);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $services = $query->orderBy('name')->get();

        $stats = [
            'total' => Service::where('tenant_id', $tenant->id)->count(),
            'active' => Service::where('tenant_id', $tenant->id)->where('is_active', true)->count(),
            'inactive' => Service::where('tenant_id', $tenant->id)->where('is_active', false)->count(),
        ];

        return view('owner.services.index', compact('services', 'stats'));
    }

    public function store(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => ['required', Rule::in(['hair', 'skin', 'nail', 'bridal', 'massage', 'other'])],
            'duration_minutes' => 'required|integer|min:15',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        Service::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'category' => $request->category,
            'duration_minutes' => $request->duration_minutes,
            'price' => $request->price,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return back()->with('success', "Service \"{$request->name}\" add ho gayi!");
    }

    public function update(Request $request, $id)
    {
        $tenant = app('currentTenant');
        $service = Service::where('tenant_id', $tenant->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => ['required', Rule::in(['hair', 'skin', 'nail', 'bridal', 'massage', 'other'])],
            'duration_minutes' => 'required|integer|min:15',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $service->update($request->only(['name', 'category', 'duration_minutes', 'price', 'description', 'is_active']));

        return back()->with('success', 'Service update ho gayi!');
    }

    public function destroy($id)
    {
        $tenant = app('currentTenant');
        $service = Service::where('tenant_id', $tenant->id)->findOrFail($id);
        $service->update(['is_active' => false]);

        return back()->with('success', 'Service deactivate ho gayi.');
    }
}
