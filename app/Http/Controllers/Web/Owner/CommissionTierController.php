<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\CommissionTier;
use App\Models\Staff;
use Illuminate\Http\Request;

class CommissionTierController extends Controller
{
    /**
     * Add a new revenue tier for a specific staff member.
     * Called from the staff management page via a small inline form.
     */
    public function store(Request $request, $staffId)
    {
        $tenant = app('currentTenant');

        $staff = Staff::where('tenant_id', $tenant->id)->findOrFail($staffId);

        $request->validate([
            'min_revenue' => 'required|numeric|min:0',
            'max_revenue' => 'nullable|numeric|gt:min_revenue',
            'commission_percent' => 'required|numeric|min:0|max:50',
        ]);

        // Prevent duplicate/overlapping tiers for this staff member
        // by checking if a tier already covers this min_revenue point.
        $overlap = CommissionTier::where('staff_id', $staff->id)
            ->where('min_revenue', '<=', $request->min_revenue)
            ->where(function ($q) use ($request) {
                $q->whereNull('max_revenue')
                    ->orWhere('max_revenue', '>', $request->min_revenue);
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors([
                'min_revenue' => 'A tier already covers this revenue range. Delete the overlapping tier first.',
            ])->withInput();
        }

        CommissionTier::create([
            'tenant_id' => $tenant->id,
            'staff_id' => $staff->id,
            'min_revenue' => $request->min_revenue,
            'max_revenue' => $request->max_revenue ?: null,
            'commission_percent' => $request->commission_percent,
        ]);

        return back()->with('success', "Tier added for {$staff->user?->name}.");
    }

    /**
     * Delete a specific tier — only if it belongs to this tenant.
     */
    public function destroy($tierId)
    {
        $tenant = app('currentTenant');

        $tier = CommissionTier::where('tenant_id', $tenant->id)->findOrFail($tierId);
        $tier->delete();

        return back()->with('success', 'Tier removed.');
    }
}
