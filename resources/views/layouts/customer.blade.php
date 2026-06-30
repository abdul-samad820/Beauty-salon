<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Book Appointment') · {{ $tenant->name ?? 'LUMIÈRE' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
      <link rel="icon" type="image/png" href="{{ asset('lumiere-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/css/customer.css') }}" />

    @stack('styles')
</head>
<body>

    <div class="orb orb-gold" aria-hidden="true"></div>
    <div class="orb orb-teal" aria-hidden="true"></div>

    {{-- Customer Navbar --}}
    <nav class="cust-nav" aria-label="Customer Navigation">
        <div>
            <a href="{{ route('customer.home', $subdomain) }}" class="cust-nav-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="34" height="34" aria-hidden="true">

                    <!-- Main Vertical Stem of 'L' -->
                    <rect x="25" y="15" width="8" height="70" fill="#D4AF37" />

                    <!-- Graceful Leaf/Flowing Horizontal Curve of 'L' -->
                    <path d="M 33 77 C 55 77 75 70 85 45 C 80 75 55 85 25 85 Z" fill="#D4AF37" />

                    <!-- Subtle Rose Gold Flowing Accent Line (Hair/Wellness vibe) -->
                    <path d="M 42 67 C 60 67 75 55 80 35 C 75 60 55 72 42 72 Z" fill="#B76E79" />

                    <!-- Geometric Premium Sparkle -->
                    <path d="M 75 10 Q 75 20 85 20 Q 75 20 75 30 Q 75 20 65 20 Q 75 20 75 10 Z" fill="#D4AF37" />

                </svg>
                LUMIÈRE
            </a>
            <p class="cust-nav-parlour">{{ $tenant->name }}</p>
        </div>

        @auth('customer')
        <div class="nav-links" role="navigation" aria-label="Customer dashboard navigation">
            <a href="{{ route('customer.home', $subdomain) }}" class="nav-link-btn {{ request()->routeIs('customer.home') ? 'active' : '' }}">
                <i class="bi bi-scissors" aria-hidden="true"></i>
                <span>Services</span>
            </a>
            <a href="{{ route('customer.appointments', $subdomain) }}" class="nav-link-btn {{ request()->routeIs('customer.appointments') ? 'active' : '' }}">
                <i class="bi bi-calendar2-check" aria-hidden="true"></i>
                <span>My Bookings</span>
            </a>

            {{-- User Profile Chip --}}
            <a href="{{ route('customer.profile', $subdomain) }}" class="user-chip {{ request()->routeIs('customer.profile') ? 'active' : '' }}" aria-label="Profile of {{ auth('customer')->user()->name }}" style="text-decoration: none;">

                <div class="user-chip-av" aria-hidden="true">
                    {{ strtoupper(substr(auth('customer')->user()->name, 0, 2)) }}
                </div>

                <span class="user-chip-name">{{ explode(' ', auth('customer')->user()->name)[0] }}</span>

                <i class="bi bi-chevron-right" style="font-size: 0.7rem; margin-left: 0.5rem; opacity: 0.5;"></i>
            </a>

            <form method="POST" action="{{ route('customer.logout', $subdomain) }}" style="display:inline">
                @csrf
                <button type="submit" class="nav-link-btn logout" aria-label="Logout">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                </button>
            </form>
        </div>
        @else
        <div class="nav-links">
            @if(!request()->routeIs('customer.login'))
            <a href="{{ route('customer.login', $subdomain) }}" class="btn-lux-ghost btn-sm">Login</a>
            @endif
        </div>
        @endauth
    </nav>

    {{-- Main Content --}}
    <main class="cust-body" id="main-content" tabindex="-1">
        @include('partials.flash-messages')
        @yield('content')
    </main>

    <div id="global-overlay-container" style="position: relative; z-index: 99999;">
        <x-confirm-modal />
        <x-page-loader />
    </div>


    <script src="{{ asset('frontend/js/customer.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    var btn = form.querySelector('button[type="submit"]');
                    if (!btn || btn.dataset.noSpinner) return;

                    var originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width:14px;height:14px;border-width:2px;margin-right:6px;vertical-align:-2px;"></span>' +
                        (btn.dataset.loadingText || 'Saving...');

                    window.addEventListener('pageshow', function() {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }, {
                        once: true
                    });
                });
            });
        });

    </script>

    @stack('scripts')

</body>
</html>
