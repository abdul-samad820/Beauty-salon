<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LUMIÈRE Admin · Bella Luxe Salon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@200;300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    :root{--bg:#0a0a0c;--bg-2:#0f0f12;--bg-card:#13131a;--border:rgba(255,255,255,0.06);--border-2:rgba(255,255,255,0.1);--gold:#c9a96e;--gold-dim:rgba(201,169,110,0.15);--gold-glow:rgba(201,169,110,0.3);--teal-light:#3a9e8d;--teal-dim:rgba(45,125,111,0.15);--purple-dim:rgba(124,92,191,0.15);--emerald:#10b981;--emerald-dim:rgba(16,185,129,0.12);--rose:#f43f5e;--rose-dim:rgba(244,63,94,0.12);--text:rgba(255,255,255,0.88);--text-2:rgba(255,255,255,0.50);--text-3:rgba(255,255,255,0.28);--ff-display:'Cormorant Garamond',serif;--ff-body:'Jost',sans-serif;--sidebar-w:240px;--transition:0.4s cubic-bezier(0.22,1,0.36,1)}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:var(--ff-body);background:var(--bg);color:var(--text);font-weight:300;overflow-x:hidden}
    .sidebar{position:fixed;top:0;left:0;width:var(--sidebar-w);height:100vh;background:var(--bg-2);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;transition:transform var(--transition)}
    .sidebar-logo{padding:1.8rem 1.5rem 1.4rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.7rem}
    .logo-mark{width:34px;height:34px;background:linear-gradient(135deg,var(--gold),#e8c48a);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:1.1rem;font-weight:500;color:#1a1400}
    .logo-text{font-family:var(--ff-display);font-size:1.2rem;font-weight:400;color:var(--text);letter-spacing:.08em}
    .logo-text span{color:var(--gold)}
    .logo-badge{font-size:.52rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);background:var(--gold-dim);border:1px solid rgba(201,169,110,.2);padding:.15rem .5rem;border-radius:20px;margin-top:.1rem}
    .sidebar-nav{flex:1;overflow-y:auto;padding:1.2rem 0}
    .sidebar-nav::-webkit-scrollbar{width:4px}
    .sidebar-nav::-webkit-scrollbar-thumb{background:var(--border-2);border-radius:2px}
    .nav-section-label{font-size:.58rem;font-weight:600;letter-spacing:.28em;text-transform:uppercase;color:var(--text-3);padding:.8rem 1.5rem .4rem}
    .nav-link-item{display:flex;align-items:center;gap:.75rem;padding:.65rem 1.5rem;color:var(--text-2);text-decoration:none;font-size:.82rem;border-left:2px solid transparent;transition:color .25s,background .25s,border-color .25s}
    .nav-link-item i{font-size:.95rem;width:18px;text-align:center}
    .nav-link-item:hover{color:var(--text);background:rgba(255,255,255,.03)}
    .nav-link-item.active{color:var(--gold);background:var(--gold-dim);border-left-color:var(--gold)}
    .nav-badge{margin-left:auto;background:var(--rose-dim);color:var(--rose);font-size:.6rem;font-weight:600;padding:.15rem .45rem;border-radius:20px}
    .nav-badge.green{background:var(--emerald-dim);color:var(--emerald)}
    .nav-badge.gold{background:var(--gold-dim);color:var(--gold)}
    .sidebar-footer{padding:1rem 1.5rem;border-top:1px solid var(--border)}
    .user-mini{display:flex;align-items:center;gap:.75rem;padding:.6rem;border-radius:8px;cursor:pointer;transition:background .25s}
    .user-mini:hover{background:rgba(255,255,255,.04)}
    .user-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--teal-light));display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:600;color:white}
    .main-wrap{margin-left:var(--sidebar-w);min-height:100vh;display:flex;flex-direction:column}
    .topbar{position:sticky;top:0;z-index:90;background:rgba(10,10,12,.85);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:.85rem 2rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap}
    .topbar-title{font-family:var(--ff-display);font-size:1.25rem;font-weight:400;letter-spacing:.02em}
    .topbar-actions{display:flex;align-items:center;gap:.75rem;margin-left:auto;flex-wrap:wrap}
    .icon-btn{width:36px;height:36px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-2);cursor:pointer;position:relative;transition:background .25s,color .25s}
    .icon-btn:hover{background:rgba(255,255,255,.07);color:var(--text)}
    .notif-dot{position:absolute;top:6px;right:6px;width:7px;height:7px;background:var(--rose);border-radius:50%;border:1.5px solid var(--bg)}
    .btn-gold{background:var(--gold);border:none;color:#1a1400;font-family:var(--ff-body);font-size:.72rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:.5rem 1.2rem;border-radius:6px;cursor:pointer;text-decoration:none;transition:background .3s,box-shadow .3s;display:inline-flex;align-items:center;gap:.5rem}
    .btn-gold:hover{background:#dbb97e;box-shadow:0 4px 20px var(--gold-glow);color:#1a1400}
    .btn-ghost{background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-family:var(--ff-body);font-size:.72rem;padding:.4rem 1rem;border-radius:6px;cursor:pointer;text-decoration:none;transition:all .25s;display:inline-flex;align-items:center;gap:.4rem}
    .btn-ghost:hover{background:rgba(255,255,255,.05);color:var(--text);border-color:var(--gold)}
    .btn-danger{background:var(--rose-dim);border:1px solid rgba(244,63,94,.3);color:var(--rose);font-family:var(--ff-body);font-size:.72rem;font-weight:500;padding:.5rem 1.2rem;border-radius:6px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem;transition:all .3s}
    .btn-danger:hover{background:var(--rose);color:white}
    .page-content{padding:2rem;flex:1}
    .card-glass{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;position:relative;overflow:hidden;transition:border-color var(--transition)}
    .card-glass::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent)}
    .tenant-hero{background:linear-gradient(135deg,#13131a 0%,#1a1624 100%);border:1px solid rgba(201,169,110,.15);border-radius:16px;padding:2rem;position:relative;overflow:hidden;margin-bottom:1.5rem}
    .tenant-hero::after{content:'';position:absolute;top:-80px;right:-80px;width:300px;height:300px;background:radial-gradient(circle,rgba(201,169,110,.08),transparent 70%);border-radius:50%;pointer-events:none}
    .tenant-avatar-lg{width:72px;height:72px;border-radius:16px;background:linear-gradient(135deg,var(--gold),#e8c48a);display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:2rem;font-weight:400;color:#1a1400;flex-shrink:0}
    .plan-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.6rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;padding:.25rem .7rem;border-radius:20px}
    .plan-enterprise{background:var(--gold-dim);color:var(--gold);border:1px solid rgba(201,169,110,.2)}
    .status-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.6rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:.25rem .65rem;border-radius:20px}
    .badge-active{background:var(--emerald-dim);color:var(--emerald)}
    .kpi-sm{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:1.2rem}
    .kpi-sm-val{font-family:var(--ff-display);font-size:1.8rem;font-weight:400;line-height:1;margin-bottom:.3rem}
    .kpi-sm-label{font-size:.62rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--text-3)}
    .section-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem}
    .section-hdr-title{font-family:var(--ff-display);font-size:1.1rem;font-weight:400}
    .section-hdr-sub{font-size:.72rem;color:var(--text-3);margin-top:.1rem}
    .chart-wrap{position:relative;height:200px}
    .chart-wrap.md{height:240px}
    .progress-custom{height:4px;background:rgba(255,255,255,.06);border-radius:2px;overflow:hidden}
    .progress-fill{height:100%;border-radius:2px;transition:width 1.2s cubic-bezier(.22,1,.36,1)}
    .stock-item{display:flex;align-items:center;gap:1rem;padding:.9rem 0;border-bottom:1px solid rgba(255,255,255,.03)}
    .stock-item:last-child{border-bottom:none}
    .stock-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0}
    .stock-level{display:flex;flex-direction:column;align-items:flex-end;gap:.2rem;min-width:70px}
    .tl-item{display:flex;gap:.9rem;padding-bottom:1.2rem;position:relative}
    .tl-item:not(:last-child)::before{content:'';position:absolute;left:14px;top:30px;bottom:0;width:1px;background:var(--border)}
    .tl-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;flex-shrink:0;border:1px solid var(--border-2)}
    .tl-title{font-size:.82rem;font-weight:400;color:var(--text)}
    .tl-meta{font-size:.68rem;color:var(--text-3);margin-top:.15rem}
    .staff-table{width:100%;border-collapse:collapse}
    .staff-table th{font-size:.6rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--text-3);padding:.7rem 1rem;border-bottom:1px solid var(--border);text-align:left}
    .staff-table td{padding:.8rem 1rem;font-size:.8rem;color:var(--text-2);border-bottom:1px solid rgba(255,255,255,.03)}
    .staff-table tr:hover td{background:rgba(255,255,255,.02);color:var(--text)}
    .staff-table tr:last-child td{border-bottom:none}
    .staff-av{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#7c5cbf);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:600;color:white;flex-shrink:0}
    .detail-tabs{display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:1.5rem;overflow-x:auto}
    .detail-tab{padding:.65rem 1.2rem;font-size:.78rem;color:var(--text-3);cursor:pointer;border-bottom:2px solid transparent;transition:color .25s,border-color .25s;white-space:nowrap}
    .detail-tab.active{color:var(--gold);border-bottom-color:var(--gold)}
    .detail-tab:hover:not(.active){color:var(--text-2)}
    .tab-panel{display:none}.tab-panel.active{display:block}
    .fade-in-up{animation:fadeInUp .7s ease both}
    .stagger-1{animation-delay:.05s}.stagger-2{animation-delay:.1s}.stagger-3{animation-delay:.15s}.stagger-4{animation-delay:.2s}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
    .sidebar-toggle{display:none;background:none;border:none;color:var(--text);font-size:1.2rem;cursor:pointer}
    @media(max-width:992px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:none}.main-wrap{margin-left:0}.sidebar-toggle{display:flex}.page-content{padding:1rem}}
  </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo"><div class="logo-mark">L</div><div><div class="logo-text">LUMIÈRE<span>.</span></div><div class="logo-badge">Super Admin</div></div></div>
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
  <div class="sidebar-footer"><div class="user-mini"><div class="user-avatar">SA</div><div style="flex:1;min-width:0"><div style="font-size:.78rem;font-weight:500;color:var(--text)">Admin Rashid</div><div style="font-size:.62rem;color:var(--text-3)">Super Admin</div></div><i class="bi bi-three-dots-vertical" style="color:var(--text-3);font-size:.85rem"></i></div></div>
