<aside class="sa-sidebar" id="saSidebar" aria-label="Super Admin Navigation">

    <div class="sa-logo">
        <div class="sa-logo-mark" aria-hidden="true">L</div>
        <div>
            <div class="sa-logo-text">LUMIÈRE<span>.</span></div>
            <div class="sa-logo-badge">Super Admin</div>
        </div>
    </div>

    <nav class="sa-nav" aria-label="Admin Navigation">

        <div class="sa-nav-label">Platform</div>

        <a href="{{ route('superadmin.dashboard') }}" class="sa-nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i> Dashboard
        </a>

        <a href="{{ route('superadmin.tenants.index') }}" class="sa-nav-item {{ request()->routeIs('superadmin.tenants*') ? 'active' : '' }}">
            <i class="bi bi-buildings-fill" aria-hidden="true"></i>
            Tenants
            @if(isset($activeTenants) && $activeTenants > 0)
            <span class="sa-nav-badge green">{{ $activeTenants }}</span>
            @endif
        </a>

        <a href="{{ route('superadmin.analytics') }}" class="sa-nav-item {{ request()->routeIs('superadmin.analytics') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Analytics
        </a>

        <a href="{{ route('superadmin.revenue') }}" class="sa-nav-item {{ request()->routeIs('superadmin.revenue') ? 'active' : '' }}">
            <i class="bi bi-currency-rupee" aria-hidden="true"></i> Revenue
        </a>

        <div class="sa-nav-label">Operations</div>

        <a href="{{ route('superadmin.appointments') }}" class="sa-nav-item {{ request()->routeIs('superadmin.appointments') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check-fill" aria-hidden="true"></i> Appointments
        </a>

        <a href="{{ route('superadmin.subscriptions.index') }}" class="sa-nav-item {{ request()->routeIs('superadmin.subscriptions*') ? 'active' : '' }}">
            <i class="bi bi-layers-fill" aria-hidden="true"></i>
            Subscriptions
            {{-- Expiring soon badge --}}
            @php
            $expiringSoon = \App\Models\Subscription::where('status','active')
            ->where('expires_at','<=',now()->addDays(7))
                ->where('expires_at','>',now())
                ->count();
                @endphp
                @if($expiringSoon > 0)
                <span class="sa-nav-badge gold">{{ $expiringSoon }}</span>
                @endif
        </a>

        <a href="{{ route('superadmin.plans.index') }}" class="sa-nav-item {{ request()->routeIs('superadmin.plans*') ? 'active' : '' }}">
            <i class="bi bi-grid" aria-hidden="true"></i> Plans
        </a>

        <div class="sa-nav-label">System</div>

        <a href="{{ route('superadmin.queue.index') }}" class="sa-nav-item {{ request()->routeIs('superadmin.queue*') ? 'active' : '' }}">
            <i class="bi bi-activity" aria-hidden="true"></i>
            Queue Monitor
            @php
            $failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
            @endphp
            @if($failedCount > 0)
            <span class="sa-nav-badge" style="background:var(--rose-dim);color:var(--rose);">
                {{ $failedCount }}
            </span>
            @endif
        </a>

        <a href="{{ route('superadmin.settings') }}" class="sa-nav-item {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
            <i class="bi bi-gear-fill" aria-hidden="true"></i> Settings
        </a>

    </nav>

    <div class="sa-footer">
        {{-- FIX: Added background: transparent and border: none to remove the white block --}}
        <button class="sa-user-mini w-100" id="saUserToggle" aria-expanded="false" aria-controls="saUserDropdown" style="background: transparent; border: none; outline: none;">

            <div class="sa-user-av" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}</div>

            <div style="flex:1;min-width:0;text-align:left;">
                <div class="sa-user-name" style="color: var(--text);">{{ auth()->user()->name ?? 'Super Admin' }}</div>
                <div class="sa-user-role" style="color: var(--text-3);">Super Admin</div>
            </div>

            <i class="bi bi-three-dots-vertical faint" aria-hidden="true"></i>
        </button>

        <div class="sa-dropdown" id="saUserDropdown" role="menu">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sa-dropdown-btn" role="menuitem" style="background: transparent; border: none; width: 100%; text-align: left;">
                    <i class="bi bi-box-arrow-left" aria-hidden="true"></i> Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Dropdown Toggle Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userBtn = document.getElementById('saUserToggle');
            const dropdown = document.getElementById('saUserDropdown');

            if (userBtn && dropdown) {
                userBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('open');
                });

                document.addEventListener('click', function(e) {
                    if (!userBtn.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.remove('open');
                    }
                });
            }
        });

    </script>
</aside>
