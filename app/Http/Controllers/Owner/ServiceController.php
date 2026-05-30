<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    // Saari services fetch karo
    public function index()
    {
        $services = Service::where(
    'tenant_id',
    app('currentTenant')->id
)
->where('is_active', true)
->paginate(20);

        return response()->json([
            'message' => 'Services fetched successfully',
            'data' => $services,
        ]);
    }

    // Nayi service banao
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services')
                    ->where(
                        'tenant_id',
                        app('currentTenant')->id
                    ),
            ],
            'category' => 'required|in:hair,skin,nail,bridal,massage,other',
            'duration_minutes' => 'required|integer|min:15',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $service = Service::create([
    'tenant_id' => app('currentTenant')->id,

    'name' => $request->name,
    'description' => $request->description,
    'category' => $request->category,
    'duration_minutes' => $request->duration_minutes,
    'price' => $request->price,
    'is_active' => true,
]);

        return response()->json([
            'message' => 'Service created successfully',
            'data' => $service,
        ], 201);
    }

    // Single service dekho
    public function show($id)
    {
        $service = Service::where(
            'tenant_id',
            app('currentTenant')->id
        )->findOrFail($id);

        if (! $service) {
            return response()->json([
                'message' => 'Service not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Service fetched successfully',
            'data' => $service,
        ]);
    }

    // Service update karo
    public function update(Request $request, $id)
    {
        $service = Service::where(
            'tenant_id',
            app('currentTenant')->id
        )->findOrFail($id);

        if (! $service) {
            return response()->json([
                'message' => 'Service not found',
            ], 404);
        }

        $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('services')
                    ->where(
                        'tenant_id',
                        app('currentTenant')->id
                    )
                    ->ignore($id),
            ],
            'category' => 'sometimes|in:hair,skin,nail,bridal,massage,other',
            'duration_minutes' => 'sometimes|integer|min:15',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $service->update($request->only([
            'name', 'description', 'category',
            'duration_minutes', 'price', 'is_active',
        ]));

        return response()->json([
            'message' => 'Service updated successfully',
            'data' => $service,
        ]);
    }

    // Service delete karo
    public function destroy($id)
    {
        $service = Service::where(
            'tenant_id',
            app('currentTenant')->id
        )->findOrFail($id);

        if (! $service) {
            return response()->json([
                'message' => 'Service not found',
            ], 404);
        }

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully',
        ]);
    }
}