</aside>

<div class="main-wrap">
  <div class="topbar">
    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
    <div>
      <div style="font-size:.7rem;color:var(--text-3);margin-bottom:.2rem"><a href="tenants.html" style="color:var(--text-3);text-decoration:none">Tenants</a><i class="bi bi-chevron-right" style="font-size:.55rem;margin:0 .4rem"></i><span style="color:var(--text-2)">Bella Luxe Salon</span></div>
      <div class="topbar-title">Bella Luxe Salon</div>
    </div>
    <div class="topbar-actions">
      <div class="icon-btn"><i class="bi bi-bell"></i><span class="notif-dot"></span></div>
      <a href="#" class="btn-ghost"><i class="bi bi-arrow-up-circle"></i> Upgrade</a>
      <a href="#" class="btn-danger"><i class="bi bi-pause-fill"></i> Suspend</a>
      <a href="#" class="btn-gold"><i class="bi bi-pencil"></i> Edit</a>
    </div>
  </div>

  <div class="page-content">
    <!-- HERO -->
    <div class="tenant-hero fade-in-up stagger-1">
      <div class="d-flex flex-wrap align-items-start gap-3">
        <div class="tenant-avatar-lg">BL</div>
        <div style="flex:1;min-width:200px">
          <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.7rem;margin-bottom:.5rem">
            <h1 style="font-family:var(--ff-display);font-size:1.8rem;font-weight:400;margin:0">Bella Luxe Salon</h1>
            <span class="plan-badge plan-enterprise">Enterprise</span>
            <span class="status-badge badge-active"><i class="bi bi-circle-fill" style="font-size:.4rem"></i>Active</span>
          </div>
          <div style="font-size:.8rem;color:var(--text-3);margin-bottom:1rem"><i class="bi bi-geo-alt-fill" style="color:var(--gold);margin-right:.3rem"></i>Connaught Place, New Delhi &nbsp;·&nbsp;<i class="bi bi-globe2" style="color:var(--gold);margin-right:.3rem"></i>bella-luxe.lumiere.app &nbsp;·&nbsp;<i class="bi bi-calendar3" style="color:var(--gold);margin-right:.3rem"></i>Member since Mar 2023</div>
          <div style="display:flex;flex-wrap:wrap;gap:.5rem">
            <span style="font-size:.72rem;background:rgba(255,255,255,.05);padding:.3rem .8rem;border-radius:6px;color:var(--text-2)"><i class="bi bi-person-fill" style="color:var(--gold);margin-right:.3rem"></i>Priya Kapoor</span>
            <span style="font-size:.72rem;background:rgba(255,255,255,.05);padding:.3rem .8rem;border-radius:6px;color:var(--text-2)"><i class="bi bi-telephone-fill" style="color:var(--gold);margin-right:.3rem"></i>+91 98765 43210</span>
            <span style="font-size:.72rem;background:rgba(255,255,255,.05);padding:.3rem .8rem;border-radius:6px;color:var(--text-2)"><i class="bi bi-whatsapp" style="color:var(--emerald);margin-right:.3rem"></i>WA Enabled</span>
          </div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font-size:.65rem;color:var(--text-3);letter-spacing:.15em;text-transform:uppercase;margin-bottom:.3rem">Next Billing</div>
          <div style="font-family:var(--ff-display);font-size:1.5rem;color:var(--gold)">15 Jun 2026</div>
          <div style="font-size:.75rem;color:var(--text-3);margin-top:.2rem">₹12,000 / month</div>
        </div>
      </div>
    </div>

    <!-- KPI ROW -->
    <div class="row g-3 mb-3 fade-in-up stagger-2">
      <div class="col-6 col-md-3"><div class="kpi-sm" style="border-top:2px solid var(--emerald)"><div class="kpi-sm-val" style="color:var(--emerald)">₹8.24L</div><div class="kpi-sm-label">Total Revenue</div><div style="font-size:.68rem;color:var(--emerald);margin-top:.4rem"><i class="bi bi-arrow-up-right"></i> +18.4%</div></div></div>
      <div class="col-6 col-md-3"><div class="kpi-sm" style="border-top:2px solid #a78bfa"><div class="kpi-sm-val" style="color:#a78bfa">4,210</div><div class="kpi-sm-label">Total Bookings</div><div style="font-size:.68rem;color:#a78bfa;margin-top:.4rem"><i class="bi bi-arrow-up-right"></i> 287 this month</div></div></div>
      <div class="col-6 col-md-3"><div class="kpi-sm" style="border-top:2px solid var(--gold)"><div class="kpi-sm-val" style="color:var(--gold)">28</div><div class="kpi-sm-label">Staff Members</div><div style="font-size:.68rem;color:var(--text-3);margin-top:.4rem">3 branches</div></div></div>
      <div class="col-6 col-md-3"><div class="kpi-sm" style="border-top:2px solid var(--teal-light)"><div class="kpi-sm-val" style="color:var(--teal-light)">48</div><div class="kpi-sm-label">Services</div><div style="font-size:.68rem;color:var(--text-3);margin-top:.4rem">6 categories</div></div></div>
    </div>

    <!-- TABS -->
    <div class="detail-tabs fade-in-up stagger-2">
      <div class="detail-tab active" onclick="switchTab(this,'tab-overview')">Overview</div>
      <div class="detail-tab" onclick="switchTab(this,'tab-analytics')">Analytics</div>
      <div class="detail-tab" onclick="switchTab(this,'tab-staff')">Staff</div>
      <div class="detail-tab" onclick="switchTab(this,'tab-inventory')">Inventory</div>
      <div class="detail-tab" onclick="switchTab(this,'tab-activity')">Activity</div>
    </div>

    <!-- OVERVIEW TAB -->
    <div class="tab-panel active" id="tab-overview">
      <div class="row g-3">
        <div class="col-lg-8 fade-in-up stagger-2">
          <div class="card-glass p-4">
            <div class="section-hdr"><div><div class="section-hdr-title">Revenue Trend</div><div class="section-hdr-sub">Monthly revenue for Bella Luxe</div></div><div class="d-flex gap-2"><button class="btn-ghost" style="border-color:var(--gold);color:var(--gold);font-size:.65rem">6M</button><button class="btn-ghost" style="font-size:.65rem">1Y</button></div></div>
            <div class="chart-wrap md"><canvas id="tenantRevChart"></canvas></div>
          </div>
        </div>
        <div class="col-lg-4 fade-in-up stagger-3">
          <div class="card-glass p-4">
            <div class="section-hdr-title mb-3">Parlour Details</div>
            <div style="display:flex;flex-direction:column;gap:0">
              <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border)"><span style="font-size:.75rem;color:var(--text-3)">Tenant ID</span><span style="font-size:.75rem;color:var(--text-2);font-family:monospace">TEN-00042</span></div>
              <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border)"><span style="font-size:.75rem;color:var(--text-3)">DB Shard</span><span style="font-size:.75rem;color:var(--text-2);font-family:monospace">shard_04</span></div>
              <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border)"><span style="font-size:.75rem;color:var(--text-3)">Plan</span><span style="font-size:.75rem;color:var(--gold)">Enterprise</span></div>
              <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border)"><span style="font-size:.75rem;color:var(--text-3)">Billing</span><span style="font-size:.75rem;color:var(--text-2)">Monthly</span></div>
              <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border)"><span style="font-size:.75rem;color:var(--text-3)">WhatsApp</span><span style="font-size:.75rem;color:var(--emerald)"><i class="bi bi-check-circle-fill"></i> Connected</span></div>
              <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border)"><span style="font-size:.75rem;color:var(--text-3)">Email SMTP</span><span style="font-size:.75rem;color:var(--emerald)"><i class="bi bi-check-circle-fill"></i> Configured</span></div>
              <div style="display:flex;justify-content:space-between;padding:.6rem 0"><span style="font-size:.75rem;color:var(--text-3)">Last Login</span><span style="font-size:.75rem;color:var(--text-2)">2h ago</span></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6 fade-in-up stagger-3">
          <div class="card-glass p-4">
            <div class="section-hdr"><div><div class="section-hdr-title">Appointment Breakdown</div><div class="section-hdr-sub">By service category</div></div></div>
            <div class="chart-wrap"><canvas id="apptPieChart"></canvas></div>
          </div>
        </div>
        <div class="col-lg-6 fade-in-up stagger-4">
          <div class="card-glass p-4">
            <div class="section-hdr-title mb-3">Top Services by Revenue</div>
            <div style="display:flex;flex-direction:column;gap:.9rem">
              <div><div style="display:flex;justify-content:space-between;margin-bottom:.4rem"><span style="font-size:.78rem;color:var(--text-2)">Bridal Package</span><span style="font-size:.78rem;color:var(--gold)">₹2,10,000</span></div><div class="progress-custom"><div class="progress-fill" style="width:85%;background:var(--gold)"></div></div></div>
              <div><div style="display:flex;justify-content:space-between;margin-bottom:.4rem"><span style="font-size:.78rem;color:var(--text-2)">Hair Color & Treatment</span><span style="font-size:.78rem;color:#a78bfa">₹1,42,000</span></div><div class="progress-custom"><div class="progress-fill" style="width:58%;background:#a78bfa"></div></div></div>
              <div><div style="display:flex;justify-content:space-between;margin-bottom:.4rem"><span style="font-size:.78rem;color:var(--text-2)">Luxury Facial</span><span style="font-size:.78rem;color:var(--teal-light)">₹98,400</span></div><div class="progress-custom"><div class="progress-fill" style="width:40%;background:var(--teal-light)"></div></div></div>
              <div><div style="display:flex;justify-content:space-between;margin-bottom:.4rem"><span style="font-size:.78rem;color:var(--text-2)">Nail Couture</span><span style="font-size:.78rem;color:var(--emerald)">₹74,200</span></div><div class="progress-custom"><div class="progress-fill" style="width:30%;background:var(--emerald)"></div></div></div>
              <div><div style="display:flex;justify-content:space-between;margin-bottom:.4rem"><span style="font-size:.78rem;color:var(--text-2)">Body Sculpting</span><span style="font-size:.78rem;color:var(--rose)">₹58,600</span></div><div class="progress-custom"><div class="progress-fill" style="width:24%;background:var(--rose)"></div></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ANALYTICS TAB -->
    <div class="tab-panel" id="tab-analytics">
      <div class="row g-3">
        <div class="col-lg-8 fade-in-up"><div class="card-glass p-4"><div class="section-hdr"><div><div class="section-hdr-title">Weekly Booking Volume</div><div class="section-hdr-sub">Completed vs Cancelled</div></div></div><div class="chart-wrap md"><canvas id="weeklyChart"></canvas></div></div></div>
        <div class="col-lg-4 fade-in-up"><div class="card-glass p-4"><div class="section-hdr-title mb-3">Customer Analytics</div><div style="text-align:center;padding:1rem 0"><div style="font-family:var(--ff-display);font-size:3rem;font-weight:300;color:var(--emerald)">1,847</div><div style="font-size:.68rem;letter-spacing:.2em;text-transform:uppercase;color:var(--text-3)">Total Customers</div></div><div style="display:flex;justify-content:space-around;padding:1rem 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);margin-bottom:1rem"><div style="text-align:center"><div style="font-family:var(--ff-display);font-size:1.4rem;color:var(--gold)">68%</div><div style="font-size:.62rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.15em">Returning</div></div><div style="text-align:center"><div style="font-family:var(--ff-display);font-size:1.4rem;color:#a78bfa">32%</div><div style="font-size:.62rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.15em">New</div></div></div><div style="font-size:.75rem;color:var(--text-3);margin-bottom:.5rem">Avg. Visit Frequency</div><div style="font-family:var(--ff-display);font-size:1.4rem;color:var(--text)">2.3x / month</div><div style="font-size:.75rem;color:var(--text-3);margin-top:1rem;margin-bottom:.5rem">Net Promoter Score</div><div style="font-family:var(--ff-display);font-size:1.4rem;color:var(--emerald)">+72 NPS</div></div></div>
      </div>
    </div>

    <!-- STAFF TAB -->
    <div class="tab-panel" id="tab-staff">
      <div class="card-glass fade-in-up" style="overflow-x:auto">
        <table class="staff-table">
          <thead><tr><th>Staff Member</th><th>Role</th><th>Specialization</th><th>Bookings</th><th>Rating</th><th>Status</th></tr></thead>
          <tbody>
            <tr><td><div style="display:flex;align-items:center;gap:.7rem"><div class="staff-av">PK</div><div><div style="font-weight:500;color:var(--text)">Priya Kapoor</div><div style="font-size:.65rem;color:var(--text-3)">Owner</div></div></div></td><td>Owner / Stylist</td><td>Hair, Bridal</td><td style="color:var(--emerald)">84</td><td style="color:var(--gold)">★ 4.9</td><td><span class="status-badge badge-active">Active</span></td></tr>
            <tr><td><div style="display:flex;align-items:center;gap:.7rem"><div class="staff-av" style="background:linear-gradient(135deg,var(--gold),#e8c48a);color:#1a1400">SM</div><div><div style="font-weight:500;color:var(--text)">Sunita Mehra</div><div style="font-size:.65rem;color:var(--text-3)">Lead Therapist</div></div></div></td><td>Senior Esthetician</td><td>Facials, Skin</td><td style="color:var(--emerald)">62</td><td style="color:var(--gold)">★ 4.8</td><td><span class="status-badge badge-active">Active</span></td></tr>
            <tr><td><div style="display:flex;align-items:center;gap:.7rem"><div class="staff-av" style="background:linear-gradient(135deg,var(--teal-light),#2d7d6f)">AR</div><div><div style="font-weight:500;color:var(--text)">Anita Rawat</div><div style="font-size:.65rem;color:var(--text-3)">Nail Artist</div></div></div></td><td>Nail Couturist</td><td>Nails, Art</td><td style="color:var(--emerald)">91</td><td style="color:var(--gold)">★ 5.0</td><td><span class="status-badge badge-active">Active</span></td></tr>
            <tr><td><div style="display:flex;align-items:center;gap:.7rem"><div class="staff-av" style="background:linear-gradient(135deg,var(--rose),#c0392b)">RJ</div><div><div style="font-weight:500;color:var(--text)">Rekha Joshi</div><div style="font-size:.65rem;color:var(--text-3)">MUA</div></div></div></td><td>Makeup Artist</td><td>Bridal, Editorial</td><td style="color:var(--text-2)">28</td><td style="color:var(--gold)">★ 4.7</td><td><span class="status-badge" style="background:rgba(251,191,36,.1);color:#fbbf24">On Leave</span></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- INVENTORY TAB -->
    <div class="tab-panel" id="tab-inventory">
      <div class="card-glass p-4 fade-in-up">
        <div class="section-hdr"><div><div class="section-hdr-title" style="color:var(--rose)"><i class="bi bi-exclamation-triangle-fill" style="margin-right:.5rem"></i>Low Stock Alerts</div><div class="section-hdr-sub">Items requiring restocking</div></div><button class="btn-ghost"><i class="bi bi-bell"></i> Notify Owner</button></div>
        <div class="stock-item"><div class="stock-icon" style="background:var(--rose-dim);color:var(--rose)"><i class="bi bi-droplet-fill"></i></div><div style="flex:1"><div style="font-size:.85rem;font-weight:500;color:var(--text)">Kerastase Hair Serum</div><div style="font-size:.7rem;color:var(--text-3);margin-top:.1rem">SKU: KER-SER-250</div></div><div class="stock-level"><div style="font-size:.82rem;font-weight:600;color:var(--rose)">2 units</div><div class="progress-custom" style="width:60px"><div class="progress-fill" style="width:10%;background:var(--rose)"></div></div><div style="font-size:.6rem;color:var(--text-3)">Min: 10</div></div></div>
        <div class="stock-item"><div class="stock-icon" style="background:rgba(251,191,36,.1);color:#fbbf24"><i class="bi bi-palette-fill"></i></div><div style="flex:1"><div style="font-size:.85rem;font-weight:500;color:var(--text)">L'Oreal Inoa Hair Color</div><div style="font-size:.7rem;color:var(--text-3);margin-top:.1rem">SKU: LOE-COL-04B</div></div><div class="stock-level"><div style="font-size:.82rem;font-weight:600;color:#fbbf24">5 units</div><div class="progress-custom" style="width:60px"><div class="progress-fill" style="width:25%;background:#fbbf24"></div></div><div style="font-size:.6rem;color:var(--text-3)">Min: 15</div></div></div>
        <div class="stock-item"><div class="stock-icon" style="background:var(--rose-dim);color:var(--rose)"><i class="bi bi-stars"></i></div><div style="flex:1"><div style="font-size:.85rem;font-weight:500;color:var(--text)">Keratin Treatment Solution</div><div style="font-size:.7rem;color:var(--text-3);margin-top:.1rem">SKU: KER-TRE-500</div></div><div class="stock-level"><div style="font-size:.82rem;font-weight:600;color:var(--rose)">1 unit</div><div class="progress-custom" style="width:60px"><div class="progress-fill" style="width:5%;background:var(--rose)"></div></div><div style="font-size:.6rem;color:var(--text-3)">Min: 8</div></div></div>
      </div>
    </div>

    <!-- ACTIVITY TAB -->
    <div class="tab-panel" id="tab-activity">
      <div class="card-glass p-4 fade-in-up">
        <div class="section-hdr-title mb-3">Activity Timeline</div>
        <div class="tl-item"><div class="tl-dot" style="background:var(--emerald-dim);color:var(--emerald)"><i class="bi bi-calendar-check"></i></div><div><div class="tl-title">287 appointments booked this month — record</div><div class="tl-meta">May 2026 · Automated report</div></div></div>
        <div class="tl-item"><div class="tl-dot" style="background:var(--gold-dim);color:var(--gold)"><i class="bi bi-arrow-up-circle"></i></div><div><div class="tl-title">Plan upgraded Pro → <strong>Enterprise</strong></div><div class="tl-meta">12 Apr 2026 · Admin initiated</div></div></div>
        <div class="tl-item"><div class="tl-dot" style="background:rgba(59,130,246,.12);color:#60a5fa"><i class="bi bi-whatsapp"></i></div><div><div class="tl-title">WhatsApp Business API connected</div><div class="tl-meta">2 Apr 2026 · By Priya Kapoor</div></div></div>
        <div class="tl-item"><div class="tl-dot" style="background:var(--rose-dim);color:var(--rose)"><i class="bi bi-exclamation-triangle"></i></div><div><div class="tl-title">Low stock alert — Keratin &amp; Hair Color</div><div class="tl-meta">28 Mar 2026 · Scheduler job</div></div></div>
        <div class="tl-item"><div class="tl-dot" style="background:var(--emerald-dim);color:var(--emerald)"><i class="bi bi-buildings-fill"></i></div><div><div class="tl-title">Tenant onboarded to platform</div><div class="tl-meta">Mar 2023 · Super Admin</div></div></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
