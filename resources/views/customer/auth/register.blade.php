@extends('layouts.auth')

@section('title', 'Register · '.$tenant->name)

@section('content')
<section class="auth-card fade-up s1" aria-labelledby="reg-heading">

    <div style="text-align:center; margin-bottom:1.4rem;">
        <span class="lux-badge lb-gold">
            <i class="bi bi-scissors" aria-hidden="true"></i>
            {{ $tenant->name }}
        </span>
    </div>

    <h2 class="auth-title" id="reg-heading">Create New Account</h2>
    <p class="auth-subtitle">Register to book appointments and track your history.</p>

    @include('partials.flash-messages')

    <form method="POST" action="{{ route('customer.register.post', $subdomain) }}" novalidate>
        @csrf

        {{-- Name --}}
        <div class="auth-field has-icon">
            <i class="bi bi-person-fill auth-field-icon" aria-hidden="true"></i>
            <input type="text" name="name" id="name" placeholder=" " value="{{ old('name') }}" style="padding-left: 2.8rem;" required autofocus autocomplete="name" />
            <label for="name">Full Name</label>
        </div>

        {{-- Email --}}
        <div class="auth-field has-icon">
            <i class="bi bi-envelope-fill auth-field-icon" aria-hidden="true"></i>
            <input type="email" name="email" id="email" placeholder=" " value="{{ old('email') }}" style="padding-left: 2.8rem;" required autocomplete="email" />
            <label for="email">Email Address</label>
        </div>

        {{-- Phone --}}
        <div class="auth-field has-icon">
            <i class="bi bi-telephone-fill auth-field-icon" aria-hidden="true"></i>
            <input type="tel" name="phone" id="phone" placeholder=" " value="{{ old('phone') }}" style="padding-left: 2.8rem;" required autocomplete="tel" />
            <label for="phone">Phone Number</label>
        </div>

        {{-- Password --}}
        <div class="auth-field has-icon">
            <i class="bi bi-lock-fill auth-field-icon" aria-hidden="true"></i>
            <input type="password" name="password" id="password" placeholder=" " style="padding-left: 2.8rem; padding-right: 2.8rem;" required autocomplete="new-password" />
            <label for="password">Password</label>
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

        <button type="submit" class="btn-lux-gold" style="width: 100%;">
            <i class="bi bi-person-plus-fill" aria-hidden="true"></i> Register
        </button>
    </form>

    <p class="auth-footer-link">
        Already have an account? <a href="{{ route('customer.login', $subdomain) }}">Login here</a>
    </p>

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
                icon ? .classList.toggle('bi-eye', !isPass);
                icon ? .classList.toggle('bi-eye-slash', isPass);
            });
        });
    });

</script>
@endpush
