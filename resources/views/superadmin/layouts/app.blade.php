<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Dashboard') · LUMIÈRE Super Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@200;300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  @stack('head-scripts')

  <style>
    :root {
      --bg:          #0a0a0c;
      --bg-2:        #0f0f12;
      --bg-card:     #13131a;
      --bg-card-2:   #16161f;
      --bg-input:    rgba(255,255,255,0.04);
      --border:      rgba(255,255,255,0.06);
      --border-2:    rgba(255,255,255,0.1);
      --gold:        #c9a96e;
      --gold-dim:    rgba(201,169,110,0.15);
      --gold-glow:   rgba(201,169,110,0.3);
      --teal:        #2d7d6f;
      --teal-light:  #3a9e8d;
      --teal-dim:    rgba(45,125,111,0.15);
      --purple:      #7c5cbf;
      --purple-dim:  rgba(124,92,191,0.15);
      --emerald:     #10b981;
      --emerald-dim: rgba(16,185,129,0.12);
      --rose:        #f43f5e;
      --rose-dim:    rgba(244,63,94,0.12);
      --text:        rgba(255,255,255,0.88);
      --text-2:      rgba(255,255,255,0.50);
      --text-3:      rgba(255,255,255,0.28);
      --ff-display:  'Cormorant Garamond', serif;
      --ff-body:     'Jost', sans-serif;
      --sidebar-w:   240px;
      --transition:  0.4s cubic-bezier(0.22, 1, 0.36, 1);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body { font-family: var(--ff-body); background: var(--bg); color: var(--text); font-weight: 300; overflow-x: hidden; }

    /* ── SIDEBAR ── */
    .sidebar { position:fixed; top:0; left:0; width:var(--sidebar-w); height:100vh; background:var(--bg-2); border-right:1px solid var(--border); display:flex; flex-direction:column; z-index:100; transition:transform var(--transition); }
    .sidebar-logo { padding:1.8rem 1.5rem 1.4rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:0.7rem; }
    .logo-mark { width:34px; height:34px; background:linear-gradient(135deg,var(--gold),#e8c48a); border-radius:8px; display:flex; align-items:center; justify-content:center; font-family:var(--ff-display); font-size:1.1rem; font-weight:500; color:#1a1400; flex-shrink:0; }
    .logo-text { font-family:var(--ff-display); font-size:1.2rem; font-weight:400; color:var(--text); letter-spacing:0.08em; }
    .logo-text span { color:var(--gold); }
    .logo-badge { font-family:var(--ff-body); font-size:0.52rem; font-weight:600; letter-spacing:0.18em; text-transform:uppercase; color:var(--gold); background:var(--gold-dim); border:1px solid rgba(201,169,110,0.2); padding:0.15rem 0.5rem; border-radius:20px; margin-top:0.1rem; }
    .sidebar-nav { flex:1; overflow-y:auto; padding:1.2rem 0; }
    .sidebar-nav::-webkit-scrollbar { width:4px; }
    .sidebar-nav::-webkit-scrollbar-track { background:transparent; }
    .sidebar-nav::-webkit-scrollbar-thumb { background:var(--border-2); border-radius:2px; }
    .nav-section-label { font-size:0.58rem; font-weight:600; letter-spacing:0.28em; text-transform:uppercase; color:var(--text-3); padding:0.8rem 1.5rem 0.4rem; }
    .nav-link-item { display:flex; align-items:center; gap:0.75rem; padding:0.65rem 1.5rem; color:var(--text-2); text-decoration:none; font-size:0.82rem; font-weight:400; border-left:2px solid transparent; transition:color 0.25s, background 0.25s, border-color 0.25s; position:relative; }
    .nav-link-item i { font-size:0.95rem; width:18px; text-align:center; }
    .nav-link-item:hover { color:var(--text); background:rgba(255,255,255,0.03); }
    .nav-link-item.active { color:var(--gold); background:var(--gold-dim); border-left-color:var(--gold); }
    .nav-badge { margin-left:auto; background:var(--rose-dim); color:var(--rose); font-size:0.6rem; font-weight:600; padding:0.15rem 0.45rem; border-radius:20px; }
    .nav-badge.green { background:var(--emerald-dim); color:var(--emerald); }
    .nav-badge.gold  { background:var(--gold-dim);    color:var(--gold); }
    .sidebar-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); }
    .user-mini { display:flex; align-items:center; gap:0.75rem; padding:0.6rem; border-radius:8px; transition:background 0.25s; cursor:pointer; }
    .user-mini:hover { background:rgba(255,255,255,0.04); }
    .user-avatar { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,var(--gold),var(--teal)); display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:600; color:white; flex-shrink:0; }
    .user-info { flex:1; min-width:0; }
    .user-name { font-size:0.78rem; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .user-role { font-size:0.62rem; color:var(--text-3); letter-spacing:0.08em; }

    /* ── MAIN WRAP ── */
    .main-wrap { margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column; }

    /* ── TOPBAR ── */
    .topbar { position:sticky; top:0; z-index:90; background:rgba(10,10,12,0.85); backdrop-filter:blur(16px); border-bottom:1px solid var(--border); padding:0.85rem 2rem; display:flex; align-items:center; gap:1.5rem; }
    .topbar-title { font-family:var(--ff-display); font-size:1.25rem; font-weight:400; color:var(--text); letter-spacing:0.02em; }
    .topbar-sub { font-size:0.72rem; color:var(--text-3); letter-spacing:0.08em; }
    .topbar-search { flex:1; max-width:320px; margin-left:auto; position:relative; }
    .topbar-search input { width:100%; background:rgba(255,255,255,0.04); border:1px solid var(--border-2); border-radius:8px; color:var(--text); font-family:var(--ff-body); font-size:0.8rem; padding:0.55rem 1rem 0.55rem 2.4rem; outline:none; transition:border-color 0.3s; }
    .topbar-search input:focus { border-color:var(--gold); }
    .topbar-search input::placeholder { color:var(--text-3); }
    .topbar-search i { position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); color:var(--text-3); font-size:0.85rem; }
    .topbar-actions { display:flex; align-items:center; gap:0.75rem; }
    .icon-btn { width:36px; height:36px; background:rgba(255,255,255,0.04); border:1px solid var(--border); border-radius:8px; display:flex; align-items:center; justify-content:center; color:var(--text-2); cursor:pointer; position:relative; transition:background 0.25s, color 0.25s; }
    .icon-btn:hover { background:rgba(255,255,255,0.07); color:var(--text); }
    .notif-dot { position:absolute; top:7px; right:7px; width:6px; height:6px; border-radius:50%; background:var(--rose); }

    /* ── PAGE CONTENT ── */
    .page-content { padding:2rem; flex:1; }

    /* ── BUTTONS ── */
    .btn-gold { background:var(--gold); border:none; color:#1a1400; font-family:var(--ff-body); font-size:0.72rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; padding:0.5rem 1.2rem; border-radius:6px; cursor:pointer; text-decoration:none; transition:background 0.3s, box-shadow 0.3s; display:inline-flex; align-items:center; gap:0.5rem; }
    .btn-gold:hover { background:#dbb97e; box-shadow:0 4px 20px var(--gold-glow); color:#1a1400; }
    .btn-ghost { background:transparent; border:1px solid var(--border-2); color:var(--text-2); font-family:var(--ff-body); font-size:0.72rem; font-weight:400; letter-spacing:0.08em; padding:0.4rem 1rem; border-radius:6px; cursor:pointer; text-decoration:none; transition:background 0.25s, color 0.25s, border-color 0.25s; display:inline-flex; align-items:center; gap:0.4rem; }
    .btn-ghost:hover { background:rgba(255,255,255,0.05); color:var(--text); border-color:var(--gold); }
    .btn-danger { background:var(--rose-dim); border:1px solid rgba(244,63,94,0.3); color:var(--rose); font-family:var(--ff-body); font-size:0.72rem; font-weight:500; padding:0.5rem 1.2rem; border-radius:6px; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; transition:all 0.3s; }
    .btn-danger:hover { background:var(--rose); color:white; }

    /* ── CARDS ── */
    .card-glass { background:var(--bg-card); border:1px solid var(--border); border-radius:12px; position:relative; overflow:hidden; transition:border-color var(--transition), box-shadow var(--transition); }
    .card-glass:hover { border-color:var(--border-2); box-shadow:0 8px 40px rgba(0,0,0,0.4); }
    .card-glass::before { content:''; position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(255,255,255,0.08),transparent); }

    /* ── STATUS BADGES ── */
    .status-badge { display:inline-flex; align-items:center; gap:0.3rem; font-size:0.62rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; padding:0.25rem 0.65rem; border-radius:20px; }
    .badge-active    { background:var(--emerald-dim); color:var(--emerald); }
    .badge-inactive  { background:rgba(255,255,255,0.05); color:var(--text-3); }
    .badge-suspended { background:var(--rose-dim); color:var(--rose); }
    .badge-trial     { background:var(--gold-dim); color:var(--gold); }
    .plan-badge { display:inline-flex; align-items:center; gap:0.3rem; font-size:0.6rem; font-weight:600; letter-spacing:0.12em; text-transform:uppercase; padding:0.25rem 0.7rem; border-radius:20px; }
    .plan-free       { background:var(--teal-dim); color:var(--teal-light); border:1px solid rgba(58,158,141,0.15); }
    .plan-pro        { background:var(--purple-dim); color:#a78bfa; border:1px solid rgba(167,139,250,0.15); }
    .plan-enterprise { background:var(--gold-dim); color:var(--gold); border:1px solid rgba(201,169,110,0.2); }

    /* ── SECTION HEADER ── */
    .section-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; }
    .section-hdr-title { font-family:var(--ff-display); font-size:1.1rem; font-weight:400; color:var(--text); }
    .section-hdr-sub { font-size:0.72rem; color:var(--text-3); margin-top:0.1rem; }

    /* ── FLASH MESSAGES ── */
    .flash-alert { padding:0.75rem 1.2rem; border-radius:8px; font-size:0.82rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:1.2rem; }
    .flash-success { background:var(--emerald-dim); border:1px solid rgba(16,185,129,0.25); color:var(--emerald); }
    .flash-error   { background:var(--rose-dim);    border:1px solid rgba(244,63,94,0.25);  color:var(--rose); }

    /* ── SIDEBAR TOGGLE (mobile) ── */
    .sidebar-toggle { display:none; background:none; border:none; color:var(--text); font-size:1.2rem; cursor:pointer; }

    /* ── LIVE DOT ── */
    .live-dot { width:8px; height:8px; border-radius:50%; background:var(--emerald); position:relative; display:inline-block; }
    .live-dot::after { content:''; position:absolute; inset:-3px; border-radius:50%; background:var(--emerald); opacity:0.4; animation:pulse-ring 1.8s ease-out infinite; }
    @keyframes pulse-ring { 0%{transform:scale(1);opacity:0.8} 100%{transform:scale(1.8);opacity:0} }

    /* ── ANIMATIONS ── */
    .fade-in    { animation:fadeIn    0.6s ease both; }
    .fade-in-up { animation:fadeInUp  0.7s ease both; }
    @keyframes fadeIn   { from{opacity:0} to{opacity:1} }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }
    .stagger-1{animation-delay:0.05s} .stagger-2{animation-delay:0.1s} .stagger-3{animation-delay:0.15s}
    .stagger-4{animation-delay:0.2s}  .stagger-5{animation-delay:0.25s} .stagger-6{animation-delay:0.3s}

    @media(max-width:992px) {
      .sidebar { transform:translateX(-100%); }
      .sidebar.open { transform:none; }
      .main-wrap { margin-left:0; }
      .sidebar-toggle { display:flex; }
      .page-content { padding:1.2rem; }
    }
  </style>
  @stack('styles')
