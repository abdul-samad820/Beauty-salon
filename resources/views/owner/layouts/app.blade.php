<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Dashboard') · LUMIÈRE</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Jost:wght@200;300;400;500;600&display=swap" rel="stylesheet" />

  {{-- Base design system CSS --}}
  <link rel="stylesheet" href="{{ asset('frontend/css/_base.css') }}" />

  @stack('styles')
</head>
<body>

{{-- Floating orbs --}}
<div class="orb orb-gold"></div>
<div class="orb orb-teal"></div>

{{-- ── SIDEBAR ── --}}
<aside class="lm-sidebar" id="sidebar">
  <div class="sidebar-logo-area">
    <div class="logo-gem">L</div>
    <div>
      <div class="logo-wordmark">LUMIÈRE<span>.</span></div>
      <div class="logo-sub">{{ auth()->user()->tenant?->name ?? 'Owner Panel' }}</div>
    </div>
  </div>

  <div class="sidebar-scroll">
    <div class="nav-grp-label">Overview</div>
    <a href="{{ route('owner.dashboard') }}"
       class="nav-item {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
      <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a href="{{ route('owner.analytics') }}"
       class="nav-item {{ request()->routeIs('owner.analytics') ? 'active' : '' }}">
      <i class="bi bi-graph-up-arrow"></i> Analytics
    </a>

    <div class="nav-grp-label">Bookings</div>
    <a href="{{ route('owner.appointments.today') }}"
       class="nav-item {{ request()->routeIs('owner.appointments.today') ? 'active' : '' }}">
      <i class="bi bi-calendar-check-fill"></i> Today's Bookings
      @php $todayCount = \App\Models\Appointment::where('tenant_id', auth()->user()->tenant_id)->whereDate('appointment_date', today())->whereNotIn('status',['cancelled'])->count(); @endphp
      @if($todayCount > 0)
        <span class="nav-badge nb-gold">{{ $todayCount }}</span>
      @endif
    </a>
    <a href="{{ route('owner.appointments.index') }}"
       class="nav-item {{ request()->routeIs('owner.appointments.index') ? 'active' : '' }}">
      <i class="bi bi-calendar2-week-fill"></i> All Appointments
    </a>

    <div class="nav-grp-label">Manage</div>
    <a href="{{ route('owner.services.index') }}"
       class="nav-item {{ request()->routeIs('owner.services*') ? 'active' : '' }}">
      <i class="bi bi-scissors"></i> Services
    </a>
    <a href="{{ route('owner.staff.index') }}"
       class="nav-item {{ request()->routeIs('owner.staff*') ? 'active' : '' }}">
      <i class="bi bi-people-fill"></i> Staff
    </a>
    <a href="{{ route('owner.inventory.index') }}"
       class="nav-item {{ request()->routeIs('owner.inventory*') ? 'active' : '' }}">
      <i class="bi bi-box-seam-fill"></i> Inventory
      @php $lowStock = \App\Models\Product::where('tenant_id', auth()->user()->tenant_id)->whereRaw('quantity <= low_stock_threshold')->count(); @endphp
      @if($lowStock > 0)
        <span class="nav-badge nb-red">{{ $lowStock }}</span>
      @endif
    </a>
    <a href="{{ route('owner.commissions.index') }}"
       class="nav-item {{ request()->routeIs('owner.commissions*') ? 'active' : '' }}">
      <i class="bi bi-cash-stack"></i> Commissions
    </a>

    <div class="nav-grp-label">More</div>
    <a href="#" class="nav-item">
      <i class="bi bi-people"></i> Customers
    </a>
    <a href="{{ route('owner.settings') }}"
       class="nav-item {{ request()->routeIs('owner.settings') ? 'active' : '' }}">
      <i class="bi bi-gear-fill"></i> Settings
    </a>
  </div>

  <div class="sidebar-footer">
    <div class="owner-pill" id="ownerPillToggle">
      <div class="owner-av">{{ auth()->user()->initials }}</div>
      <div style="flex:1;min-width:0;">
        <div class="owner-name">{{ auth()->user()->name }}</div>
        <div class="owner-role">Owner · {{ auth()->user()->tenant?->subdomain }}</div>
      </div>
      <i class="bi bi-three-dots-vertical" style="color:var(--text-3);font-size:0.85rem;"></i>
    </div>
    <div id="ownerDropdown" style="display:none;margin-top:0.4rem;background:var(--bg-card-2);border:1px solid var(--border-2);border-radius:8px;overflow:hidden;">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="width:100%;background:none;border:none;padding:0.6rem 1rem;text-align:left;font-family:var(--ff-body);font-size:0.78rem;color:var(--rose);cursor:pointer;display:flex;align-items:center;gap:0.6rem;">
          <i class="bi bi-box-arrow-left"></i> Logout
        </button>
      </form>
    </div>
  </div>
