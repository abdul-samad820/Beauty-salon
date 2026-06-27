<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Retrieve all staff members.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $currentTenant = app('currentTenant');

        $staff = Staff::with('user:id,name,email,phone,is_active')
            ->where('tenant_id', $currentTenant->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Staff fetched successfully',
            'data' => $staff,
        ]);
    }

    /**
     * Create a new staff member.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone'),
            ],
            'password' => 'required|min:8|confirmed',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string',
            'working_hours' => 'nullable|array',
        ]);

        $result = DB::transaction(function () use ($request) {

            $currentTenant = app('currentTenant');

            /**
             * Step 1: Create the User
             */
            $user = User::create([
                'tenant_id' => $currentTenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            /**
             * Step 2: Assign Role
             */
            $user->assignRole('staff');

            /**
             * Step 3: Create Staff Profile
             */
            $staff = Staff::create([
                'tenant_id' => $currentTenant->id,
                'user_id' => $user->id,
                'commission_percent' => $request->commission_percent,

                'specializations' => $request->specializations ?? [],

                'working_hours' => $request->working_hours ?? [
                    'mon' => '09:00-20:00',
                    'tue' => '09:00-20:00',
                    'wed' => '09:00-20:00',
                    'thu' => '09:00-20:00',
                    'fri' => '09:00-20:00',
                    'sat' => '09:00-20:00',
                    'sun' => null,
                ],

                'is_available' => true,
            ]);

            return $staff->load('user:id,name,email,phone,is_active');
        });

        return response()->json([
            'message' => 'Staff added successfully',
            'data' => $result,
        ], 201);
    }

    /**
     * Retrieve a specific staff member.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $currentTenant = app('currentTenant');

        $staff = Staff::with('user:id,name,email,phone,is_active')
            ->where('tenant_id', $currentTenant->id)
            ->find($id);

        if (! $staff) {
            return response()->json([
                'message' => 'Staff not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Staff fetched successfully',
            'data' => $staff,
        ]);
    }

    /**
     * Update staff member details.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $currentTenant = app('currentTenant');

        $staff = Staff::with('user')
            ->where('tenant_id', $currentTenant->id)
            ->find($id);

        if (! $staff) {
            return response()->json([
                'message' => 'Staff not found',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users', 'phone')
                    ->ignore($staff->user_id),
            ],
            'password' => 'sometimes|min:8|confirmed',
            'commission_percent' => 'sometimes|numeric|min:0|max:100',
            'specializations' => 'sometimes|array',
            'specializations.*' => 'string',
            'working_hours' => 'sometimes|array',
            'is_available' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request, $staff) {

            /**
             * Update User record
             */
            $userData = [];

            if ($request->has('name')) {
                $userData['name'] = $request->name;
            }

            if ($request->has('phone')) {
                $userData['phone'] = $request->phone;
            }

            if ($request->has('is_active')) {
                $userData['is_active'] = $request->is_active;
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if (! empty($userData)) {
                $staff->user->update($userData);
            }

            /**
             * Update Staff record
             */
            $staffData = [];

            if ($request->has('commission_percent')) {
                $staffData['commission_percent'] = $request->commission_percent;
            }

            if ($request->has('specializations')) {
                $staffData['specializations'] = $request->specializations;
            }

            if ($request->has('working_hours')) {
                $staffData['working_hours'] = $request->working_hours;
            }

            if ($request->has('is_available')) {
                $staffData['is_available'] = $request->is_available;
            }

            if (! empty($staffData)) {
                $staff->update($staffData);
            }
        });

        return response()->json([
            'message' => 'Staff updated successfully',
            'data' => $staff->fresh()->load('user:id,name,email,phone,is_active'),
        ]);
    }

    /**
     * Delete a staff member.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $currentTenant = app('currentTenant');

        $staff = Staff::with('user')
            ->where('tenant_id', $currentTenant->id)
            ->find($id);

        if (! $staff) {
            return response()->json([
                'message' => 'Staff not found',
            ], 404);
        }

        DB::transaction(function () use ($staff) {

            $staff->delete();

            if ($staff->user) {
                $staff->user->update([
                    'is_active' => false,
                ]);
            }
        });

        return response()->json([
            'message' => 'Staff removed successfully',
        ]);
    }
}
