@extends('layouts.owner')

@section('title', 'Platform Settings')
@section('page-title', 'System Settings')
@section('breadcrumb', 'More / Settings')

@section('content')

<div class="row g-4">

    <!-- Salon Information Panel -->
    <div class="col-12 col-xl-6 fade-up s1">
        <div class="card-lux p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                    <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom: 0;">Salon Information</h3>
                    <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem; margin-bottom: 0;">Update configuration details and customer contact profile.</p>
                </div>

                <form method="POST" action="{{ route('owner.settings.update') }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="form_type" value="info">

                    <div class="mb-3">
                        <x-forms.input name="business_name" label="Salon Identity Name *" :value="$tenant->name" :required="true" />
                    </div>
                    <div class="mb-3">
                        <x-forms.input name="email" label="Store Desk Email Address" :value="$tenant->email" type="email" />
                    </div>
                    <div class="mb-3">
                        <x-forms.input name="phone" label="Primary Support Contact Number" :value="$tenant->phone" />
                    </div>
                    <div class="mb-4">
                        <label class="lux-label" for="address">Physical Location Address</label>
                        <textarea name="address" id="address" rows="3" class="lux-input">{{ $tenant->address }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="lux-label" for="description">About / Salon Description</label>
                        <textarea name="description" id="description" rows="4" class="lux-input" placeholder="Provide a brief description of your salon...">{{ $tenant->description }}</textarea>
                        <small style="color:var(--text-3);font-size:0.72rem;">
                            This description will be displayed on the customer landing page.
                        </small>
                    </div>
                    <div class="mb-3">
                        <x-forms.input name="instagram_url" label="Instagram URL" :value="$tenant->instagram_url" placeholder="https://instagram.com/yoursalon" />
                    </div>
                    <div class="mb-4">
                        <x-forms.input name="facebook_url" label="Facebook URL" :value="$tenant->facebook_url" placeholder="https://facebook.com/yoursalon" />
                    </div>
                    <div style="padding-top: 0.5rem;">
                        <button type="submit" class="btn-lux-gold btn-sm" data-loading-text="Saving Info...">
                            <i class="bi bi-floppy" aria-hidden="true"></i> Save Information Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Landing Page Hero Image Panel -->
    <div class="col-12 col-xl-6 fade-up s1">
        <div class="card-lux p-4 h-100">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom: 0;">Landing Page Hero Image</h3>
                <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem; margin-bottom: 0;">The background photo shown on your public booking page's first screen.</p>
            </div>

            <div style="border-radius: var(--r-md); overflow: hidden; margin-bottom: 1rem; aspect-ratio: 16/9; background: rgba(255,255,255,0.03); border: 1px solid var(--border);">
                <img
                   src="{{ $tenant->hero_image ? cloudinary()->image($tenant->hero_image)->toUrl() : 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=1800&q=85&auto=format&fit=crop' }}"
                    alt="Current hero image"
                    style="width:100%; height:100%; object-fit:cover; display:block;"
                />
            </div>

            <form method="POST" action="{{ route('owner.settings.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <input type="hidden" name="form_type" value="hero_image">

                <div class="mb-3">
                    <label class="lux-label" for="hero_image">Upload New Photo</label>
                    <input type="file" name="hero_image" id="hero_image" accept="image/png,image/jpeg,image/webp" class="lux-input" required />
                    <small style="color:var(--text-3);font-size:0.72rem;">JPG, PNG or WEBP — max 3MB. Landscape photos work best.</small>
                </div>

                <div style="padding-top: 0.5rem;">
                    <button type="submit" class="btn-lux-gold btn-sm" data-loading-text="Uploading...">
                        <i class="bi bi-upload" aria-hidden="true"></i> Update Hero Image
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Operating Hours Panel -->
    <div class="col-12 col-xl-6 fade-up s2">
        <div class="card-lux p-4 h-100">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom: 0;">Operational Working Hours</h3>
                <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem; margin-bottom: 0;">Configure business hours for appointment scheduling.</p>
            </div>

            <form method="POST" action="{{ route('owner.settings.update') }}">
                @csrf @method('PUT')
                <input type="hidden" name="form_type" value="hours">
                @php
                $days = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];
                $hours = $tenant->settings['working_hours'] ?? [];
                @endphp

                <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                    @foreach($days as $key => $dayName)
                    @php $val = $hours[$key] ?? null; @endphp
                   <div class="wh-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: var(--r-md);">

                        <!-- Day & Toggle Switch -->
                       <div class="wh-day" style="display: flex; align-items: center; gap: 1rem; width: 140px;">
                            <label class="lux-switch">
                                <input type="checkbox" name="days[{{ $key }}][open]" value="1" class="day-toggle" data-day="{{ $key }}" {{ $val ? 'checked' : '' }} aria-label="{{ $dayName }} operational status" />
                                <span class="lux-slider"></span>
                            </label>
                            <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-2);">{{ $dayName }}</span>
                        </div>

                        <!-- Time Inputs Container -->
                      <div id="hours_{{ $key }}" class="wh-times" style="display: flex; align-items: center; gap: 0.5rem; transition: opacity 0.3s; opacity: {{ $val ? '1' : '0.3' }}; pointer-events: {{ $val ? 'auto' : 'none' }}; flex: 1;">
                            <input type="time" name="days[{{ $key }}][open_time]" value="{{ $val ? explode('-', $val)[0] : '09:00' }}" class="lux-input" style="color-scheme: dark; font-size: 0.75rem; padding: 0.4rem 0.5rem;" aria-label="{{ $dayName }} opening time" />
                            <span style="font-size: 0.7rem; color: var(--text-3);">to</span>
                            <input type="time" name="days[{{ $key }}][close_time]" value="{{ $val ? explode('-', $val)[1] ?? '18:00' : '18:00' }}" class="lux-input" style="color-scheme: dark; font-size: 0.75rem; padding: 0.4rem 0.5rem;" aria-label="{{ $dayName }} closing time" />
                        </div>

                    </div>
                    @endforeach
                </div>

                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <button type="submit" class="btn-lux-gold btn-sm" data-loading-text="Saving Hours...">
                        <i class="bi bi-clock" aria-hidden="true"></i> Save Operating Time Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security & Password Panel -->
    <div class="col-12 col-xl-6 fade-up s3">
        <div class="card-lux p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                    <h3 class="serif" style="font-size: 1.2rem; color: var(--text); margin-bottom: 0;">Change Security Password</h3>
                    <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem; margin-bottom: 0;">Refresh security credentials to safeguard your account.</p>
                </div>

                <form method="POST" action="{{ route('owner.settings.password') }}">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <x-forms.input name="current_password" label="Current Password *" type="password" :required="true" />
                    </div>
                    <div class="mb-3">
                        <x-forms.input name="password" label="New Secure Password *" type="password" :required="true" />
                    </div>
                    <div class="mb-4">
                        <x-forms.input name="password_confirmation" label="Confirm New Password *" type="password" :required="true" />
                    </div>

                    <div style="padding-top: 0.5rem;">
                        <button type="submit" class="btn-lux-ghost btn-sm border-0" style="background: rgba(255,255,255,0.05);">
                            <i class="bi bi-shield-lock" aria-hidden="true"></i> Update Security Credentials
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    .lux-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        flex-shrink: 0;
        margin-bottom: 0;
    }

    .lux-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        margin: 0;
    }

    .lux-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--bg-input);
        border: 1px solid var(--border-2);
        border-radius: 34px;
        transition: .4s;
    }

    .lux-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: var(--text-3);
        border-radius: 50%;
        transition: .4s;
    }

    input:checked+.lux-slider {
        background-color: var(--emerald-dim);
        border-color: rgba(16, 185, 129, 0.3);
    }

    input:checked+.lux-slider:before {
        transform: translateX(20px);
        background-color: var(--emerald);
    }

</style>
@endpush

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.day-toggle').forEach(cb => {
            cb.addEventListener('change', function() {
                const panel = document.getElementById('hours_' + this.dataset.day);
                if (panel) {
                    if (this.checked) {
                        panel.style.opacity = '1';
                        panel.style.pointerEvents = 'auto';
                    } else {
                        panel.style.opacity = '0.3';
                        panel.style.pointerEvents = 'none';
                    }
                }
            });
        });
    });

</script>
@endpush