@extends('layouts.superadmin')

@section('title', 'Platform Settings')
@section('page-title', 'Platform Settings')
@section('page-sub', 'Global configuration for LUMIÈRE SaaS')

@section('content')

<div class="row g-4">

    {{-- Settings Form --}}
    <div class="col-lg-8 fade-up s1">
        <form method="POST" action="{{ route('superadmin.settings.update') }}">
            @csrf @method('PUT')

            {{-- Platform --}}
            <div class="card-lux p-4 mb-3">
                <h3 class="sec-title" style="margin-bottom:1.2rem;">
                    <i class="bi bi-globe" style="color:var(--gold);margin-right:.4rem;"></i> Platform Settings
                </h3>
                <div class="row g-2">
                    <div class="col-md-6 form-group">
                        <label class="lux-label">Platform Name *</label>
                        <input type="text" name="platform_name" value="{{ $settings['platform_name'] ?? 'LUMIÈRE' }}" class="lux-input @error('platform_name') border-rose @enderror" required>
                        @error('platform_name')<div style="font-size:.65rem;color:var(--rose);margin-top:.3rem;">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="lux-label">Platform Email *</label>
                        <input type="email" name="platform_email" value="{{ $settings['platform_email'] ?? '' }}" class="lux-input" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="lux-label">Default Commission % *</label>
                        <input type="number" name="default_commission_percent" value="{{ $settings['default_commission_percent'] ?? 20 }}" class="lux-input" min="0" max="100" step="0.5" required>
                        <div style="font-size:.62rem;color:var(--text-3);margin-top:.2rem;">
                            Default commission percentage for new staff members.
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="lux-label">Default Trial Days *</label>
                        <input type="number" name="default_trial_days" value="{{ $settings['default_trial_days'] ?? 14 }}" class="lux-input" min="0" max="365" required>
                        <div style="font-size:.62rem;color:var(--text-3);margin-top:.2rem;">
                            Number of days for the new tenant free trial period.
                        </div>
                    </div>
                </div>

                {{-- Toggles --}}
                <div style="display:flex;gap:2rem;margin-top:.5rem;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;">
                        <input type="checkbox" name="allow_new_registrations" value="1" {{ ($settings['allow_new_registrations'] ?? true) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:var(--gold);">
                        <div>
                            <div style="font-size:.78rem;color:var(--text);">Allow New Registrations</div>
                            <div style="font-size:.62rem;color:var(--text-3);">Enable self-service tenant registration.</div>
                        </div>
                    </label>
                    <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;">
                        <input type="checkbox" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:var(--rose);">
                        <div>
                            <div style="font-size:.78rem;color:var(--text);">Maintenance Mode</div>
                            <div style="font-size:.62rem;color:var(--rose);">Enabling this will take the platform offline.</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Mail Settings --}}
            <div class="card-lux p-4 mb-3">
                <h3 class="sec-title" style="margin-bottom:1.2rem;">
                    <i class="bi bi-envelope" style="color:var(--gold);margin-right:.4rem;"></i> Mail Settings
                </h3>
                <div style="font-size:.72rem;color:var(--text-3);margin-bottom:1rem;">
                    Configure SMTP credentials in your <code style="background:rgba(255,255,255,0.06);padding:.1rem .4rem;border-radius:3px;">.env</code> file
                    (<code style="background:rgba(255,255,255,0.06);padding:.1rem .4rem;border-radius:3px;">MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD</code>).
                    Configure the sender details below.
                </div>
                <div class="row g-2">
                    <div class="col-md-6 form-group">
                        <label class="lux-label">Mail From Address</label>
                        <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? '' }}" class="lux-input" placeholder="noreply@yourdomain.com">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="lux-label">Mail From Name</label>
                        <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? 'LUMIÈRE' }}" class="lux-input" placeholder="LUMIÈRE Beauty">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="lux-label">Low Stock Alert Email</label>
                        <input type="email" name="low_stock_alert_email" value="{{ $settings['low_stock_alert_email'] ?? '' }}" class="lux-input" placeholder="alerts@yourdomain.com">
                        <div style="font-size:.62rem;color:var(--text-3);margin-top:.2rem;">
                            Notification email address for low inventory alerts.
                        </div>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="submit" class="btn-lux-gold">
                    <i class="bi bi-check-lg"></i> Save Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Right Panel --}}
    <div class="col-lg-4 fade-up s2">

        {{-- System Info --}}
        <div class="card-lux p-4 mb-3">
            <h3 class="sec-title" style="margin-bottom:1rem;">
                <i class="bi bi-info-circle" style="color:var(--gold);margin-right:.4rem;"></i> System Info
            </h3>
            @php
            $info = [
            'Laravel Version' => app()->version(),
            'PHP Version' => phpversion(),
            'Environment' => config('app.env'),
            'Debug Mode' => config('app.debug') ? '⚠️ ON' : '✅ OFF',
            'Queue Driver' => config('queue.default'),
            'Cache Driver' => config('cache.default'),
            'DB Connection' => config('database.default'),
            'Timezone' => config('app.timezone'),
            'Last Settings Update' => isset($settings['updated_at'])
            ? \Carbon\Carbon::parse($settings['updated_at'])->format('d M Y H:i')
            : 'Never',
            ];
            @endphp
            @foreach($info as $key => $val)
            <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.75rem;">
                <span style="color:var(--text-3);">{{ $key }}</span>
                <span style="color:{{ str_contains($val,'⚠️') ? 'var(--rose)' : 'var(--text)' }};">{{ $val }}</span>
            </div>
            @endforeach
        </div>

        {{-- Cache Controls --}}
        <div class="card-lux p-4">
            <h3 class="sec-title" style="margin-bottom:.5rem;">
                <i class="bi bi-lightning-fill" style="color:var(--gold);margin-right:.4rem;"></i> Cache Controls
            </h3>
            <p style="font-size:.72rem;color:var(--text-3);margin-bottom:1rem;">
                Clear the cache after updating configuration files.
            </p>
            <form method="POST" action="{{ route('superadmin.settings.clear-cache') }}">
                @csrf
                <button type="submit" class="btn-lux-ghost" style="width:100%;">
                    <i class="bi bi-trash3"></i> Clear All Cache
                    <span style="font-size:.62rem;color:var(--text-3);display:block;">config + view + application</span>
                </button>
            </form>

            @if(config('app.debug'))
            <div class="flash-alert flash-warning" style="margin-top:1rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>APP_DEBUG is enabled.</strong> Please disable for production.
            </div>
            @endif
        </div>

    </div>
</div>

@endsection
