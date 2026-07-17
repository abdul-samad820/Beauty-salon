<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title', 'Analytics Dashboard') · LUMIÈRE</title>
       <link rel="icon" type="image/png" href="{{ asset('lumiere-favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    <link rel="stylesheet" href="{{ asset('frontend/css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/css/owner.css') }}" />

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

        .lux-notif-item:hover {
            background: rgba(255, 255, 255, 0.03) !important;
        }

        @keyframes luxSpin {
            to {
                transform: rotate(360deg);
            }
        }

    </style>

    @stack('styles')
    @stack('head-scripts')
</head>
<body>

    <div class="orb orb-gold" aria-hidden="true"></div>
    <div class="orb orb-teal" aria-hidden="true"></div>

    <div class="main-layout-container">

        @include('partials.owner-sidebar')

        <div class="main-wrap">

            @include('partials.owner-topbar')

            <main id="main-content" tabindex="-1">
                <div class="page-body">

                    @include('partials.flash-messages')

                    @yield('content')

                </div>
            </main>
        </div>
    </div>

    <div id="global-overlay-container" style="position: relative; z-index: 99999;">
        <x-confirm-modal />
        <x-page-loader />
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('frontend/js/app.js') }}"></script>
    <script src="{{ asset('frontend/js/owner.js') }}"></script>

    @stack('scripts')

    {{-- Global Form Submission Feedback & Notification System --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form Submit Spinner
            // ✅ YEH KARO
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    var btn = form.querySelector('button[type="submit"]');
                    if (!btn) return;

                    var originalText = btn.innerHTML;
                    btn.disabled = true;

                    // Icon-only action buttons — sirf circle spinner
                    if (btn.classList.contains('action-btn-pro') || btn.dataset.iconSpinner) {
                        btn.innerHTML = '<span style="width:14px;height:14px;border:2px solid rgba(255,255,255,0.15);border-top-color:var(--gold);border-radius:50%;display:inline-block;animation:luxSpin 0.6s linear infinite;"></span>';
                    }
                    // Normal buttons — spinner + text
                    else if (!btn.dataset.noSpinner) {
                        btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;">' +
                            '<span style="width:13px;height:13px;border:2px solid rgba(201,169,110,0.3);border-top-color:var(--gold);border-radius:50%;display:inline-block;animation:luxSpin 0.6s linear infinite;flex-shrink:0;"></span>' +
                            (btn.dataset.loadingText || 'Saving...') +
                            '</span>';
                    }

                    window.addEventListener('pageshow', function() {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }, {
                        once: true
                    });
                });
            });
        });

        // ==========================================
        // PREMIUM NOTIFICATION SYSTEM
        // ==========================================

        // Mobile search overlay toggle — same button icon swaps search <-> close
        function toggleMobileSearch() {
            const form = document.getElementById('topbarSearchForm');
            const input = document.getElementById('topbarSearchInput');
            const btn = document.getElementById('mobile-search-btn');
            const icon = btn ? btn.querySelector('i') : null;
            if (!form) return;

            const isOpen = form.classList.contains('mobile-active');

            if (!isOpen) {
                form.classList.add('mobile-active');
                if (icon) { icon.classList.remove('bi-search'); icon.classList.add('bi-x-lg'); }
                if (btn) btn.setAttribute('aria-label', 'Close search');
                setTimeout(() => input && input.focus(), 50);
            } else {
                form.classList.remove('mobile-active');
                if (icon) { icon.classList.remove('bi-x-lg'); icon.classList.add('bi-search'); }
                if (btn) btn.setAttribute('aria-label', 'Open search');
            }
        }

        function toggleNotifications() {
            const dropdown = document.getElementById('notif-dropdown');
            if (!dropdown) return;
            const isOpen = dropdown.style.display === 'block';

            if (isOpen) {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
                loadNotifications();
            }
        }

        function loadNotifications() {
            fetch('/owner/notifications')
                .then(res => res.json())
                .then(data => {
                    updateNotifCount(data.unread_count);
                    renderNotifications(data.notifications);
                })
                .catch(() => {
                    const list = document.getElementById('notif-list');
                    if (list) list.innerHTML = '<div style="padding:2rem; text-align:center; color:var(--rose); font-size:0.8rem;"><i class="bi bi-exclamation-triangle mb-2" style="font-size: 1.5rem; display:block;"></i> Failed to load timeline.</div>';
                });
        }

        function renderNotifications(notifications) {
            const list = document.getElementById('notif-list');
            if (!list) return;

            if (!notifications || !notifications.length) {
                list.innerHTML = '<div style="padding:4rem 2rem; text-align:center; color:var(--text-3); font-size:0.85rem;"><i class="bi bi-bell-slash" style="font-size:2rem; display:block; margin-bottom:1rem; opacity:0.3;"></i>You are all caught up!</div>';
                return;
            }

            list.innerHTML = notifications.map(n => `
            <div class="lux-notif-item" style="display:flex; align-items:flex-start; gap:1rem; padding:1.2rem; border-bottom:1px solid rgba(255,255,255,0.03); background:${n.is_read ? 'transparent' : 'rgba(201,169,110,0.05)'}; cursor: pointer; transition: background 0.3s ease;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.05); color:${n.color || 'var(--gold)'}; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; border: 1px solid rgba(255,255,255,0.05);">
                    <i class="bi ${n.icon || 'bi-calendar-check'}"></i>
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
            if (!badge) return;

            if (count > 0) {
                badge.style.display = 'flex';
                badge.textContent = count > 99 ? '99+' : count;
            } else {
                badge.style.display = 'none';
            }
        }

        function markAllRead() {
            fetch('/owner/notifications/mark-read', {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        , 'Content-Type': 'application/json'
                    , }
                })
                .then(res => res.json())
                .then(() => {
                    updateNotifCount(0);
                    loadNotifications();
                });
        }

        // Close dropdown when clicked outside
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('notif-wrapper');
            const dropdown = document.getElementById('notif-dropdown');
            if (wrapper && dropdown && !wrapper.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Advanced Live Polling
        (function() {
            let lastCount = null;

            function checkNewBookings() {
                fetch('/owner/notifications/new-bookings', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (lastCount !== null && data.count > lastCount) {
                            showNotificationToast(data.count - lastCount);
                        }
                        if (data.count !== undefined) {
                            updateNotifCount(data.count); // Sync with bell badge
                            lastCount = data.count;
                        }
                    })
                    .catch(() => {});
            }

            function showNotificationToast(count) {
                const toast = document.createElement('div');
                toast.style.cssText = 'position:fixed;top:1.5rem;right:1.5rem;background:var(--gold);color:#000;padding:.75rem 1.25rem;border-radius:8px;font-size:.85rem;font-weight:600;z-index:99999;box-shadow:0 4px 20px rgba(0,0,0,.3); animation: fadeUp 0.3s ease;';
                toast.innerHTML = `<i class="bi bi-bell-fill me-2"></i> ${count} new booking${count > 1 ? 's' : ''} arrived!`;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 5000);
            }

            checkNewBookings();
            setInterval(checkNewBookings, 30000);
        })();

        // Page load pe unread count fetch karo
        fetch('/owner/notifications')
            .then(res => res.json())
            .then(data => updateNotifCount(data.unread_count))
            .catch(() => {});

    </script>
</body>
</html>
