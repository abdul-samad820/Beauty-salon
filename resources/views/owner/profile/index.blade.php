@extends('layouts.owner')

@section('title', 'My Profile')
@section('page-title', 'Profile Hub')
@section('breadcrumb', 'More / Profile')

@section('content')
<div class="row g-4 flex-xl-row-reverse">

    <div class="col-12 col-xl-6 d-flex flex-column gap-4">

        {{-- Password Management --}}
        <div class="card-lux p-4 fade-up s2">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom: 0;">Change Password</h3>
                <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem; margin-bottom: 0;">Ensure your password is at least 8 characters long and includes a mix of characters.</p>
            </div>

            <form method="POST" action="{{ route('owner.profile.password') }}">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="lux-label" for="curPass">Current Password *</label>
                    <div style="position: relative;">
                        <input type="password" name="current_password" id="curPass" class="lux-input @error('current_password') border-rose @enderror" placeholder="Enter current password" required>
                        <button type="button" onclick="handlePasswordRevealToggle('curPass', 'eyeIcon1')" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: var(--text-3); cursor: pointer; padding: 0;">
                            <i class="bi bi-eye" id="eyeIcon1"></i>
                        </button>
                    </div>
                    @error('current_password')
                    <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="lux-label" for="newPass">New Password *</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="newPass" class="lux-input @error('password') border-rose @enderror" placeholder="Minimum 8 characters" required>
                        <button type="button" onclick="handlePasswordRevealToggle('newPass', 'eyeIcon2')" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: var(--text-3); cursor: pointer; padding: 0;">
                            <i class="bi bi-eye" id="eyeIcon2"></i>
                        </button>
                    </div>

                    <div style="margin-top: 0.8rem;">
                        <div style="width: 100%; height: 6px; border-radius: 3px; background: rgba(255,255,255,0.05); overflow: hidden;">
                            <div id="strengthBar" style="height: 100%; width: 0%; background: var(--rose); transition: all 0.3s ease;"></div>
                        </div>
                        <div id="strengthText" style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; margin-top: 0.4rem; color: var(--text-3);"></div>
                    </div>
                    @error('password')
                    <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="lux-label" for="password_confirmation">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="lux-input" placeholder="Repeat new password" required>
                </div>

                <div style="padding-top: 0.5rem;">
                    <button type="submit" class="btn-lux-ghost btn-sm border-0" style="width: 100%; justify-content: center; background: rgba(255,255,255,0.05);">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i> Update Password
                    </button>
                </div>
            </form>
        </div>

        {{-- Account Details --}}
        <div class="card-lux p-4 fade-up s3">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.8rem; margin-bottom: 1rem;">
                <h3 class="serif" style="font-size: 1.1rem; color: var(--text); margin-bottom: 0;">Account Information</h3>
            </div>
            @php
            $info = [
            'Role' => ucfirst($user->getRoleNames()->first() ?? 'Staff'),
            'Registration Date' => $user->created_at?->format('d M Y'),
            'Last Updated' => $user->updated_at?->diffForHumans(),
            'Status' => $user->is_active ? 'Active' : 'Suspended',
            ];
            @endphp
            <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                @foreach($info as $keyTitle => $valueLog)
                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.02);">
                    <span style="font-size: 0.75rem; font-weight: 500; color: var(--text-3);">{{ $keyTitle }}</span>
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 0.4rem;">
                        @if($keyTitle === 'Status')
                        <span class="live-dot" style="background: {{ $user->is_active ? 'var(--emerald)' : 'var(--rose)' }};"></span>
                        @endif
                        {{ $valueLog }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- General Information --}}
    <div class="col-12 col-xl-6 fade-up s1">
        <div class="card-lux p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="min-width: 0;">
                        <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom: 0;">General Details</h3>
                        <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem; margin-bottom: 0;">Update your contact information.</p>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-2); display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 600; color: var(--text-2); flex-shrink: 0;" aria-hidden="true">
                        {{ $user->initials }}
                    </div>
                </div>

                <form method="POST" action="{{ route('owner.profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="lux-label" for="name">Full Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="lux-input @error('name') border-rose @enderror" required>
                        @error('name')<p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="lux-label" for="email">Email Address *</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="lux-input @error('email') border-rose @enderror" required>
                        @error('email')<p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="lux-label" for="phone">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="lux-input" placeholder="+91 XXXXX XXXXX">
                    </div>

                    {{-- Profile Photo --}}
                    <div class="col-12 d-flex align-items-center gap-4 mb-3">
                        <div style="width:80px;height:80px;border-radius:50%;overflow:hidden;border:2px solid var(--gold);flex-shrink:0;">
                            @if(auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" style="width:100%;height:100%;object-fit:cover;" alt="Profile Photo">
                            @else
                            <div style="width:100%;height:100%;background:var(--gold-dim);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:var(--gold);">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            @endif
                        </div>
                        <div>
                            <label class="lux-label">Profile Photo</label>
                            <input type="file" name="profile_photo" accept="image/*" class="lux-input" style="padding:.4rem;">
                            <p style="font-size:.7rem;color:var(--text-3);margin-top:.3rem;">JPG, PNG, WEBP — Max 2MB</p>
                        </div>
                    </div>

                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: var(--r-md); padding: 1rem; margin-bottom: 1.5rem;">
                        <div style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-3); margin-bottom: 0.3rem;">Associated Parlour</div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: var(--text);">{{ $user->tenant?->name }}</div>
                        <div style="font-size: 0.7rem; font-family: monospace; color: var(--gold); margin-top: 0.2rem;">{{ $user->tenant?->subdomain }}.lumiere.app</div>
                    </div>

                    <div style="padding-top: 0.5rem;">
                        <button type="submit" class="btn-lux-gold btn-sm" style="width: 100%; justify-content: center;">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function handlePasswordRevealToggle(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
            icon.style.color = 'var(--gold)';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
            icon.style.color = 'var(--text-3)';
        }
    }

    document.getElementById('newPass').addEventListener('input', function() {
        const val = this.value;
        const bar = document.getElementById('strengthBar');
        const text = document.getElementById('strengthText');
        let score = 0;

        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [{
                pct: '0%'
                , color: 'var(--text-3)'
                , label: 'No Password'
            }
            , {
                pct: '25%'
                , color: 'var(--rose)'
                , label: 'Weak'
            }
            , {
                pct: '50%'
                , color: 'var(--amber)'
                , label: 'Fair'
            }
            , {
                pct: '75%'
                , color: 'var(--teal)'
                , label: 'Good'
            }
            , {
                pct: '100%'
                , color: 'var(--emerald)'
                , label: 'Strong ✓'
            }
        , ];

        bar.style.width = levels[score].pct;
        bar.style.background = levels[score].color;
        text.textContent = levels[score].label;
        text.style.color = levels[score].color;
    });

</script>
@endpush
