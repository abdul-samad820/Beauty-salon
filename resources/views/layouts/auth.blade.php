<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Login') · LUMIÈRE</title>
    <link rel="icon" type="image/png" href="{{ asset('lumiere-favicon.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('frontend/css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/css/auth.css') }}" />

    @stack('styles')
</head>
<body class="auth-body">

    <main class="auth-wrap">
        <!-- PREMIUM LUMIÈRE BRAND HEADER (FLAT VECTOR STYLE) -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 2.5rem;">

            <div style="display: inline-flex; align-items: center; gap: 1rem; font-family: 'Playfair Display', serif;">

                <!-- Minimalist Flat Vector SVG Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="52" height="52">

                    <!-- Main Vertical Stem of 'L' -->
                    <rect x="25" y="15" width="8" height="70" fill="#D4AF37" />

                    <!-- Graceful Leaf/Flowing Horizontal Curve of 'L' -->
                    <path d="M 33 77 C 55 77 75 70 85 45 C 80 75 55 85 25 85 Z" fill="#D4AF37" />

                    <!-- Subtle Rose Gold Flowing Accent Line (Hair/Wellness vibe) -->
                    <path d="M 42 67 C 60 67 75 55 80 35 C 75 60 55 72 42 72 Z" fill="#B76E79" />

                    <!-- Geometric Premium Sparkle -->
                    <path d="M 75 10 Q 75 20 85 20 Q 75 20 75 30 Q 75 20 65 20 Q 75 20 75 10 Z" fill="#D4AF37" />

                </svg>

                <!-- Elegant Typography -->
                <span style="font-size: 2rem; font-weight: 500; color: #FFFFFF; letter-spacing: 0.15em; line-height: 1;">
                    LUMIÈRE<span style="color: #D4AF37;">.</span>
                </span>
            </div>

            <!-- Clean Subtitle -->
            <div style="margin-top: 0.8rem; font-size: 0.65rem; color: #888888; letter-spacing: 0.35em; text-transform: uppercase; font-weight: 500; font-family: var(--ff-body, sans-serif);">
                Salon Management Platform
            </div>

        </div>

        @yield('content')
    </main>

    <script src="{{ asset('frontend/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
