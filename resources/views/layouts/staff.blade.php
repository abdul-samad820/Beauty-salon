<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Staff Portal') · LUMIÈRE</title>
      <link rel="icon" type="image/png" href="{{ asset('lumiere-favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('frontend/css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/css/owner.css') }}" />

    <style>
        /* ── BULLETPROOF LAYOUT & DARK THEME FIX ── */
        :root {
            --sidebar-w: 260px;
        }

        body,
        html {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background: var(--bg-body, #09090b) !important;
            /* FIX: Force Dark Background */
            color: var(--text, #f4f4f5) !important;
            overflow: hidden;
        }

        .main-layout-container {
            display: flex;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: var(--bg-body, #09090b) !important;
            /* FIX: Force Dark Background */
        }

        /* Force Sidebar Size & Position */
        .sidebar {
            width: var(--sidebar-w) !important;
            min-width: var(--sidebar-w) !important;
            height: 100vh !important;
            background: var(--bg-2, #18181b) !important;
            border-right: 1px solid var(--border, rgba(255, 255, 255, 0.05)) !important;
            display: flex !important;
            flex-direction: column !important;
            flex-shrink: 0 !important;
            position: relative !important;
            transform: none !important;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(201, 169, 110, 0.3);
            border-radius: 10px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: var(--gold, #C9A84C);
        }

        /* Force Content Area Settings */
        .main-wrap {
            flex: 1 !important;
            width: calc(100vw - var(--sidebar-w)) !important;
            max-width: calc(100vw - var(--sidebar-w)) !important;
            height: 100vh !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            margin-left: 0 !important;
            padding: 0 !important;
            position: relative;
            display: block !important;
            background: var(--bg-body, #09090b) !important;
            /* FIX: Force Dark Background */
        }

        .page-body {
            padding: 2.5rem !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
        }

        /* ── Mobile Topbar (hamburger + branding, sticky, non-overlapping) ── */
        .staff-mobile-topbar {
            display: none;
            align-items: center;
            gap: 0.9rem;
            position: sticky;
            top: 0;
            z-index: 500;
            padding: 0.9rem 1.2rem;
            background: rgba(9, 9, 11, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border, rgba(255,255,255,0.08));
        }
        .staff-mobile-toggle {
            display: flex;
            width: 38px; height: 38px;
            flex-shrink: 0;
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border, rgba(255,255,255,0.08));
            color: var(--text, #f4f4f5);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.1rem;
        }
        .staff-topbar-title {
            font-family: var(--ff-display);
            font-size: 1.05rem;
            letter-spacing: 0.03em;
            color: var(--text, #f4f4f5);
        }
        .staff-topbar-title span {
            color: var(--gold, #C9A84C);
        }
        .staff-sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 999;
        }

        @media (max-width: 992px) {
            .sidebar {
                position: fixed !important;
                top: 0; left: 0;
                z-index: 1000;
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease;
            }
            .sidebar.open {
                transform: translateX(0) !important;
                box-shadow: 4px 0 40px rgba(0,0,0,0.7);
            }
            .main-wrap {
                width: 100vw !important;
                max-width: 100vw !important;
            }
            .page-body {
                padding: 1.2rem !important;
            }
            .staff-mobile-topbar {
                display: flex !important;
            }
            .staff-sidebar-backdrop.show {
                display: block !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="orb orb-gold" aria-hidden="true"></div>
    <div class="orb orb-teal" aria-hidden="true"></div>

    <div class="main-layout-container">
        {{-- Sidebar --}}
        <div class="staff-sidebar-backdrop" id="staffSidebarBackdrop"></div>
        <aside class="sidebar">
            <div class="sidebar-logo" style="padding: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <span class="logo-mark"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="34" height="34" aria-hidden="true">

                <!-- Main Vertical Stem of 'L' -->
                <rect x="25" y="15" width="8" height="70" fill="#D4AF37" />

                <!-- Graceful Leaf/Flowing Horizontal Curve of 'L' -->
                <path d="M 33 77 C 55 77 75 70 85 45 C 80 75 55 85 25 85 Z" fill="#D4AF37" />

                <!-- Subtle Rose Gold Flowing Accent Line (Hair/Wellness vibe) -->
                <path d="M 42 67 C 60 67 75 55 80 35 C 75 60 55 72 42 72 Z" fill="#B76E79" />

                <!-- Geometric Premium Sparkle -->
                <path d="M 75 10 Q 75 20 85 20 Q 75 20 75 30 Q 75 20 65 20 Q 75 20 75 10 Z" fill="#D4AF37" />

            </svg></span>
                <div>
                    <div class="logo-text" style="font-weight:600; color:var(--text);">LUMIÈRE<span>.</span></div>
                    <div class="logo-sub" style="font-size:0.7rem; color:var(--text-3); text-transform:uppercase;">Staff Portal</div>
                </div>
            </div>

            <nav class="sidebar-nav" aria-label="Staff Navigation">
                <a href="{{ route('staff.dashboard') }}" class="nav-item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2" aria-hidden="true"></i> <span>Dashboard</span>
                </a>
                <a href="{{ route('staff.appointments') }}" class="nav-item {{ request()->routeIs('staff.appointments') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check" aria-hidden="true"></i> <span>My Appointments</span>
                </a>
                <a href="{{ route('staff.commissions') }}" class="nav-item {{ request()->routeIs('staff.commissions') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack" aria-hidden="true"></i> <span>My Commissions</span>
                </a>
                <a href="{{ route('staff.profile') }}" class="nav-item {{ request()->routeIs('staff.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle" aria-hidden="true"></i> <span>My Profile</span>
                </a>
            </nav>

            {{-- Footer Area --}}
            <div style="padding: 1rem; border-top: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; padding: 0.5rem;">
                    <div style="width: 35px; height: 35px; background: rgba(201, 169, 110, 0.15); color: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-family: var(--ff-display); border: 1px solid rgba(201, 169, 110, 0.3);">
                        {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.8rem; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ Auth::user()->name ?? 'Staff' }}
                        </div>
                        <div style="font-size: 0.65rem; color: var(--text-3);">Staff Account</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-lux-ghost w-100" style="color: var(--rose); border-color: rgba(244, 63, 94, 0.2); justify-content: center; padding: 0.5rem; font-size: 0.8rem;">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Page Content --}}
        <div class="main-wrap">
            {{-- Mobile-only sticky topbar: hamburger + branding, never overlaps content --}}
            <div class="staff-mobile-topbar">
                <button type="button" class="staff-mobile-toggle" id="staffSidebarToggle" aria-label="Toggle sidebar">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                <span class="staff-topbar-title">LUMIÈRE <span>Staff Portal</span></span>
            </div>

            <main id="main-content" tabindex="-1">
                <div class="page-body">
                    @include('partials.flash-messages')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const staffToggleBtn = document.getElementById('staffSidebarToggle');
    const staffSidebar = document.querySelector('.sidebar');
    const staffBackdrop = document.getElementById('staffSidebarBackdrop');

    function toggleStaffSidebar() {
        staffSidebar.classList.toggle('open');
        staffBackdrop.classList.toggle('show');
    }

    staffToggleBtn?.addEventListener('click', toggleStaffSidebar);
    staffBackdrop?.addEventListener('click', toggleStaffSidebar);
</script>
    @stack('scripts')
</body>
</html>