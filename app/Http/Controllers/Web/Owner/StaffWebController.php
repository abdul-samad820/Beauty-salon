<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffWebController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');

        $staff = Staff::with('user')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->get();

        $stats = [
            'total' => $staff->count(),
            'available' => $staff->where('is_available', true)->count(),
            'avg_commission' => $staff->avg('commission_percent') ?? 0,
        ];

        return view('owner.staff.index', compact('staff', 'stats'));
    }

    public function store(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'specializations' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $tenant) {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => true,
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

        return back()->with('success', "Staff \"{$request->name}\" add ho gaya!");
    }

    public function update(Request $request, $id)
    {
        $tenant = app('currentTenant');
        $staff = Staff::with('user')->where('tenant_id', $tenant->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'commission_percent' => 'required|numeric|min:0|max:100',
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

        return back()->with('success', 'Staff update ho gaya!');
    }

    public function destroy($id)
    {
        $tenant = app('currentTenant');
        $staff = Staff::with('user')->where('tenant_id', $tenant->id)->findOrFail($id);

        DB::transaction(function () use ($staff) {
            if ($staff->user) {
                $staff->user->update(['is_active' => false]);
            }
            $staff->update(['is_available' => false]);
        });

        return back()->with('success', 'Staff member deactivate ho gaya.');
    }
}
