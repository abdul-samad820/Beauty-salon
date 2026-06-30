@php
$tenantId = auth()->user()->tenant_id;
$todayCount = \App\Models\Appointment::where('tenant_id', $tenantId)
->whereDate('appointment_date', today())
->whereNotIn('status', ['cancelled'])->count();
$lowStock = \App\Models\Product::where('tenant_id', $tenantId)
->whereRaw('quantity <= low_stock_threshold')->count();
    $pendingReviews = \App\Models\Review::where('tenant_id', $tenantId)
    ->where('status', 'pending')->count();
    @endphp

    <aside class="lm-sidebar" id="sidebar" aria-label="Owner Navigation">
        <div class="sidebar-logo-area">
           <div class="logo-gem" aria-hidden="true">
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
</div>
            <div>
                <div class="logo-wordmark">LUMIÈRE<span>.</span></div>
                <div class="logo-sub">{{ auth()->user()->tenant?->name ?? 'Management Panel' }}</div>
            </div>
        </div>

        {{-- Scrolling Navigation Area --}}
        <nav class="sidebar-scroll" aria-label="Main Navigation">
            <div class="nav-grp-label">Overview</div>
            <a href="{{ route('owner.dashboard') }}" class="nav-item {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2" aria-hidden="true"></i> Dashboard
            </a>
            <a href="{{ route('owner.analytics') }}" class="nav-item {{ request()->routeIs('owner.analytics') ? 'active' : '' }}">
                <i class="bi bi-graph-up" aria-hidden="true"></i> Analytics
            </a>

            <div class="nav-grp-label">Bookings</div>
            <a href="{{ route('owner.appointments.today') }}" class="nav-item {{ request()->routeIs('owner.appointments.today') ? 'active' : '' }}">
                <i class="bi bi-calendar-check" aria-hidden="true"></i> Today's Bookings
                @if($todayCount > 0)
                <span class="sidebar-badge">{{ $todayCount }}</span>
                @endif
            </a>
            <a href="{{ route('owner.appointments.index') }}" class="nav-item {{ request()->routeIs('owner.appointments.index') ? 'active' : '' }}">
                <i class="bi bi-calendar3" aria-hidden="true"></i> All Appointments
            </a>
            <a href="{{ route('owner.appointments.create') }}" class="nav-item {{ request()->routeIs('owner.appointments.create') ? 'active' : '' }}">
                <i class="bi bi-calendar-plus" aria-hidden="true"></i> New Booking
            </a>

            <div class="nav-grp-label">Manage</div>
            <a href="{{ route('owner.services.index') }}" class="nav-item {{ request()->routeIs('owner.services*') ? 'active' : '' }}">
                <i class="bi bi-scissors" aria-hidden="true"></i> Services
            </a>
            <a href="{{ route('owner.staff.index') }}" class="nav-item {{ request()->routeIs('owner.staff*') ? 'active' : '' }}">
                <i class="bi bi-people" aria-hidden="true"></i> Staff Matrix
            </a>
            <a href="{{ route('owner.customers.index') }}" class="nav-item {{ request()->routeIs('owner.customers*') ? 'active' : '' }}">
                <i class="bi bi-person-vcard" aria-hidden="true"></i> Customers
            </a>
            <a href="{{ route('owner.inventory.index') }}" class="nav-item {{ request()->routeIs('owner.inventory.index') || request()->routeIs('owner.inventory.stock*') ? 'active' : '' }}">
                <i class="bi bi-box-seam" aria-hidden="true"></i> Inventory
                @if($lowStock > 0)
                <span class="sidebar-badge" style="background:var(--rose-dim); color:var(--rose);">{{ $lowStock }}</span>
                @endif
            </a>
            <a href="{{ route('owner.inventory.service-mapping') }}" class="nav-item {{ request()->routeIs('owner.inventory.service-mapping*') ? 'active' : '' }}" style="padding-left: 3rem; font-size: 0.75rem;">
                <i class="bi bi-diagram-3" aria-hidden="true"></i> Service Mapping
            </a>
            <a href="{{ route('owner.commissions.index') }}" class="nav-item {{ request()->routeIs('owner.commissions*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack" aria-hidden="true"></i> Commissions
            </a>
            <a href="{{ route('owner.reviews.index') }}" class="nav-item {{ request()->routeIs('owner.reviews*') ? 'active' : '' }}">
                <i class="bi bi-star" aria-hidden="true"></i> Reviews
                @if($pendingReviews > 0)
                <span class="sidebar-badge" style="background:var(--gold-dim); color:var(--gold);">{{ $pendingReviews }}</span>
                @endif
            </a>
            <a href="{{ route('owner.gallery.index') }}" class="nav-item {{ request()->routeIs('owner.gallery*') ? 'active' : '' }}">
                <i class="bi bi-images" aria-hidden="true"></i> Gallery
            </a>

            <div class="nav-grp-label">More Options</div>
            <a href="{{ route('owner.settings') }}" class="nav-item {{ request()->routeIs('owner.settings') ? 'active' : '' }}">
                <i class="bi bi-gear" aria-hidden="true"></i> Settings
            </a>
        </nav>

        {{-- Static Footer --}}
        <div class="sidebar-footer">
            <button class="owner-pill" id="ownerPillToggle" style="width: 100%; background: transparent; border: none; text-align: left;">
                <div class="owner-av">{{ auth()->user()->initials ?? 'O' }}</div>
                <div style="flex:1; min-width:0;">
                    <div class="owner-name">{{ auth()->user()->name }}</div>
                    <div class="owner-role">Owner · {{ auth()->user()->tenant?->subdomain }}</div>
                </div>
                <i class="bi bi-three-dots-vertical faint"></i>
            </button>
            <div id="ownerDropdown" style="display: none; margin: 0.5rem; background: var(--bg-card); border-radius: 8px;">
                <a href="{{ route('owner.profile') }}" style="display: block; padding: 0.6rem 1rem; color: var(--text-2); font-size: 0.78rem; text-decoration: none;">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="width:100%; border:none; background:none; padding: 0.6rem 1rem; color: var(--rose); font-size: 0.78rem; text-align:left;">Logout</button>
                </form>
            </div>
        </div>
    </aside>

    <style>
        /* Sidebar Layout Fix */
        .lm-sidebar {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
        }

        /* Premium Scroller */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(201, 169, 110, 0.3);
            border-radius: 10px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: var(--gold);
        }

        /* Badge Helper */
        .sidebar-badge {
            margin-left: auto;
            background: var(--amber-dim);
            color: var(--amber);
            font-size: 0.6rem;
            font-weight: 600;
            padding: 0.15rem 0.45rem;
            border-radius: 20px;
        }

    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('ownerPillToggle');
            const drop = document.getElementById('ownerDropdown');
            if (btn) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    drop.style.display = drop.style.display === 'none' ? 'block' : 'none';
                });
            }
        });

    </script>