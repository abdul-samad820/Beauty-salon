<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        $request->validate([
            'business_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $settings = $tenant->settings ?? [];

        if ($request->filled('working_start') && $request->filled('working_end')) {
            $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            foreach ($days as $day) {
                if ($request->boolean("open_{$day}")) {
                    $settings['working_hours'][$day] = $request->input("open_{$day}_start", '09:00').'-'.$request->input("open_{$day}_end", '20:00');
                } else {
                    $settings['working_hours'][$day] = null;
                }
            }
            $settings['working_hours']['sun'] = $request->boolean('open_sun')
                ? ($request->input('open_sun_start', '10:00').'-'.$request->input('open_sun_end', '18:00'))
                : null;
        }

        $tenant->update([
            'name' => $request->business_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'settings' => $settings,
        ]);

        return back()->with('success', 'Settings save ho gayi!');
    }
}