Chart.defaults.color='rgba(255,255,255,0.4)';Chart.defaults.borderColor='rgba(255,255,255,0.06)';Chart.defaults.font.family="'Jost',sans-serif";Chart.defaults.font.weight='300';
const gold='#c9a96e',emerald='#10b981',purple='#a78bfa',teal='#3a9e8d',rose='#f43f5e';
(function(){const ctx=document.getElementById('tenantRevChart').getContext('2d');const g=ctx.createLinearGradient(0,0,0,240);g.addColorStop(0,gold+'44');g.addColorStop(1,gold+'00');new Chart(ctx,{type:'line',data:{labels:['Dec','Jan','Feb','Mar','Apr','May'],datasets:[{label:'Revenue',data:[520000,610000,680000,720000,790000,824000],borderColor:gold,borderWidth:2,backgroundColor:g,fill:true,tension:0.4,pointRadius:5,pointBackgroundColor:gold}]},options:{responsive:true,maintainAspectRatio:false,animation:{duration:1500},plugins:{legend:{display:false},tooltip:{backgroundColor:'#1a1a24',borderColor:'rgba(255,255,255,.1)',borderWidth:1,callbacks:{label:v=>'₹'+v.raw.toLocaleString()}}},scales:{x:{grid:{display:false}},y:{grid:{color:'rgba(255,255,255,.04)'},ticks:{callback:v=>'₹'+(v/100000).toFixed(0)+'L'}}}}});})();
new Chart(document.getElementById('apptPieChart').getContext('2d'),{type:'doughnut',data:{labels:['Bridal','Hair','Facials','Nails','Body','Other'],datasets:[{data:[28,32,18,12,6,4],backgroundColor:[gold,purple,teal,emerald,rose,'rgba(255,255,255,0.15)'],borderColor:'#13131a',borderWidth:3}]},options:{responsive:true,maintainAspectRatio:false,cutout:'68%',animation:{duration:1200,animateScale:true},plugins:{legend:{position:'right',labels:{usePointStyle:true,pointStyleWidth:8,boxHeight:6,font:{size:11}}},tooltip:{backgroundColor:'#1a1a24',borderColor:'rgba(255,255,255,.1)',borderWidth:1}}}});
function switchTab(el,id){document.querySelectorAll('.detail-tab').forEach(t=>t.classList.remove('active'));document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));el.classList.add('active');document.getElementById(id).classList.add('active');if(id==='tab-analytics'&&!window._wChart){window._wChart=true;const ctx=document.getElementById('weeklyChart').getContext('2d');new Chart(ctx,{type:'bar',data:{labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],datasets:[{label:'Completed',data:[42,58,49,71,84,96,74],backgroundColor:purple+'bb',borderRadius:4},{label:'Cancelled',data:[4,7,3,8,5,2,6],backgroundColor:rose+'88',borderRadius:4}]},options:{responsive:true,maintainAspectRatio:false,animation:{duration:1200},plugins:{legend:{position:'top',labels:{usePointStyle:true,boxHeight:6}},tooltip:{backgroundColor:'#1a1a24',borderColor:'rgba(255,255,255,.1)',borderWidth:1}},scales:{x:{grid:{display:false}},y:{grid:{color:'rgba(255,255,255,.04)'}}}}});}}
</script>
</body>
</html>
