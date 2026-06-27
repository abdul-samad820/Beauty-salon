<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Login') · LUMIÈRE</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('frontend/css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/css/auth.css') }}" />

    @stack('styles')
</head>
<body class="auth-body">

    <main class="auth-wrap">
        {{-- Brand logo --}}
        <div class="auth-logo" aria-label="LUMIÈRE">
            <div class="auth-logo-mark" aria-hidden="true">L</div>
            <div class="auth-logo-text">LUMIÈRE<span>.</span></div>
            <p class="auth-logo-sub">Salon Management Platform</p>
        </div>

        @yield('content')
    </main>

    <script src="{{ asset('frontend/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
