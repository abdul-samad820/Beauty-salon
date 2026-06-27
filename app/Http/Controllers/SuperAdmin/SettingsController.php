<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = $this->loadSettings();

        return view('superadmin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'platform_name' => 'required|string|max:100',
            'platform_email' => 'required|email',
            'default_commission_percent' => 'required|numeric|min:0|max:100',
            'default_trial_days' => 'required|integer|min:0|max:365',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:100',
            'low_stock_alert_email' => 'nullable|email',
        ]);

        $settings = [
            'platform_name' => $request->platform_name,
            'platform_email' => $request->platform_email,
            'default_commission_percent' => $request->default_commission_percent,
            'default_trial_days' => $request->default_trial_days,
            'mail_from_address' => $request->mail_from_address,
            'mail_from_name' => $request->mail_from_name,
            'low_stock_alert_email' => $request->low_stock_alert_email,
            'maintenance_mode' => $request->boolean('maintenance_mode'),
            'allow_new_registrations' => $request->boolean('allow_new_registrations', true),
            'updated_at' => now()->toISOString(),
        ];

        foreach ($settings as $key => $value) {
            PlatformSetting::set($key, $value);
        }

        return back()->with('success', 'Platform settings saved successfully.');

    }

    /**
     * Clear Laravel caches — useful after config changes
     */
    public function clearCache()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        return back()->with('success', 'Cache cleared successfully — config, view, application.');

    }

    private function loadSettings(): array
    {
        $keys = [
            'platform_name', 'platform_email', 'default_commission_percent',
            'default_trial_days', 'mail_from_address', 'mail_from_name',
            'low_stock_alert_email', 'maintenance_mode', 'allow_new_registrations',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = PlatformSetting::get($key);
        }

        return $settings;
    }
}
