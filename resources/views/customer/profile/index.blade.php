@extends('layouts.customer')

@section('title', 'My Profile')

@push('styles')
<style>
    .prog-track {
        height: 4px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 2px;
        overflow: hidden;
    }

    .prog-fill {
        height: 100%;
        width: 0%;
        transition: width .3s, background .3s;
    }

</style>
@endpush

@section('content')

<div class="row g-4 justify-content-center">

    {{-- Profile Update Section --}}
    <div class="col-lg-6 fade-up s1">
        <div class="card-lux p-4">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <div>
                    <h3 class="serif" style="font-size:1.2rem; color:var(--gold); margin-bottom:0.2rem;">My Profile</h3>
                    <p style="font-size:0.75rem; color:var(--text-3);">Update your account information</p>
                </div>
                <div style="width:52px;height:52px;border-radius:50%;background:var(--gold-dim); display:flex; align-items:center; justify-content:center; font-family:var(--ff-display); font-size:1.3rem; color:var(--gold); border:1px solid var(--gold);">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            </div>

            <form method="POST" action="{{ route('customer.profile.update', $subdomain) }}">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="lux-label">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="lux-input @error('name') border-rose @enderror" required>
                    @error('name')<div style="font-size:.7rem; color:var(--rose); margin-top:.3rem;">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="lux-label">Email</label>
                    <input type="email" value="{{ $user->email }}" class="lux-input" disabled style="opacity:.5; cursor:not-allowed;">
                    <div style="font-size:.65rem; color:var(--text-3); margin-top:.3rem;">Email address cannot be changed</div>
                </div>

                <div class="mb-4">
                    <label class="lux-label">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="lux-input" placeholder="+91 XXXXX XXXXX">
                </div>

                <button type="submit" class="btn-lux-gold" style="width:100%;">
                    <i class="bi bi-check-lg"></i> Save Profile
                </button>
            </form>
        </div>
    </div>

    {{-- Password Change & Stats Section --}}
    <div class="col-lg-6 fade-up s2">
        <div class="card-lux p-4 mb-3">
            <div style="margin-bottom:1.5rem;">
                <h3 class="serif" style="font-size:1.2rem; color:var(--text); margin-bottom:0.2rem;">Change Password</h3>
                <p style="font-size:0.75rem; color:var(--text-3);">Use a strong, secure password</p>
            </div>

            <form method="POST" action="{{ route('customer.profile.password', $subdomain) }}">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="lux-label">Current Password *</label>
                    <input type="password" name="current_password" class="lux-input @error('current_password') border-rose @enderror" placeholder="Current password" required>
                    @error('current_password')<div style="font-size:.7rem; color:var(--rose); margin-top:.3rem;">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="lux-label">New Password *</label>
                    <input type="password" name="password" id="custNewPass" class="lux-input" placeholder="Min 8 characters" required>
                    <div style="margin-top:.5rem;">
                        <div class="prog-track">
                            <div class="prog-fill" id="custStrengthBar"></div>
                        </div>
                        <div id="custStrengthText" style="font-size:.65rem; margin-top:.3rem;"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="lux-label">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" class="lux-input" placeholder="Repeat new password" required>
                </div>

                <button type="submit" class="btn-lux-gold" style="width:100%;">
                    <i class="bi bi-shield-lock"></i> Update Password
                </button>
            </form>
        </div>

        {{-- Account Statistics --}}
        <div class="card-lux p-4">
            <h3 class="serif" style="font-size:1rem; color:var(--text); margin-bottom:1rem;">Account Statistics</h3>
            @php
            $cId = $user->id; $tId = $tenant->id;
            $totalAppts = \App\Models\Appointment::where('customer_id',$cId)->where('tenant_id',$tId)->count();
            $completedAppts = \App\Models\Appointment::where('customer_id',$cId)->where('tenant_id',$tId)->where('status','completed')->count();
            @endphp
            @foreach(['Member Since' => $user->created_at?->format('M Y'), 'Total Bookings' => $totalAppts, 'Completed' => $completedAppts, 'Parlour' => $tenant->name] as $k => $v)
            <div style="display:flex; justify-content:space-between; padding:0.5rem 0; border-bottom:1px solid var(--border); font-size:0.8rem;">
                <span style="color:var(--text-3);">{{ $k }}</span>
                <span style="color:var(--text);">{{ $v }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.getElementById('custNewPass').addEventListener('input', function() {
        const val = this.value
            , bar = document.getElementById('custStrengthBar')
            , text = document.getElementById('custStrengthText');
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        const levels = [{
                pct: '0%'
                , color: 'var(--rose)'
                , label: ''
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
                , color: 'var(--gold)'
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
