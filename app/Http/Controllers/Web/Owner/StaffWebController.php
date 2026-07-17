<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffWebController extends Controller
{
    /**
     * Display the list of staff members.
     */
    public function index()
    {
        $tenant = app('currentTenant');

        $staff = Staff::with(['user', 'commissionTiers'])
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Staff::where('tenant_id', $tenant->id)->count(),
            'available' => Staff::where('tenant_id', $tenant->id)->where('is_available', true)->count(),
            'avg_commission' => Staff::where('tenant_id', $tenant->id)->avg('commission_percent') ?? 0,
        ];

        return view('owner.staff.index', compact('staff', 'stats'));
    }

    /**
     * Store a new staff member.
     */
    public function store(Request $request)
    {
        $tenant = app('currentTenant');
        $plan = Plan::where('slug', $tenant->plan)->first();
        $currentStaffCount = Staff::where('tenant_id', $tenant->id)->count();

        if (! $plan || $currentStaffCount >= ($plan->max_staff ?? 0)) {
            return back()->withErrors(['limit' => 'Staff limit reached for your current plan. Please upgrade to add more staff.']);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->where(fn ($q) => $q->where('tenant_id', $tenant->id))],
            'phone' => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
            'commission_percent' => 'required|numeric|min:0|max:50',
            'specializations' => 'nullable|string',
        ], [
            'email.unique' => 'This email is already registered in the system. If this person is an existing customer, please use a different email for their staff account.',
        ]);

        DB::transaction(function () use ($request, $tenant) {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('staff');

            $specs = $request->specializations
                ? array_map('trim', explode(',', $request->specializations))
                : [];

            Staff::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'commission_percent' => $request->commission_percent,
                'specializations' => $specs,
                'working_hours' => [
                    'mon' => '09:00-20:00', 'tue' => '09:00-20:00',
                    'wed' => '09:00-20:00', 'thu' => '09:00-20:00',
                    'fri' => '09:00-20:00', 'sat' => '09:00-20:00',
                    'sun' => null,
                ],
                'is_available' => true,
            ]);
        });

        return back()->with('success', "Staff member \"{$request->name}\" added successfully.");
    }

    /**
     * Update an existing staff member.
     */
    public function update(Request $request, $id)
    {
        $tenant = app('currentTenant');
        $staff = Staff::with('user')->where('tenant_id', $tenant->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'commission_percent' => 'required|numeric|min:0|max:50',
            'specializations' => 'nullable|string',
            'is_available' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request, $staff) {
            $staff->user->update([
                'name' => $request->name,
                'phone' => $request->phone,
            ]);

            $specs = $request->specializations
                ? array_map('trim', explode(',', $request->specializations))
                : [];

            $staff->update([
                'commission_percent' => $request->commission_percent,
                'specializations' => $specs,
                'is_available' => $request->boolean('is_available', $staff->is_available),
            ]);
        });

        return back()->with('success', 'Staff member updated successfully.');
    }

    /**
     * Deactivate a staff member.
     */
    public function destroy($id)
    {
        $tenant = app('currentTenant');
        $staff = Staff::with('user')->where('tenant_id', $tenant->id)->findOrFail($id);

        DB::transaction(function () use ($staff) {
            if ($staff->user) {
                $staff->user->update(['is_active' => false]);
                DB::table('sessions')
                    ->where('user_id', $staff->user->id)
                    ->delete();
            }
            $staff->update(['is_available' => false]);
        });

        return back()->with('success', 'Staff member deactivated successfully.');
    }
}
