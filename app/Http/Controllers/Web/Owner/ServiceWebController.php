<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Plan;
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

        $services = $query->orderBy('name')->paginate(15);

        $counts = Service::where('tenant_id', $tenant->id)
                   ->selectRaw('is_active, COUNT(*) as count')
                   ->groupBy('is_active')
                   ->pluck('count', 'is_active');

        $stats = [
            'total' => $counts->sum(),
            'active' => $counts->get(1, 0),
            'inactive' => $counts->get(0, 0),
        ];

        return view('owner.services.index', compact('services', 'stats'));
    }

    public function store(Request $request)
    {
        $tenant = app('currentTenant');
        $plan = Plan::where('slug', $tenant->plan)->first();
        $currentServiceCount = Service::where('tenant_id', $tenant->id)->count();

        if (! $plan || $currentServiceCount >= ($plan->max_services ?? 0)) {
            return back()->withErrors(['limit' => 'Service limit reached for your current plan. Please upgrade to add more services.']);
        }
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

        return back()->with('success', "Service \"{$request->name}\" added successfully.");

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

        return back()->with('success', 'Service updated successfully.');

    }

    public function destroy($id)
    {
        $tenant = app('currentTenant');
        $service = Service::where('tenant_id', $tenant->id)->findOrFail($id);

        // Upcoming appointments check
        $upcomingCount = $service->appointments()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('appointment_date', '>=', today())
            ->count();

        if ($upcomingCount > 0) {
            return back()->withErrors(['limit' => "Cannot deactivate: {$upcomingCount} upcoming appointment(s) use this service.",
            ]);
        }

        $service->update(['is_active' => false]);

        return back()->with('success', 'Service deactivated successfully.');
    }
}
