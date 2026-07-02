@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<section class="auth-card fade-up s1" aria-labelledby="reset-heading" style="max-width: 420px; margin: 0 auto; width: 100%;">

    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--teal-dim); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
            <i class="bi bi-shield-lock" style="font-size: 1.5rem; color: var(--teal-light);"></i>
        </div>
        <h2 class="auth-title" id="reset-heading">Reset Password</h2>
        <p class="auth-subtitle">Create your new secure password.</p>
    </div>

    @include('partials.flash-messages')

    <form method="POST" action="{{ route('customer.password.update', $subdomain ?? request()->route('subdomain')) }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-field has-icon">
            <i class="bi bi-envelope-fill auth-field-icon" aria-hidden="true"></i>
            <input type="email" name="email" id="email" placeholder=" " value="{{ $email ?? old('email') }}" style="padding-left: 2.8rem; color: var(--text-3);" required readonly autocomplete="email" />
            <label for="email">Email Address</label>
        </div>

        {{-- New Password --}}
        <div class="auth-field has-icon">
            <i class="bi bi-lock-fill auth-field-icon" aria-hidden="true"></i>
            <input type="password" name="password" id="password" placeholder=" " style="padding-left: 2.8rem; padding-right: 2.8rem;" required autocomplete="new-password" />
            <label for="password">New Password</label>
            <button type="button" class="auth-eye-toggle" data-target="password" data-icon="eyeIcon1" aria-label="Toggle password">
                <i class="bi bi-eye" id="eyeIcon1" aria-hidden="true"></i>
            </button>
        </div>

        {{-- Confirm Password --}}
        <div class="auth-field has-icon">
            <i class="bi bi-lock-fill auth-field-icon" aria-hidden="true"></i>
            <input type="password" name="password_confirmation" id="password_confirmation" placeholder=" " style="padding-left: 2.8rem; padding-right: 2.8rem;" required autocomplete="new-password" />
            <label for="password_confirmation">Confirm Password</label>
            <button type="button" class="auth-eye-toggle" data-target="password_confirmation" data-icon="eyeIcon2" aria-label="Toggle confirm password">
                <i class="bi bi-eye" id="eyeIcon2" aria-hidden="true"></i>
            </button>
        </div>

        <button type="submit" class="btn-lux-gold" style="width: 100%; margin-top: 1rem;">
            <i class="bi bi-check2-circle" aria-hidden="true"></i> Update Password
        </button>
    </form>

</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.auth-eye-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const icon = document.getElementById(btn.dataset.icon);
                if (!input) return;
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                icon?.classList.toggle('bi-eye', !isPass);
                icon?.classList.toggle('bi-eye-slash', isPass);
            });
        });
    });

</script>
@endpush
