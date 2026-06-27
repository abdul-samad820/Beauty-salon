@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<section class="auth-card" aria-labelledby="login-heading">

    <h2 class="auth-title" id="login-heading">Welcome back</h2>
    <p class="auth-subtitle">Please sign in to your account</p>

    @include('partials.flash-messages')

    <form method="POST" action="{{ route('login.post') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="auth-field has-icon">
            <i class="bi bi-envelope-fill auth-field-icon" aria-hidden="true"></i>
            <input type="email" name="email" id="email" placeholder=" " value="{{ old('email') }}" style="padding-left: 3rem;" required autofocus autocomplete="email" aria-required="true" />
            <label for="email">Email Address</label>
        </div>

        {{-- Password --}}
        <div class="auth-field has-icon">
            <i class="bi bi-lock-fill auth-field-icon" aria-hidden="true"></i>
            <input type="password" name="password" id="password" placeholder=" " style="padding-left: 3rem; padding-right: 3rem;" required autocomplete="current-password" aria-required="true" />
            <label for="password">Password</label>

            <button type="button" class="auth-eye-toggle" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); border: none; background: transparent; cursor: pointer; color: var(--text-3);" aria-label="Toggle password visibility" data-target="password" data-icon="eyeIcon">
                <i class="bi bi-eye" id="eyeIcon" aria-hidden="true"></i>
            </button>
        </div>

        {{-- Remember / Forgot --}}
        <div class="auth-remember" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-size: 0.8rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} />
                Remember me
            </label>
        </div>

        <button type="submit" class="btn-lux-gold" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
            Login
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

                icon.classList.toggle('bi-eye', !isPass);
                icon.classList.toggle('bi-eye-slash', isPass);
            });
        });
    });

</script>
@endpush
