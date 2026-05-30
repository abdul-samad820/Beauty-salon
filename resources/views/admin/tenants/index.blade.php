<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LUMIÈRE Admin · Tenant Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@200;300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    :root{--bg:#0a0a0c;--bg-2:#0f0f12;--bg-card:#13131a;--border:rgba(255,255,255,0.06);--border-2:rgba(255,255,255,0.1);--gold:#c9a96e;--gold-dim:rgba(201,169,110,0.15);--gold-glow:rgba(201,169,110,0.3);--teal:#2d7d6f;--teal-light:#3a9e8d;--teal-dim:rgba(45,125,111,0.15);--purple:#7c5cbf;--purple-dim:rgba(124,92,191,0.15);--emerald:#10b981;--emerald-dim:rgba(16,185,129,0.12);--rose:#f43f5e;--rose-dim:rgba(244,63,94,0.12);--text:rgba(255,255,255,0.88);--text-2:rgba(255,255,255,0.50);--text-3:rgba(255,255,255,0.28);--ff-display:'Cormorant Garamond',serif;--ff-body:'Jost',sans-serif;--sidebar-w:240px;--transition:0.4s cubic-bezier(0.22,1,0.36,1)}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:var(--ff-body);background:var(--bg);color:var(--text);font-weight:300;overflow-x:hidden}
    .sidebar{position:fixed;top:0;left:0;width:var(--sidebar-w);height:100vh;background:var(--bg-2);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;transition:transform var(--transition)}
    .sidebar-logo{padding:1.8rem 1.5rem 1.4rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.7rem}
    .logo-mark{width:34px;height:34px;background:linear-gradient(135deg,var(--gold),#e8c48a);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:1.1rem;font-weight:500;color:#1a1400;flex-shrink:0}
    .logo-text{font-family:var(--ff-display);font-size:1.2rem;font-weight:400;color:var(--text);letter-spacing:.08em}
    .logo-text span{color:var(--gold)}
    .logo-badge{font-size:.52rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);background:var(--gold-dim);border:1px solid rgba(201,169,110,.2);padding:.15rem .5rem;border-radius:20px;margin-top:.1rem}
    .sidebar-nav{flex:1;overflow-y:auto;padding:1.2rem 0}
    .sidebar-nav::-webkit-scrollbar{width:4px}
    .sidebar-nav::-webkit-scrollbar-thumb{background:var(--border-2);border-radius:2px}
    .nav-section-label{font-size:.58rem;font-weight:600;letter-spacing:.28em;text-transform:uppercase;color:var(--text-3);padding:.8rem 1.5rem .4rem}
    .nav-link-item{display:flex;align-items:center;gap:.75rem;padding:.65rem 1.5rem;color:var(--text-2);text-decoration:none;font-size:.82rem;font-weight:400;border-left:2px solid transparent;transition:color .25s,background .25s,border-color .25s}
    .nav-link-item i{font-size:.95rem;width:18px;text-align:center}
    .nav-link-item:hover{color:var(--text);background:rgba(255,255,255,.03)}
    .nav-link-item.active{color:var(--gold);background:var(--gold-dim);border-left-color:var(--gold)}
    .nav-badge{margin-left:auto;background:var(--rose-dim);color:var(--rose);font-size:.6rem;font-weight:600;padding:.15rem .45rem;border-radius:20px}
    .nav-badge.green{background:var(--emerald-dim);color:var(--emerald)}
    .nav-badge.gold{background:var(--gold-dim);color:var(--gold)}
    .sidebar-footer{padding:1rem 1.5rem;border-top:1px solid var(--border)}
    .user-mini{display:flex;align-items:center;gap:.75rem;padding:.6rem;border-radius:8px;cursor:pointer;transition:background .25s}
    .user-mini:hover{background:rgba(255,255,255,.04)}
    .user-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--teal));display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:600;color:white;flex-shrink:0}
    .user-name{font-size:.78rem;font-weight:500;color:var(--text)}
    .user-role{font-size:.62rem;color:var(--text-3)}
    .main-wrap{margin-left:var(--sidebar-w);min-height:100vh;display:flex;flex-direction:column}
    .topbar{position:sticky;top:0;z-index:90;background:rgba(10,10,12,.85);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:.85rem 2rem;display:flex;align-items:center;gap:1.5rem}
    .topbar-title{font-family:var(--ff-display);font-size:1.25rem;font-weight:400;letter-spacing:.02em}
    .topbar-sub{font-size:.72rem;color:var(--text-3)}
    .topbar-search{flex:1;max-width:320px;margin-left:auto;position:relative}
    .topbar-search input{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.8rem;padding:.55rem 1rem .55rem 2.4rem;outline:none;transition:border-color .3s}
    .topbar-search input:focus{border-color:var(--gold)}
    .topbar-search input::placeholder{color:var(--text-3)}
    .topbar-search i{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:.85rem}
    .topbar-actions{display:flex;align-items:center;gap:.75rem}
    .icon-btn{width:36px;height:36px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-2);cursor:pointer;position:relative;transition:background .25s,color .25s}
    .icon-btn:hover{background:rgba(255,255,255,.07);color:var(--text)}
    .notif-dot{position:absolute;top:6px;right:6px;width:7px;height:7px;background:var(--rose);border-radius:50%;border:1.5px solid var(--bg)}
    .btn-gold{background:var(--gold);border:none;color:#1a1400;font-family:var(--ff-body);font-size:.72rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:.5rem 1.2rem;border-radius:6px;cursor:pointer;text-decoration:none;transition:background .3s,box-shadow .3s;display:inline-flex;align-items:center;gap:.5rem}
    .btn-gold:hover{background:#dbb97e;box-shadow:0 4px 20px var(--gold-glow);color:#1a1400}
    .btn-ghost{background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-family:var(--ff-body);font-size:.72rem;padding:.4rem 1rem;border-radius:6px;cursor:pointer;text-decoration:none;transition:background .25s,color .25s,border-color .25s;display:inline-flex;align-items:center;gap:.4rem}
    .btn-ghost:hover{background:rgba(255,255,255,.05);color:var(--text);border-color:var(--gold)}
    .page-content{padding:2rem;flex:1}
    .card-glass{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;position:relative;overflow:hidden}
    .card-glass::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent)}
    .mini-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:1.2rem 1.4rem;display:flex;align-items:center;gap:1rem;transition:border-color .3s}
    .mini-stat:hover{border-color:var(--border-2)}
    .mini-stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
    .mini-stat-val{font-family:var(--ff-display);font-size:1.8rem;font-weight:400;line-height:1}
    .mini-stat-label{font-size:.65rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--text-3);margin-top:.2rem}
    .filter-bar{display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;margin-bottom:1.5rem}
    .filter-input{background:rgba(255,255,255,.04);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.8rem;padding:.5rem 1rem;outline:none;transition:border-color .3s}
    .filter-input:focus{border-color:var(--gold)}
    .filter-input::placeholder{color:var(--text-3)}
    select.filter-input option{background:var(--bg-card);color:var(--text)}
    .filter-tabs{display:flex;gap:.4rem;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:.3rem}
    .filter-tab{padding:.35rem .9rem;border-radius:6px;font-size:.72rem;font-weight:500;color:var(--text-2);cursor:pointer;transition:all .25s;border:none;background:transparent}
    .filter-tab.active{background:var(--gold);color:#1a1400}
    .filter-tab:hover:not(.active){background:rgba(255,255,255,.05);color:var(--text)}
    .tenant-table{width:100%;border-collapse:collapse}
    .tenant-table thead th{font-size:.6rem;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:var(--text-3);padding:.9rem 1.2rem;border-bottom:1px solid var(--border);background:rgba(255,255,255,.01);text-align:left;white-space:nowrap;cursor:pointer;transition:color .2s;user-select:none}
    .tenant-table thead th:hover{color:var(--text-2)}
    .tenant-table thead th.sorted{color:var(--gold)}
    .tenant-table tbody td{padding:.95rem 1.2rem;font-size:.82rem;font-weight:300;color:var(--text-2);border-bottom:1px solid rgba(255,255,255,.03);vertical-align:middle;white-space:nowrap}
    .tenant-table tbody tr:hover td{background:rgba(255,255,255,.02);color:var(--text)}
    .tenant-table tbody tr:last-child td{border-bottom:none}
    .tenant-av{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:1rem;font-weight:500;flex-shrink:0}
    .tenant-name-wrap{display:flex;align-items:center;gap:.75rem}
    .tenant-name{font-weight:500;color:var(--text);font-size:.85rem}
    .tenant-slug{font-size:.68rem;color:var(--text-3);margin-top:.1rem;font-family:monospace}
    .plan-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.6rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;padding:.25rem .7rem;border-radius:20px}
    .plan-enterprise{background:var(--gold-dim);color:var(--gold);border:1px solid rgba(201,169,110,.2)}
    .plan-pro{background:var(--purple-dim);color:#a78bfa;border:1px solid rgba(167,139,250,.15)}
    .plan-starter{background:var(--teal-dim);color:var(--teal-light);border:1px solid rgba(58,158,141,.15)}
    .status-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.6rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:.25rem .65rem;border-radius:20px}
    .badge-active{background:var(--emerald-dim);color:var(--emerald)}
    .badge-inactive{background:rgba(255,255,255,.05);color:var(--text-3)}
    .badge-trial{background:var(--gold-dim);color:var(--gold)}
    .badge-suspended{background:var(--rose-dim);color:var(--rose)}
    .action-btn{width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:transparent;display:inline-flex;align-items:center;justify-content:center;color:var(--text-3);cursor:pointer;transition:all .2s;font-size:.8rem;text-decoration:none}
    .action-btn:hover{border-color:var(--gold);color:var(--gold);background:var(--gold-dim)}
    .action-btn.danger:hover{border-color:var(--rose);color:var(--rose);background:var(--rose-dim)}
    .action-btn.success:hover{border-color:var(--emerald);color:var(--emerald);background:var(--emerald-dim)}
    .pagination-custom{display:flex;align-items:center;gap:.4rem}
    .page-btn{width:32px;height:32px;border-radius:6px;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;color:var(--text-2);cursor:pointer;transition:all .25s;font-size:.78rem;text-decoration:none}
    .page-btn:hover{border-color:var(--gold);color:var(--gold);background:var(--gold-dim)}
    .page-btn.active{background:var(--gold);color:#1a1400;border-color:var(--gold);font-weight:600}
    .page-btn.disabled{opacity:.3;pointer-events:none}
    .tenant-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:1.5rem;transition:all var(--transition);position:relative;overflow:hidden}
    .tenant-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--gold-dim),transparent);opacity:0;transition:opacity .3s}
    .tenant-card:hover{border-color:var(--border-2);box-shadow:0 8px 40px rgba(0,0,0,.4);transform:translateY(-2px)}
    .tenant-card:hover::before{opacity:1}
    .tc-logo{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:1.2rem;font-weight:500;flex-shrink:0}
    .tc-stat{text-align:center;padding:.6rem .5rem;background:rgba(255,255,255,.03);border-radius:8px}
    .tc-stat-val{font-family:var(--ff-display);font-size:1.2rem;font-weight:400;color:var(--text);line-height:1}
    .tc-stat-label{font-size:.58rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-3);margin-top:.2rem}
    .rev-bar-wrap{height:4px;background:rgba(255,255,255,.06);border-radius:2px;margin-top:.3rem;overflow:hidden}
    .rev-bar-fill{height:100%;border-radius:2px;transition:width 1.2s cubic-bezier(.22,1,.36,1)}
    .fade-in-up{animation:fadeInUp .7s ease both}
    .stagger-1{animation-delay:.05s}.stagger-2{animation-delay:.1s}.stagger-3{animation-delay:.15s}.stagger-4{animation-delay:.2s}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
    .sidebar-toggle{display:none;background:none;border:none;color:var(--text);font-size:1.2rem;cursor:pointer}
    @media(max-width:992px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:none}.main-wrap{margin-left:0}.sidebar-toggle{display:flex}.page-content{padding:1.2rem}}
  </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-mark">L</div>
    <div><div class="logo-text">LUMIÈRE<span>.</span></div><div class="logo-badge">Super Admin</div></div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Platform</div>
    <a href="dashboard.html" class="nav-link-item"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
    <a href="tenants.html" class="nav-link-item active"><i class="bi bi-buildings-fill"></i> Tenants <span class="nav-badge green">104</span></a>
    <a href="#" class="nav-link-item"><i class="bi bi-graph-up-arrow"></i> Analytics</a>
    <a href="#" class="nav-link-item"><i class="bi bi-currency-dollar"></i> Revenue</a>
    <div class="nav-section-label">Operations</div>
    <a href="#" class="nav-link-item"><i class="bi bi-calendar2-check-fill"></i> Appointments <span class="nav-badge gold">1.2K</span></a>
    <a href="#" class="nav-link-item"><i class="bi bi-layers-fill"></i> Subscriptions</a>
    <a href="#" class="nav-link-item"><i class="bi bi-bell-fill"></i> Reminders <span class="nav-badge">3</span></a>
    <a href="#" class="nav-link-item"><i class="bi bi-box-seam-fill"></i> Inventory</a>
    <div class="nav-section-label">System</div>
    <a href="#" class="nav-link-item"><i class="bi bi-activity"></i> Queue Monitor</a>
    <a href="#" class="nav-link-item"><i class="bi bi-clock-history"></i> Scheduler</a>
    <a href="#" class="nav-link-item"><i class="bi bi-shield-fill-check"></i> Security</a>
    <a href="#" class="nav-link-item"><i class="bi bi-gear-fill"></i> Settings</a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-mini">
      <div class="user-avatar">SA</div>
      <div style="flex:1;min-width:0"><div class="user-name">Admin Rashid</div><div class="user-role">Super Admin</div></div>
      <i class="bi bi-three-dots-vertical" style="color:var(--text-3);font-size:.85rem"></i>
    </div>
  </div>
