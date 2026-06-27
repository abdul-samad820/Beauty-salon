<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsWebController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');

        return view('owner.settings.index', compact('tenant'));
    }

    public function update(Request $request)
    {
        $tenant = app('currentTenant');
        $type = $request->input('form_type');

        if ($type === 'info') {
            $request->validate([
                'business_name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'description' => 'nullable|string|max:1000',
                'instagram_url' => 'nullable|url|max:255',
                'facebook_url' => 'nullable|url|max:255',
            ]);

            $tenant->update([
                'name' => $request->business_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'description' => $request->description,
                'instagram_url' => $request->instagram_url,
                'facebook_url' => $request->facebook_url,
            ]);

            return back()->with('success', 'Success: Salon information updated successfully.');
        }

        if ($type === 'hours') {
            $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            $settings = $tenant->settings ?? [];

            foreach ($days as $day) {
                $dayData = $request->input("days.{$day}");
                $isOpen = ! empty($dayData['open']);

                if ($isOpen && ! empty($dayData['open_time']) && ! empty($dayData['close_time'])) {
                    $settings['working_hours'][$day] = $dayData['open_time'].'-'.$dayData['close_time'];
                } else {
                    $settings['working_hours'][$day] = null;
                }
            }

            $tenant->update(['settings' => $settings]);

            return back()->with('success', 'Success: Operating hours updated successfully.');
        }

        return back()->with('error', 'Invalid form submission.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Success: Password updated successfully.');
    }
}
