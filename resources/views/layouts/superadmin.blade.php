<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Dashboard') · LUMIÈRE Super Admin</title>
        <link rel="icon" type="image/png" href="{{ asset('lumiere-favicon.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    <link rel="stylesheet" href="{{ asset('frontend/css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/css/superadmin.css') }}" />

    {{-- Global Chart.js configuration --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @stack('styles')

    <style>
        /* Premium Notification Animations & Scroll */
        @keyframes pulseGlow {
            0% {
                box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.4);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(244, 63, 94, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(244, 63, 94, 0);
            }
        }

        .notif-badge-active {
            animation: pulseGlow 2s infinite;
        }

        .lux-notif-scroller::-webkit-scrollbar {
            width: 4px;
        }

        .lux-notif-scroller::-webkit-scrollbar-thumb {
            background: rgba(201, 169, 110, 0.3);
            border-radius: 10px;
        }

        .lux-notif-scroller::-webkit-scrollbar-thumb:hover {
            background: var(--gold);
        }

        .lux-notif-item:hover {
            background: rgba(255, 255, 255, 0.03) !important;
        }

    </style>

    @stack('head-scripts')
</head>
<body>

    <div class="orb orb-gold" aria-hidden="true"></div>
    <div class="orb orb-teal" aria-hidden="true"></div>

    @include('partials.superadmin-sidebar')

    <div class="sa-wrap">

        {{-- Topbar --}}
        <header class="sa-topbar" role="banner">
            <button class="sa-sidebar-toggle" id="saToggle" aria-label="Toggle sidebar" aria-controls="saSidebar">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>

            <div style="flex:1;">
                @hasSection('breadcrumb')
                <nav aria-label="Breadcrumb" style="font-size:0.7rem;color:var(--text-3);margin-bottom:0.2rem;">
                    @yield('breadcrumb')
                </nav>
                @endif
                <h1 class="sa-topbar-title">@yield('page-title', 'Dashboard')</h1>
                @hasSection('page-sub')
                <p class="sa-topbar-sub">@yield('page-sub')</p>
                @endif
            </div>

            <form class="sa-topbar-search" action="{{ route('superadmin.tenants.index') }}" method="GET" role="search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search tenants, metrics…" aria-label="Search platform records" />
            </form>

            <div class="sa-topbar-actions" role="toolbar" aria-label="Dashboard actions">

                {{-- REFINED PREMIUM NOTIFICATION BELL --}}
                <div style="position:relative;" id="notif-wrapper">

                    {{-- Bell Button --}}
                    <button type="button" id="notif-btn" aria-label="View system notifications" onclick="toggleNotifications()" style="position: relative; width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); color: var(--text-2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold)'; this.style.background='rgba(201,169,110,0.05)';" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.color='var(--text-2)'; this.style.background='rgba(255,255,255,0.03)';">

                        <i class="bi bi-bell" style="font-size: 1.15rem;"></i>

                        {{-- Notification Badge (Red Cutout Style) --}}
                        <span id="notif-count" class="notif-badge-active" style="display:none; position:absolute; top:-4px; right:-4px; background: var(--rose, #f43f5e); color: white; font-size: 0.6rem; font-family: var(--ff-display); font-weight: 700; width: 20px; height: 20px; border-radius: 50%; border: 2px solid var(--bg-body, #09090b); align-items: center; justify-content: center; line-height: 1;"></span>
                    </button>

                    {{-- Glassmorphism Dropdown --}}
                    <div id="notif-dropdown" style="display:none; position:absolute; right:0; top:calc(100% + 12px); width:360px; background: rgba(15, 15, 20, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(201, 169, 110, 0.2); border-radius: 16px; box-shadow: 0 16px 40px rgba(0,0,0,0.5); z-index:9999; overflow:hidden; transform-origin: top right; animation: fadeUp 0.2s ease forwards;">

                        {{-- Header --}}
                        <div style="display:flex; justify-content:space-between; align-items:center; padding: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.05); background: linear-gradient(180deg, rgba(201, 169, 110, 0.05) 0%, transparent 100%);">
                            <span class="serif" style="font-size:1.1rem; color:var(--text); font-weight: 600;">Notifications</span>
                            <button onclick="markAllRead()" class="btn-lux-ghost" style="font-size:0.7rem; padding: 0.3rem 0.6rem; height: auto;">Mark all read</button>
                        </div>

                        {{-- Notification List --}}
                        <div id="notif-list" class="lux-notif-scroller" style="max-height:350px; overflow-y:auto;">
                            <div style="padding:3rem 2rem; text-align:center; color:var(--text-3); font-size:0.85rem;">
                                <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"></div>
                                <div>Loading updates...</div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div style="padding:0.8rem 1rem; border-top:1px solid rgba(255,255,255,0.05); text-align:center; background: rgba(0,0,0,0.2);">
                            <a href="{{ route('superadmin.tenants.index') }}" style="font-size:0.75rem; color:var(--gold); text-decoration:none; font-weight: 500; letter-spacing:0.05em; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='var(--gold)'">View All Platform Activity <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>

                @yield('topbar-actions')
            </div>
        </header>

        {{-- Main Page Content --}}
        <main class="sa-content" id="main-content" tabindex="-1">
            @include('partials.flash-messages')
            @yield('content')
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('frontend/js/app.js') }}"></script>
    <script src="{{ asset('frontend/js/superadmin.js') }}"></script>

    {{-- Script Operations --}}
    <script>
        const saToggle = document.getElementById('saToggle');
        const saSidebar = document.getElementById('saSidebar');
        if (saToggle && saSidebar) {
            saToggle.addEventListener('click', () => saSidebar.classList.toggle('open'));
        }

        let notifLoaded = false;

        function toggleNotifications() {
            const dropdown = document.getElementById('notif-dropdown');
            const isOpen = dropdown.style.display === 'block';

            if (isOpen) {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
                if (!notifLoaded) loadNotifications();
            }
        }

        function loadNotifications() {
            fetch('/superadmin/notifications')
                .then(res => res.json())
                .then(data => {
                    notifLoaded = true;
                    updateNotifCount(data.unread_count);
                    renderNotifications(data.notifications);
                })
                .catch(() => {
                    document.getElementById('notif-list').innerHTML =
                        '<div style="padding:2rem; text-align:center; color:var(--rose); font-size:0.8rem;"><i class="bi bi-exclamation-triangle mb-2" style="font-size: 1.5rem; display:block;"></i> Failed to load timeline.</div>';
                });
        }

        function renderNotifications(notifications) {
            const list = document.getElementById('notif-list');

            if (!notifications.length) {
                list.innerHTML = '<div style="padding:4rem 2rem; text-align:center; color:var(--text-3); font-size:0.85rem;"><i class="bi bi-bell-slash" style="font-size:2rem; display:block; margin-bottom:1rem; opacity:0.3;"></i>You are all caught up!</div>';
                return;
            }

            list.innerHTML = notifications.map(n => `
                <div class="lux-notif-item" style="display:flex; align-items:flex-start; gap:1rem; padding:1.2rem; border-bottom:1px solid rgba(255,255,255,0.03); background:${n.is_read ? 'transparent' : 'rgba(201,169,110,0.05)'}; cursor: pointer; transition: background 0.3s ease;">
                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.05); color:${n.color}; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; border: 1px solid rgba(255,255,255,0.05);">
                        <i class="bi ${n.icon}"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <p style="font-size:0.8rem; color:var(--text); margin:0; line-height:1.4; ${n.is_read ? 'opacity: 0.8;' : 'font-weight:600;'}">${n.title}</p>
                        <p style="font-size:0.7rem; color:var(--text-3); margin-top:0.3rem;"><i class="bi bi-clock me-1"></i>${n.time}</p>
                    </div>
                    ${!n.is_read ? '<div style="width:8px; height:8px; border-radius:50%; background:var(--gold); flex-shrink:0; margin-top:6px; box-shadow: 0 0 8px var(--gold);"></div>' : ''}
                </div>
            `).join('');
        }

        function updateNotifCount(count) {
            const badge = document.getElementById('notif-count');

            if (count > 0) {
                badge.style.display = 'flex';
                badge.textContent = count > 99 ? '99+' : count;
            } else {
                badge.style.display = 'none';
            }
        }

        function markAllRead() {
            fetch('/superadmin/notifications/mark-read', {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        , 'Content-Type': 'application/json'
                    , }
                })
                .then(res => res.json())
                .then(() => {
                    updateNotifCount(0);
                    notifLoaded = false;
                    loadNotifications();
                });
        }

        // Close dropdown when clicked outside
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('notif-wrapper');
            const dropdown = document.getElementById('notif-dropdown');
            if (wrapper && !wrapper.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Load unread count on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/superadmin/notifications')
                .then(res => res.json())
                .then(data => updateNotifCount(data.unread_count));
        });

        // Refresh count every 60 seconds
        setInterval(function() {
            fetch('/superadmin/notifications')
                .then(res => res.json())
                .then(data => updateNotifCount(data.unread_count));
        }, 60000);

    </script>

    @stack('scripts')

    <div id="global-overlay-container" style="position: relative; z-index: 99999;">
        <x-confirm-modal />
        <x-page-loader />
    </div>
</body>
</html>