</aside>

<div class="main-wrap">
  <div class="topbar">
    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
    <div><div class="topbar-title">Tenant Management</div><div class="topbar-sub">104 parlours · 97 active · 3 trials · 4 suspended</div></div>
    <div class="topbar-search"><i class="bi bi-search"></i><input type="text" placeholder="Search tenants…" id="searchInput" /></div>
    <div class="topbar-actions">
      <div class="icon-btn"><i class="bi bi-bell"></i><span class="notif-dot"></span></div>
      <div class="icon-btn" onclick="toggleView()" title="Toggle View"><i class="bi bi-grid" id="viewIcon"></i></div>
      <a href="create-tenant.html" class="btn-gold"><i class="bi bi-plus-lg"></i> Add Tenant</a>
    </div>
  </div>

  <div class="page-content">
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3 fade-in-up stagger-1">
        <div class="mini-stat" style="border-top:2px solid var(--gold)">
          <div class="mini-stat-icon" style="background:var(--gold-dim);color:var(--gold)"><i class="bi bi-buildings-fill"></i></div>
          <div><div class="mini-stat-val" style="color:var(--gold)">104</div><div class="mini-stat-label">Total Tenants</div></div>
        </div>
      </div>
      <div class="col-6 col-md-3 fade-in-up stagger-2">
        <div class="mini-stat" style="border-top:2px solid var(--emerald)">
          <div class="mini-stat-icon" style="background:var(--emerald-dim);color:var(--emerald)"><i class="bi bi-circle-fill"></i></div>
          <div><div class="mini-stat-val" style="color:var(--emerald)">97</div><div class="mini-stat-label">Active</div></div>
        </div>
      </div>
      <div class="col-6 col-md-3 fade-in-up stagger-3">
        <div class="mini-stat" style="border-top:2px solid var(--gold)">
          <div class="mini-stat-icon" style="background:var(--gold-dim);color:var(--gold)"><i class="bi bi-hourglass-split"></i></div>
          <div><div class="mini-stat-val" style="color:var(--gold)">3</div><div class="mini-stat-label">Trial Period</div></div>
        </div>
      </div>
      <div class="col-6 col-md-3 fade-in-up stagger-4">
        <div class="mini-stat" style="border-top:2px solid var(--rose)">
          <div class="mini-stat-icon" style="background:var(--rose-dim);color:var(--rose)"><i class="bi bi-pause-circle-fill"></i></div>
          <div><div class="mini-stat-val" style="color:var(--rose)">4</div><div class="mini-stat-label">Suspended</div></div>
        </div>
      </div>
    </div>

    <div class="filter-bar fade-in-up stagger-2">
      <div class="filter-tabs">
        <button class="filter-tab active" onclick="setTab(this,'all')">All</button>
        <button class="filter-tab" onclick="setTab(this,'active')">Active</button>
        <button class="filter-tab" onclick="setTab(this,'trial')">Trial</button>
        <button class="filter-tab" onclick="setTab(this,'suspended')">Suspended</button>
      </div>
      <select class="filter-input" style="min-width:130px">
        <option value="">All Plans</option>
        <option>Enterprise</option><option>Pro</option><option>Starter</option>
      </select>
      <select class="filter-input" style="min-width:130px">
        <option value="">All Cities</option>
        <option>Mumbai</option><option>Delhi</option><option>Bangalore</option><option>Hyderabad</option><option>Pune</option>
      </select>
      <select class="filter-input" style="min-width:140px">
        <option>Sort: Revenue ↓</option><option>Sort: Revenue ↑</option><option>Sort: Name A–Z</option><option>Sort: Newest First</option>
      </select>
      <button class="btn-ghost ms-auto"><i class="bi bi-download"></i> Export CSV</button>
    </div>

    <div id="tableView" class="card-glass fade-in-up stagger-3">
      <div style="overflow-x:auto">
        <table class="tenant-table">
          <thead>
            <tr>
              <th><input type="checkbox" style="accent-color:var(--gold)" /></th>
              <th class="sorted">Tenant <i class="bi bi-chevron-down" style="font-size:.55rem"></i></th>
              <th>City</th><th>Plan</th><th>Status</th><th>Staff</th><th>Services</th><th>Bookings</th><th>Revenue</th><th>Joined</th><th>Actions</th>
            </tr>
          </thead>
          <tbody id="tenantTableBody"></tbody>
        </table>
      </div>
      <div style="padding:1rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.8rem">
        <div style="font-size:.75rem;color:var(--text-3)">Showing <strong style="color:var(--text-2)">1–10</strong> of <strong style="color:var(--text-2)">104</strong> tenants</div>
        <div class="pagination-custom">
          <a href="#" class="page-btn disabled"><i class="bi bi-chevron-left"></i></a>
          <a href="#" class="page-btn active">1</a>
          <a href="#" class="page-btn">2</a>
          <a href="#" class="page-btn">3</a>
          <span style="color:var(--text-3);font-size:.8rem;padding:0 .3rem">…</span>
          <a href="#" class="page-btn">11</a>
          <a href="#" class="page-btn"><i class="bi bi-chevron-right"></i></a>
        </div>
      </div>
    </div>

    <div id="cardView" style="display:none">
      <div class="row g-3" id="tenantCardGrid"></div>
      <div class="text-center mt-4"><button class="btn-ghost"><i class="bi bi-arrow-down"></i> Load More</button></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const TENANTS=[
  {name:'Glamour Studio',slug:'glamour-studio',city:'Mumbai',plan:'Pro',status:'Active',staff:12,services:24,bookings:1842,rev:284200,joined:'Jan 2024',color:'#a78bfa'},
  {name:'Bella Luxe Salon',slug:'bella-luxe',city:'Delhi',plan:'Enterprise',status:'Active',staff:28,services:48,bookings:4210,rev:824000,joined:'Mar 2023',color:'#c9a96e'},
  {name:'Silk Touch Beauty',slug:'silk-touch',city:'Bangalore',plan:'Starter',status:'Trial',staff:5,services:12,bookings:210,rev:21500,joined:'May 2026',color:'#3a9e8d'},
  {name:'Velvet Chair',slug:'velvet-chair',city:'Hyderabad',plan:'Pro',status:'Active',staff:18,services:32,bookings:2140,rev:367800,joined:'Jul 2023',color:'#a78bfa'},
  {name:'Pearl Beauty Hub',slug:'pearl-beauty',city:'Pune',plan:'Starter',status:'Suspended',staff:4,services:9,bookings:0,rev:0,joined:'Oct 2024',color:'#f43f5e'},
  {name:'Aura Wellness',slug:'aura-wellness',city:'Jaipur',plan:'Pro',status:'Active',staff:14,services:28,bookings:1560,rev:218400,joined:'Feb 2024',color:'#60a5fa'},
  {name:'Orchid Salon',slug:'orchid-salon',city:'Chennai',plan:'Enterprise',status:'Active',staff:22,services:41,bookings:3140,rev:641200,joined:'Nov 2022',color:'#c9a96e'},
  {name:'Rose Studio',slug:'rose-studio',city:'Kolkata',plan:'Starter',status:'Trial',staff:6,services:14,bookings:310,rev:34800,joined:'Apr 2026',color:'#f9a8d4'},
  {name:'Luxe & Co.',slug:'luxe-co',city:'Ahmedabad',plan:'Pro',status:'Active',staff:16,services:30,bookings:1870,rev:298600,joined:'Jun 2023',color:'#a78bfa'},
  {name:'Mirror Mirror',slug:'mirror-mirror',city:'Surat',plan:'Enterprise',status:'Suspended',staff:9,services:18,bookings:0,rev:0,joined:'Aug 2023',color:'#f43f5e'},
];
const planClass={Enterprise:'plan-enterprise',Pro:'plan-pro',Starter:'plan-starter'};
const statusClass={Active:'badge-active',Trial:'badge-trial',Suspended:'badge-suspended'};
function fmtRev(n){if(n===0)return'—';if(n>=100000)return'₹'+(n/100000).toFixed(1)+'L';return'₹'+(n/1000).toFixed(0)+'K';}
function initials(name){return name.split(' ').slice(0,2).map(w=>w[0]).join('');}
function buildTable(data){
  document.getElementById('tenantTableBody').innerHTML=data.map(t=>`
    <tr>
      <td><input type="checkbox" style="accent-color:var(--gold)"></td>
      <td><div class="tenant-name-wrap"><div class="tenant-av" style="background:${t.color}22;color:${t.color}">${initials(t.name)}</div><div><div class="tenant-name">${t.name}</div><div class="tenant-slug">${t.slug}</div></div></div></td>
      <td>${t.city}</td>
      <td><span class="plan-badge ${planClass[t.plan]}">${t.plan}</span></td>
      <td><span class="status-badge ${statusClass[t.status]}"><i class="bi bi-circle-fill" style="font-size:.4rem"></i>${t.status}</span></td>
      <td>${t.staff}</td><td>${t.services}</td><td>${t.bookings.toLocaleString()}</td>
      <td style="color:${t.rev>0?'var(--emerald)':'var(--text-3)'};font-weight:${t.rev>0?'500':'300'}">${fmtRev(t.rev)}</td>
      <td>${t.joined}</td>
      <td><div style="display:flex;gap:.35rem"><a href="tenant-detail.html" class="action-btn" title="View"><i class="bi bi-eye"></i></a><a href="#" class="action-btn success"><i class="bi bi-check-lg"></i></a><a href="#" class="action-btn danger"><i class="bi bi-pause-fill"></i></a><a href="#" class="action-btn danger"><i class="bi bi-trash3"></i></a></div></td>
    </tr>`).join('');
}
function buildCards(data){
  document.getElementById('tenantCardGrid').innerHTML=data.map(t=>`
    <div class="col-sm-6 col-lg-4 col-xl-3">
      <div class="tenant-card">
        <div style="display:flex;align-items:center;gap:.8rem;margin-bottom:1rem">
          <div class="tc-logo" style="background:${t.color}22;color:${t.color}">${initials(t.name)}</div>
          <div style="flex:1;min-width:0"><div style="font-weight:500;color:var(--text);font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${t.name}</div><div style="font-size:.68rem;color:var(--text-3)">${t.city}</div></div>
          <span class="status-badge ${statusClass[t.status]}" style="font-size:.55rem">${t.status}</span>
        </div>
        <span class="plan-badge ${planClass[t.plan]}" style="margin-bottom:1rem;display:inline-flex">${t.plan}</span>
        <div class="row g-2 mb-3" style="margin-top:.5rem">
          <div class="col-4"><div class="tc-stat"><div class="tc-stat-val">${t.staff}</div><div class="tc-stat-label">Staff</div></div></div>
          <div class="col-4"><div class="tc-stat"><div class="tc-stat-val">${t.services}</div><div class="tc-stat-label">Services</div></div></div>
          <div class="col-4"><div class="tc-stat"><div class="tc-stat-val">${(t.bookings/1000).toFixed(1)}K</div><div class="tc-stat-label">Bookings</div></div></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:.3rem"><span style="font-size:.7rem;color:var(--text-3)">Revenue</span><span style="font-size:.75rem;font-weight:500;color:${t.rev>0?'var(--emerald)':'var(--text-3)'}">${fmtRev(t.rev)}</span></div>
        <div class="rev-bar-wrap"><div class="rev-bar-fill" style="width:${Math.min(t.rev/824000*100,100)}%;background:${t.color}"></div></div>
        <div style="display:flex;gap:.5rem;margin-top:1rem">
          <a href="tenant-detail.html" class="btn-ghost" style="flex:1;justify-content:center;font-size:.68rem">View Details</a>
          <a href="#" class="action-btn danger"><i class="bi bi-pause-fill"></i></a>
        </div>
      </div>
    </div>`).join('');
}
buildTable(TENANTS);buildCards(TENANTS);
let isCard=false;
function toggleView(){isCard=!isCard;document.getElementById('tableView').style.display=isCard?'none':'block';document.getElementById('cardView').style.display=isCard?'block':'none';document.getElementById('viewIcon').className=isCard?'bi bi-list':'bi bi-grid';}
function setTab(btn,val){document.querySelectorAll('.filter-tab').forEach(b=>b.classList.remove('active'));btn.classList.add('active');const f=val==='all'?TENANTS:TENANTS.filter(t=>t.status.toLowerCase()===val);buildTable(f);buildCards(f);}
document.getElementById('searchInput').addEventListener('input',function(){const q=this.value.toLowerCase();const f=TENANTS.filter(t=>t.name.toLowerCase().includes(q)||t.city.toLowerCase().includes(q));buildTable(f);buildCards(f);});
</script>
</body>
</html>
