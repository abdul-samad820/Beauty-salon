@extends('layouts.auth')

@section('title', 'Customer Login · ' . $tenant->name)

@section('content')
<section class="auth-card fade-up s1" style="max-width: 400px; margin: 0 auto; width: 100%; background: var(--bg-card); padding: 2.5rem; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 10px 40px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.05); position: relative; overflow: hidden;">

    {{-- Subtle Gold Glow Background Effect --}}
    <div style="position: absolute; top: -50px; left: -50px; width: 150px; height: 150px; background: var(--gold); filter: blur(80px); opacity: 0.12; pointer-events: none;"></div>

    <div style="text-align: center; margin-bottom: 2rem; position: relative; z-index: 1;">
        {{-- Elegant Avatar Icon --}}
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; background: var(--gold-dim); color: var(--gold); margin-bottom: 1.2rem; border: 1px solid rgba(201,169,110,0.2); box-shadow: 0 0 15px rgba(201,169,110,0.1);">
            <i class="bi bi-person-fill" style="font-size: 1.4rem;"></i>
        </div>

        <h2 class="auth-title serif" style="margin: 0; font-size: 1.8rem; color: var(--text);">Welcome Back</h2>
        <p class="auth-subtitle" style="font-size: 0.8rem; color: var(--text-3); margin-top: 0.4rem; letter-spacing: 0.02em;">
            Sign in to {{ $tenant->name }}
        </p>
    </div>

    @include('partials.flash-messages')

    <form method="POST" action="{{ route('customer.login.post', $subdomain) }}" novalidate style="position: relative; z-index: 1;">
        @csrf

        {{-- Email --}}
        <div class="auth-field has-icon" style="margin-bottom: 1.25rem;">
            <i class="bi bi-envelope auth-field-icon"></i>
            <input type="email" name="email" id="email" placeholder=" " value="{{ old('email') }}" required autofocus autocomplete="email" />
            <label for="email">Email Address</label>
        </div>

        {{-- Password --}}
        <div class="auth-field has-icon" style="margin-bottom: 0.5rem;">
            <i class="bi bi-lock auth-field-icon"></i>
            <input type="password" name="password" id="password" placeholder=" " required autocomplete="current-password" />
            <label for="password">Password</label>
            <button type="button" class="auth-eye-toggle" data-target="password" data-icon="eyeIcon" aria-label="Toggle password visibility" style="background: transparent; border: none; color: var(--text-3); position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; transition: color 0.3s;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--text-3)'">
                <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
        </div>

        {{-- Remember Me --}}
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <input type="checkbox" name="remember" value="1" id="remember" {{ old('remember') ? 'checked' : '' }} />
            <label for="remember" style="font-size: 0.8rem; color: var(--text-3); cursor: pointer;">
                Remember me
            </label>
        </div>

        {{-- Forgot Password Link (Right Aligned) --}}
        <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
            <a href="{{ route('customer.password.request', $subdomain) }}" style="font-size: 0.75rem; color: var(--text-3); text-decoration: none; transition: color 0.3s ease;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='var(--text-3)'">
                Forgot password?
            </a>
        </div>

        {{-- Sleek Login Button --}}
        <button type="submit" class="btn-lux-gold w-100" style="padding: 0.8rem; justify-content: center; font-size: 0.9rem; font-weight: 600; letter-spacing: 0.05em; border-radius: 8px;">
            Login <i class="bi bi-arrow-right ms-2"></i>
        </button>
    </form>

    {{-- Register Section --}}
    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; position: relative; z-index: 1;">
        <p style="font-size: 0.8rem; color: var(--text-3); margin: 0;">
            New to {{ $tenant->name }}?
            <a href="{{ route('customer.register', $subdomain) }}" style="color: var(--gold); text-decoration: none; font-weight: 600; letter-spacing: 0.02em; transition: opacity 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                Create an account
            </a>
        </p>
    </div>

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