</head>
<body>

{{-- ── SIDEBAR ── --}}
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-mark">L</div>
    <div>
      <div class="logo-text">LUMIÈRE<span>.</span></div>
      <div class="logo-badge">Super Admin</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Platform</div>
    <a href="{{ route('superadmin.dashboard') }}" class="nav-link-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
      <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a href="{{ route('superadmin.tenants.index') }}" class="nav-link-item {{ request()->routeIs('superadmin.tenants*') ? 'active' : '' }}">
      <i class="bi bi-buildings-fill"></i> Tenants
      <span class="nav-badge green">{{ \App\Models\Tenant::where('status','active')->count() }}</span>
    </a>
    <a href="#" class="nav-link-item">
      <i class="bi bi-graph-up-arrow"></i> Analytics
    </a>
    <a href="#" class="nav-link-item">
      <i class="bi bi-currency-rupee"></i> Revenue
    </a>

    <div class="nav-section-label">Operations</div>
    <a href="#" class="nav-link-item">
      <i class="bi bi-calendar2-check-fill"></i> Appointments
    </a>
    <a href="#" class="nav-link-item">
      <i class="bi bi-layers-fill"></i> Subscriptions
    </a>
    <a href="#" class="nav-link-item">
      <i class="bi bi-bell-fill"></i> Reminders
    </a>
    <a href="#" class="nav-link-item">
      <i class="bi bi-box-seam-fill"></i> Inventory
    </a>

    <div class="nav-section-label">System</div>
    <a href="#" class="nav-link-item">
      <i class="bi bi-activity"></i> Queue Monitor
    </a>
    <a href="#" class="nav-link-item">
      <i class="bi bi-clock-history"></i> Scheduler
    </a>
    <a href="#" class="nav-link-item">
      <i class="bi bi-shield-fill-check"></i> Security
    </a>
    <a href="#" class="nav-link-item">
      <i class="bi bi-gear-fill"></i> Settings
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-mini" id="userDropdownToggle">
      <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
      <div class="user-info">
        <div class="user-name">{{ auth()->user()->name }}</div>
        <div class="user-role">Super Admin</div>
      </div>
      <i class="bi bi-three-dots-vertical" style="color:var(--text-3);font-size:0.85rem;"></i>
    </div>
    {{-- Dropdown --}}
    <div id="userDropdownMenu" style="display:none;margin-top:0.5rem;background:var(--bg-card);border:1px solid var(--border-2);border-radius:8px;overflow:hidden;">
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
  <div class="topbar">
    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
      <i class="bi bi-list"></i>
    </button>
    <div>
      @hasSection('breadcrumb')
        <div style="font-size:0.7rem;color:var(--text-3);margin-bottom:0.2rem;">
          @yield('breadcrumb')
        </div>
      @endif
      <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
      @hasSection('page-sub')
        <div class="topbar-sub">@yield('page-sub')</div>
      @endif
    </div>

    <div class="topbar-search">
      <i class="bi bi-search"></i>
      <input type="text" placeholder="Search tenants, metrics…" />
    </div>

    <div class="topbar-actions">
      <div class="icon-btn" title="Notifications">
        <i class="bi bi-bell"></i>
        <span class="notif-dot"></span>
      </div>
      @yield('topbar-actions')
    </div>
  </div>

  {{-- ── PAGE CONTENT ── --}}
  <div class="page-content">

    {{-- Flash messages --}}
    @if(session('success'))
      <div class="flash-alert flash-success fade-in">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="flash-alert flash-error fade-in">
        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
      </div>
    @endif
    @if($errors->any())
      <div class="flash-alert flash-error fade-in">
        <i class="bi bi-exclamation-circle-fill"></i>
        {{ $errors->first() }}
      </div>
    @endif

    @yield('content')
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // User dropdown toggle
  document.getElementById('userDropdownToggle')?.addEventListener('click', function() {
    const menu = document.getElementById('userDropdownMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
  });
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#userDropdownToggle') && !e.target.closest('#userDropdownMenu')) {
      const menu = document.getElementById('userDropdownMenu');
      if (menu) menu.style.display = 'none';
    }
  });

  // Flash message auto-hide
  setTimeout(() => {
    document.querySelectorAll('.flash-alert').forEach(el => {
      el.style.transition = 'opacity 0.5s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    });
  }, 4000);
</script>
@stack('scripts')
</body>
</html>
