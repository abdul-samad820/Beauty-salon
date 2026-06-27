@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<section class="auth-card fade-up s1" aria-labelledby="forgot-heading" style="max-width: 420px; margin: 0 auto; width: 100%;">

    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--gold-dim); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
            <i class="bi bi-key" style="font-size: 1.5rem; color: var(--gold);"></i>
        </div>
        <h2 class="auth-title" id="forgot-heading">Forgot Password?</h2>
        <p class="auth-subtitle"> Enter your registered email, we'll send you a reset link.</p>

    </div>

    @include('partials.flash-messages')

    {{-- Yahan apne route ka naam confirm kar lena, usually 'customer.password.email' hota hai --}}
    <form method="POST" action="{{ route('customer.password.email', $subdomain ?? request()->route('subdomain')) }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="auth-field has-icon">
            <i class="bi bi-envelope-fill auth-field-icon" aria-hidden="true"></i>
            <input type="email" name="email" id="email" placeholder=" " value="{{ old('email') }}" style="padding-left: 2.8rem;" required autofocus autocomplete="email" />
            <label for="email">Email Address</label>
        </div>

        <button type="submit" class="btn-lux-gold" style="width: 100%; margin-top: 1rem;">
            <i class="bi bi-send" aria-hidden="true"></i> Send Reset Link
        </button>
    </form>

    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border); text-align: center;">
        <p style="font-size: 0.75rem; color: var(--text-3);">
            <i class="bi bi-arrow-left"></i>
            <a href="{{ route('customer.login', $subdomain ?? request()->route('subdomain')) }}" style="color: var(--gold); text-decoration: none; font-weight: 600; margin-left: 0.3rem;">Back to Login</a>
        </p>
    </div>

</section>
@endsection