</aside>

{{-- ── MAIN WRAP ── --}}
<div class="main-wrap">

  {{-- ── TOPBAR ── --}}
  <div class="lm-topbar">
    <button class="tb-icon" id="sidebarToggle" style="border:none;">
      <i class="bi bi-list"></i>
    </button>

    <div style="flex:1;">
      @hasSection('breadcrumb')
        <div style="font-size:0.65rem;color:var(--text-3);margin-bottom:0.15rem;">
          @yield('breadcrumb')
        </div>
      @endif
      <div class="topbar-heading">@yield('page-title', 'Dashboard')</div>
      @hasSection('page-sub')
        <div class="topbar-sub">@yield('page-sub')</div>
      @endif
    </div>

    <div class="topbar-search">
      <i class="bi bi-search"></i>
      <input type="text" placeholder="Search…" />
    </div>

    <div class="topbar-actions">
      <div class="tb-icon" title="Notifications">
        <i class="bi bi-bell"></i>
        @if(isset($lowStock) && $lowStock > 0)
          <span class="tb-dot"></span>
        @endif
      </div>
      @yield('topbar-actions')
    </div>
  </div>

  {{-- ── PAGE BODY ── --}}
  <div class="page-body">

    {{-- Flash messages --}}
    @if(session('success'))
      <div style="background:var(--emerald-dim);border:1px solid rgba(16,185,129,0.25);border-radius:8px;padding:0.75rem 1.2rem;font-size:0.82rem;color:var(--emerald);margin-bottom:1.2rem;display:flex;align-items:center;gap:0.6rem;" class="fade-up">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div style="background:var(--rose-dim);border:1px solid rgba(244,63,94,0.25);border-radius:8px;padding:0.75rem 1.2rem;font-size:0.82rem;color:var(--rose);margin-bottom:1.2rem;display:flex;align-items:center;gap:0.6rem;" class="fade-up">
        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
      </div>
    @endif
    @if($errors->any())
      <div style="background:var(--rose-dim);border:1px solid rgba(244,63,94,0.25);border-radius:8px;padding:0.75rem 1.2rem;font-size:0.82rem;color:var(--rose);margin-bottom:1.2rem;display:flex;align-items:center;gap:0.6rem;" class="fade-up">
        <i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}
      </div>
    @endif

    @yield('content')
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Sidebar toggle
  document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
  });
  // Owner dropdown
  document.getElementById('ownerPillToggle')?.addEventListener('click', function () {
    const d = document.getElementById('ownerDropdown');
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('#ownerPillToggle') && !e.target.closest('#ownerDropdown')) {
      const d = document.getElementById('ownerDropdown');
      if (d) d.style.display = 'none';
    }
  });
  // Flash auto-hide
  setTimeout(() => {
    document.querySelectorAll('[style*="emerald-dim"],[style*="rose-dim"]').forEach(el => {
      el.style.transition = 'opacity 0.5s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    });
  }, 4500);
</script>
@stack('scripts')
</body>
</html>
