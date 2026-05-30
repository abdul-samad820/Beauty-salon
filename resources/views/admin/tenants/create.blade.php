<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LUMIÈRE Admin · Create New Tenant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@200;300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    :root{
      --bg:#0a0a0c;--bg-2:#0f0f12;--bg-card:#13131a;--bg-input:rgba(255,255,255,0.04);
      --border:rgba(255,255,255,0.06);--border-2:rgba(255,255,255,0.12);
      --gold:#c9a96e;--gold-dim:rgba(201,169,110,0.15);--gold-glow:rgba(201,169,110,0.35);
      --teal:#2d7d6f;--teal-light:#3a9e8d;--teal-dim:rgba(45,125,111,0.15);
      --purple:#7c5cbf;--purple-dim:rgba(124,92,191,0.15);
      --emerald:#10b981;--emerald-dim:rgba(16,185,129,0.12);
      --rose:#f43f5e;--rose-dim:rgba(244,63,94,0.12);
      --text:rgba(255,255,255,0.88);--text-2:rgba(255,255,255,0.50);--text-3:rgba(255,255,255,0.28);
      --ff-display:'Cormorant Garamond',serif;--ff-body:'Jost',sans-serif;
      --sidebar-w:240px;--transition:0.4s cubic-bezier(0.22,1,0.36,1);
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:var(--ff-body);background:var(--bg);color:var(--text);font-weight:300;overflow-x:hidden}

    /* ── SIDEBAR ── */
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

    /* ── MAIN ── */
    .main-wrap{margin-left:var(--sidebar-w);min-height:100vh;display:flex;flex-direction:column}
    .topbar{position:sticky;top:0;z-index:90;background:rgba(10,10,12,.85);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:.85rem 2rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap}
    .topbar-title{font-family:var(--ff-display);font-size:1.25rem;font-weight:400;letter-spacing:.02em}
    .topbar-sub{font-size:.72rem;color:var(--text-3)}
    .topbar-actions{display:flex;align-items:center;gap:.75rem;margin-left:auto}
    .icon-btn{width:36px;height:36px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-2);cursor:pointer;position:relative;transition:all .25s}
    .icon-btn:hover{background:rgba(255,255,255,.07);color:var(--text)}
    .notif-dot{position:absolute;top:6px;right:6px;width:7px;height:7px;background:var(--rose);border-radius:50%;border:1.5px solid var(--bg)}
    .btn-gold{background:var(--gold);border:none;color:#1a1400;font-family:var(--ff-body);font-size:.75rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:.6rem 1.5rem;border-radius:8px;cursor:pointer;text-decoration:none;transition:all .3s;display:inline-flex;align-items:center;gap:.5rem}
    .btn-gold:hover{background:#dbb97e;box-shadow:0 4px 24px var(--gold-glow);color:#1a1400}
    .btn-ghost{background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-family:var(--ff-body);font-size:.75rem;padding:.6rem 1.5rem;border-radius:8px;cursor:pointer;text-decoration:none;transition:all .25s;display:inline-flex;align-items:center;gap:.5rem}
    .btn-ghost:hover{background:rgba(255,255,255,.05);color:var(--text);border-color:var(--gold)}
    .page-content{padding:2rem;flex:1}

    /* ── STEPPER ── */
    .stepper-wrap{display:flex;align-items:center;gap:0;margin-bottom:2.5rem;position:relative}
    .stepper-wrap::before{content:'';position:absolute;top:18px;left:0;right:0;height:1px;background:var(--border-2);z-index:0}
    .step{display:flex;flex-direction:column;align-items:center;gap:.5rem;flex:1;position:relative;z-index:1;cursor:pointer}
    .step-circle{width:36px;height:36px;border-radius:50%;border:1.5px solid var(--border-2);background:var(--bg-card);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:500;color:var(--text-3);transition:all .4s;position:relative}
    .step-circle::after{content:'';position:absolute;inset:-4px;border-radius:50%;border:1px solid transparent;transition:border-color .4s}
    .step.active .step-circle{border-color:var(--gold);color:var(--gold);background:var(--gold-dim);box-shadow:0 0 20px var(--gold-glow)}
    .step.active .step-circle::after{border-color:rgba(201,169,110,.2)}
    .step.done .step-circle{border-color:var(--emerald);background:var(--emerald-dim);color:var(--emerald)}
    .step-label{font-size:.62rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-3);text-align:center;transition:color .4s;white-space:nowrap}
    .step.active .step-label{color:var(--gold)}
    .step.done .step-label{color:var(--emerald)}
    .step-line{flex:1;height:1px;background:var(--border-2);transition:background .4s;margin-top:-18px;z-index:0}
    .step-line.done{background:var(--emerald)}

    /* ── FORM PANELS ── */
    .form-panel{display:none;animation:panelIn .5s ease both}
    .form-panel.active{display:block}
    @keyframes panelIn{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:none}}

    /* ── FORM CARD ── */
    .form-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem;position:relative;overflow:hidden}
    .form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent)}
    .form-card-title{font-family:var(--ff-display);font-size:1.2rem;font-weight:400;color:var(--text);margin-bottom:.3rem}
    .form-card-sub{font-size:.75rem;color:var(--text-3);margin-bottom:1.8rem}

    /* ── FLOATING LABEL INPUTS ── */
    .fl-group{position:relative;margin-bottom:1.4rem}
    .fl-group label{position:absolute;top:50%;left:1rem;transform:translateY(-50%);font-size:.82rem;color:var(--text-3);pointer-events:none;transition:all .25s;background:transparent;padding:0 .2rem;letter-spacing:.04em}
    .fl-group.has-icon label{left:2.8rem}
    .fl-group input,.fl-group select,.fl-group textarea{width:100%;background:var(--bg-input);border:1px solid var(--border-2);border-radius:10px;color:var(--text);font-family:var(--ff-body);font-size:.85rem;font-weight:300;padding:.9rem 1rem;outline:none;transition:border-color .3s,background .3s,box-shadow .3s;appearance:none}
    .fl-group.has-icon input,.fl-group.has-icon select{padding-left:2.8rem}
    .fl-group textarea{padding-top:1.2rem;resize:vertical;min-height:100px}
    .fl-group input:focus,.fl-group select:focus,.fl-group textarea:focus{border-color:var(--gold);background:rgba(201,169,110,.04);box-shadow:0 0 0 3px rgba(201,169,110,.08)}
    .fl-group input:focus + label,
    .fl-group input:not(:placeholder-shown) + label,
    .fl-group select:focus + label,
    .fl-group select:not([value=""]) + label,
    .fl-group textarea:focus + label,
    .fl-group textarea:not(:placeholder-shown) + label,
    .fl-group.active label{top:0;font-size:.65rem;font-weight:600;letter-spacing:.12em;color:var(--gold);background:var(--bg-card);padding:0 .4rem}
    .fl-group input::placeholder,.fl-group textarea::placeholder{color:transparent}
    .fl-group select option{background:var(--bg-card);color:var(--text)}
    .fl-input-icon{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:.9rem;pointer-events:none}
    .fl-group textarea ~ .fl-input-icon{top:1.2rem;transform:none}
    .fl-group.valid input,.fl-group.valid select{border-color:var(--emerald)}
    .fl-group.valid .fl-input-icon,.fl-group.valid label{color:var(--emerald)}
    .fl-group.error input,.fl-group.error select{border-color:var(--rose)}
    .fl-group.error label{color:var(--rose)}
    .field-error{font-size:.68rem;color:var(--rose);margin-top:.3rem;display:none}
    .fl-group.error .field-error{display:block}
    .fl-check-icon{position:absolute;right:1rem;top:50%;transform:translateY(-50%);color:var(--emerald);font-size:.9rem;display:none}
    .fl-group.valid .fl-check-icon{display:block}

    /* ── PLAN CARDS ── */
    .plan-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem}
    .plan-card{background:rgba(255,255,255,.03);border:1.5px solid var(--border-2);border-radius:12px;padding:1.5rem;cursor:pointer;transition:all .35s;position:relative;overflow:hidden}
    .plan-card::before{content:'';position:absolute;inset:0;background:transparent;transition:background .35s}
    .plan-card:hover{border-color:var(--gold);transform:translateY(-2px)}
    .plan-card.selected{border-color:var(--gold);background:var(--gold-dim);box-shadow:0 8px 32px rgba(201,169,110,.2)}
    .plan-card.selected::before{background:radial-gradient(circle at top right,rgba(201,169,110,.08),transparent 60%)}
    .plan-check{position:absolute;top:.8rem;right:.8rem;width:22px;height:22px;border-radius:50%;border:1.5px solid var(--border-2);display:flex;align-items:center;justify-content:center;transition:all .3s;font-size:.7rem;color:transparent}
    .plan-card.selected .plan-check{background:var(--gold);border-color:var(--gold);color:#1a1400}
    .plan-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:1rem}
    .plan-name{font-family:var(--ff-display);font-size:1.3rem;font-weight:400;color:var(--text);margin-bottom:.2rem}
    .plan-price{font-family:var(--ff-display);font-size:1.6rem;font-weight:400;margin-bottom:.8rem}
    .plan-price small{font-family:var(--ff-body);font-size:.7rem;color:var(--text-3)}
    .plan-features{list-style:none;display:flex;flex-direction:column;gap:.4rem}
    .plan-features li{font-size:.75rem;color:var(--text-2);display:flex;align-items:center;gap:.5rem}
    .plan-features li i{color:var(--emerald);font-size:.75rem}
    .plan-features li.no i{color:var(--text-3)}
    .plan-features li.no{color:var(--text-3)}

    /* ── BRANDING UPLOAD ── */
    .upload-zone{border:1.5px dashed var(--border-2);border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;transition:all .35s;position:relative;overflow:hidden}
    .upload-zone:hover,.upload-zone.dragover{border-color:var(--gold);background:rgba(201,169,110,.05)}
    .upload-zone input{position:absolute;inset:0;opacity:0;cursor:pointer}
    .upload-icon{width:52px;height:52px;border-radius:12px;background:var(--gold-dim);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:var(--gold);margin:0 auto 1rem}
    .upload-title{font-size:.85rem;font-weight:500;color:var(--text);margin-bottom:.3rem}
    .upload-sub{font-size:.72rem;color:var(--text-3)}

    /* ── COLOR PICKER ROW ── */
    .color-row{display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem}
    .color-swatch{width:36px;height:36px;border-radius:8px;cursor:pointer;border:2px solid transparent;transition:all .25s;position:relative}
    .color-swatch.selected{border-color:white;transform:scale(1.1)}
    .color-swatch.selected::after{content:'✓';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:white;font-size:.8rem;font-weight:700}

    /* ── TOGGLE SWITCH ── */
    .toggle-row{display:flex;align-items:center;justify-content:space-between;padding:.9rem 0;border-bottom:1px solid var(--border)}
    .toggle-row:last-child{border-bottom:none}
    .toggle-info-title{font-size:.82rem;font-weight:400;color:var(--text)}
    .toggle-info-sub{font-size:.68rem;color:var(--text-3);margin-top:.1rem}
    .toggle-switch{width:40px;height:22px;background:rgba(255,255,255,.1);border-radius:11px;cursor:pointer;position:relative;transition:background .3s;flex-shrink:0}
    .toggle-switch.on{background:var(--emerald)}
    .toggle-switch::after{content:'';position:absolute;width:16px;height:16px;border-radius:50%;background:white;top:3px;left:3px;transition:transform .3s}
    .toggle-switch.on::after{transform:translateX(18px)}

    /* ── REVIEW BLOCK ── */
    .review-block{background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:10px;padding:1.2rem;margin-bottom:1rem}
    .review-label{font-size:.6rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--text-3);margin-bottom:.8rem}
    .review-row{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid rgba(255,255,255,.03);font-size:.82rem}
    .review-row:last-child{border-bottom:none}
    .review-key{color:var(--text-3)}
    .review-val{color:var(--text);font-weight:400;text-align:right}

    /* ── SUCCESS OVERLAY ── */
    .success-overlay{display:none;position:fixed;inset:0;background:rgba(10,10,12,.95);z-index:999;align-items:center;justify-content:center;flex-direction:column;text-align:center;padding:2rem}
    .success-overlay.show{display:flex;animation:fadeIn .5s ease}
    .success-circle{width:80px;height:80px;border-radius:50%;background:var(--emerald-dim);border:2px solid var(--emerald);display:flex;align-items:center;justify-content:center;font-size:2rem;color:var(--emerald);margin:0 auto 1.5rem;animation:popIn .6s .2s cubic-bezier(.34,1.56,.64,1) both}
    @keyframes popIn{from{transform:scale(0)}to{transform:scale(1)}}
    @keyframes fadeIn{from{opacity:0}to{opacity:1}}

    /* ── PROGRESS BAR ── */
    .form-progress{height:3px;background:var(--border-2);border-radius:2px;margin-bottom:2rem;overflow:hidden}
    .form-progress-fill{height:100%;background:linear-gradient(90deg,var(--teal),var(--gold));border-radius:2px;transition:width .6s cubic-bezier(.22,1,.36,1)}

    /* ── MISC ── */
    .divider-label{display:flex;align-items:center;gap:.8rem;margin:1.5rem 0;color:var(--text-3);font-size:.68rem;letter-spacing:.15em;text-transform:uppercase}
    .divider-label::before,.divider-label::after{content:'';flex:1;height:1px;background:var(--border)}
    .fade-in-up{animation:fadeInUp .7s ease both}
    .stagger-1{animation-delay:.05s}.stagger-2{animation-delay:.1s}.stagger-3{animation-delay:.15s}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
    .sidebar-toggle{display:none;background:none;border:none;color:var(--text);font-size:1.2rem;cursor:pointer}
    @media(max-width:992px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:none}.main-wrap{margin-left:0}.sidebar-toggle{display:flex}.page-content{padding:1.2rem}}
    @media(max-width:576px){.plan-grid{grid-template-columns:1fr}.stepper-wrap{gap:.5rem}}
  </style>
</head>
<body>

<!-- SIDEBAR -->
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
      <div style="flex:1;min-width:0"><div style="font-size:.78rem;font-weight:500;color:var(--text)">Admin Rashid</div><div style="font-size:.62rem;color:var(--text-3)">Super Admin</div></div>
      <i class="bi bi-three-dots-vertical" style="color:var(--text-3);font-size:.85rem"></i>
    </div>
  </div>
</aside>

<!-- MAIN -->
<div class="main-wrap">
  <div class="topbar">
    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
    <div>
      <div style="font-size:.7rem;color:var(--text-3);margin-bottom:.2rem">
        <a href="tenants.html" style="color:var(--text-3);text-decoration:none">Tenants</a>
        <i class="bi bi-chevron-right" style="font-size:.55rem;margin:0 .4rem"></i>
        <span style="color:var(--text-2)">Create New Tenant</span>
      </div>
      <div class="topbar-title">Onboard New Parlour</div>
    </div>
    <div class="topbar-actions">
      <div class="icon-btn"><i class="bi bi-bell"></i><span class="notif-dot"></span></div>
      <a href="tenants.html" class="btn-ghost"><i class="bi bi-x-lg"></i> Cancel</a>
    </div>
  </div>

  <div class="page-content">
    <div class="row justify-content-center">
      <div class="col-xl-9 col-lg-11">

        <!-- PROGRESS BAR -->
        <div class="form-progress fade-in-up">
          <div class="form-progress-fill" id="progressFill" style="width:25%"></div>
        </div>

        <!-- STEPPER -->
        <div class="stepper-wrap fade-in-up stagger-1">
          <div class="step active" id="step-dot-1" onclick="goTo(1)">
            <div class="step-circle" id="step-circle-1">1</div>
            <div class="step-label">Business Info</div>
          </div>
          <div class="step" id="step-dot-2" onclick="goTo(2)">
            <div class="step-circle" id="step-circle-2">2</div>
            <div class="step-label">Choose Plan</div>
          </div>
          <div class="step" id="step-dot-3" onclick="goTo(3)">
            <div class="step-circle" id="step-circle-3">3</div>
            <div class="step-label">Branding</div>
          </div>
          <div class="step" id="step-dot-4" onclick="goTo(4)">
            <div class="step-circle" id="step-circle-4">4</div>
            <div class="step-label">Review & Launch</div>
          </div>
        </div>

        <!-- ══ STEP 1: BUSINESS INFO ══ -->
        <div class="form-panel active" id="panel-1">
          <div class="form-card fade-in-up stagger-2">
            <div class="form-card-title">Parlour Business Information</div>
            <div class="form-card-sub">Enter the core details for the new salon tenant account</div>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="fl-group has-icon valid">
                  <i class="bi bi-buildings fl-input-icon"></i>
                  <input type="text" id="salonName" placeholder="x" value="Aura Glow Studio" />
                  <label>Salon / Parlour Name</label>
                  <i class="bi bi-check-circle-fill fl-check-icon"></i>
                </div>
              </div>
              <div class="col-md-6">
                <div class="fl-group has-icon" id="slugGroup">
                  <i class="bi bi-link-45deg fl-input-icon"></i>
                  <input type="text" id="slugInput" placeholder="x" />
                  <label>Subdomain Slug</label>
                  <i class="bi bi-check-circle-fill fl-check-icon"></i>
                  <div class="field-error">Slug already taken. Try another.</div>
                </div>
                <div style="font-size:.68rem;color:var(--text-3);margin-top:-.9rem;margin-bottom:.8rem;padding-left:.2rem">
                  Preview: <span id="slugPreview" style="color:var(--gold)">aura-glow.lumiere.app</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="fl-group has-icon">
                  <i class="bi bi-person-fill fl-input-icon"></i>
                  <input type="text" placeholder="x" />
                  <label>Owner Full Name</label>
                  <i class="bi bi-check-circle-fill fl-check-icon"></i>
                </div>
              </div>
              <div class="col-md-6">
                <div class="fl-group has-icon">
                  <i class="bi bi-envelope-fill fl-input-icon"></i>
                  <input type="email" placeholder="x" />
                  <label>Owner Email Address</label>
                  <i class="bi bi-check-circle-fill fl-check-icon"></i>
                </div>
              </div>
              <div class="col-md-6">
                <div class="fl-group has-icon">
                  <i class="bi bi-telephone-fill fl-input-icon"></i>
                  <input type="tel" placeholder="x" />
                  <label>Mobile / WhatsApp Number</label>
                  <i class="bi bi-check-circle-fill fl-check-icon"></i>
                </div>
              </div>
              <div class="col-md-6">
                <div class="fl-group has-icon active">
                  <i class="bi bi-geo-alt-fill fl-input-icon"></i>
                  <select>
                    <option value="" disabled selected></option>
                    <option>Mumbai</option><option>Delhi</option><option>Bangalore</option>
                    <option>Hyderabad</option><option>Pune</option><option>Chennai</option>
                    <option>Kolkata</option><option>Jaipur</option><option>Ahmedabad</option><option>Other</option>
                  </select>
                  <label>City</label>
                </div>
              </div>
              <div class="col-12">
                <div class="fl-group has-icon">
                  <i class="bi bi-pin-map-fill fl-input-icon" style="top:1.2rem;transform:none"></i>
                  <textarea placeholder="x" style="padding-left:2.8rem"></textarea>
                  <label>Full Business Address</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="fl-group has-icon">
                  <i class="bi bi-file-text fl-input-icon"></i>
                  <input type="text" placeholder="x" />
                  <label>GST Number (optional)</label>
                  <i class="bi bi-check-circle-fill fl-check-icon"></i>
                </div>
              </div>
              <div class="col-md-6">
                <div class="fl-group has-icon active">
                  <i class="bi bi-shop fl-input-icon"></i>
                  <select>
                    <option value="" disabled selected></option>
                    <option>Beauty Parlour</option><option>Hair Salon</option><option>Unisex Salon</option>
                    <option>Spa & Wellness</option><option>Nail Studio</option><option>Bridal Studio</option>
                  </select>
                  <label>Business Type</label>
                </div>
              </div>
            </div>
          </div>

          <div class="form-card fade-in-up stagger-3">
            <div class="form-card-title">Admin Account Setup</div>
            <div class="form-card-sub">Login credentials for the tenant admin panel</div>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="fl-group has-icon">
                  <i class="bi bi-person-badge fl-input-icon"></i>
                  <input type="text" placeholder="x" />
                  <label>Admin Username</label>
                  <i class="bi bi-check-circle-fill fl-check-icon"></i>
                </div>
              </div>
              <div class="col-md-6">
                <div class="fl-group has-icon">
                  <i class="bi bi-lock-fill fl-input-icon"></i>
                  <input type="password" placeholder="x" />
                  <label>Temporary Password</label>
                  <i class="bi bi-check-circle-fill fl-check-icon"></i>
                </div>
              </div>
              <div class="col-12">
                <div style="display:flex;align-items:center;gap:.8rem;padding:.8rem;background:var(--gold-dim);border:1px solid rgba(201,169,110,.2);border-radius:8px">
                  <i class="bi bi-info-circle-fill" style="color:var(--gold);flex-shrink:0"></i>
                  <span style="font-size:.75rem;color:var(--text-2)">Tenant will receive login credentials via email. They must change their password on first login.</span>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end">
            <button class="btn-gold" onclick="goTo(2)">Next: Choose Plan <i class="bi bi-arrow-right"></i></button>
          </div>
        </div>

        <!-- ══ STEP 2: PLAN SELECTION ══ -->
        <div class="form-panel" id="panel-2">
          <div class="form-card fade-in-up">
            <div class="form-card-title">Select Subscription Plan</div>
            <div class="form-card-sub">Choose the right tier for this parlour. Plans can be upgraded anytime.</div>

            <div class="plan-grid">
              <!-- STARTER -->
              <div class="plan-card" onclick="selectPlan(this,'Starter')">
                <div class="plan-check"><i class="bi bi-check"></i></div>
                <div class="plan-icon" style="background:var(--teal-dim);color:var(--teal-light)"><i class="bi bi-rocket-takeoff"></i></div>
                <div class="plan-name">Starter</div>
                <div class="plan-price" style="color:var(--teal-light)">₹2,499 <small>/ month</small></div>
                <ul class="plan-features">
                  <li><i class="bi bi-check-circle-fill"></i> Up to 5 staff</li>
                  <li><i class="bi bi-check-circle-fill"></i> 500 appointments/mo</li>
                  <li><i class="bi bi-check-circle-fill"></i> Email reminders</li>
                  <li><i class="bi bi-check-circle-fill"></i> Basic analytics</li>
                  <li class="no"><i class="bi bi-x-circle-fill"></i> WhatsApp reminders</li>
                  <li class="no"><i class="bi bi-x-circle-fill"></i> Multi-branch</li>
                  <li class="no"><i class="bi bi-x-circle-fill"></i> Custom domain</li>
                </ul>
              </div>

              <!-- PRO -->
              <div class="plan-card selected" onclick="selectPlan(this,'Pro')">
                <div class="plan-check"><i class="bi bi-check"></i></div>
                <div style="position:absolute;top:.8rem;left:50%;transform:translateX(-50%);background:var(--gold);color:#1a1400;font-size:.58rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;padding:.2rem .6rem;border-radius:20px;white-space:nowrap">Most Popular</div>
                <div class="plan-icon" style="background:var(--purple-dim);color:#a78bfa;margin-top:1.5rem"><i class="bi bi-stars"></i></div>
                <div class="plan-name">Pro</div>
                <div class="plan-price" style="color:#a78bfa">₹5,999 <small>/ month</small></div>
                <ul class="plan-features">
                  <li><i class="bi bi-check-circle-fill"></i> Up to 20 staff</li>
                  <li><i class="bi bi-check-circle-fill"></i> Unlimited appointments</li>
                  <li><i class="bi bi-check-circle-fill"></i> WhatsApp reminders</li>
                  <li><i class="bi bi-check-circle-fill"></i> Advanced analytics</li>
                  <li><i class="bi bi-check-circle-fill"></i> Inventory alerts</li>
                  <li><i class="bi bi-check-circle-fill"></i> 2 branches</li>
                  <li class="no"><i class="bi bi-x-circle-fill"></i> Custom domain</li>
                </ul>
              </div>

              <!-- ENTERPRISE -->
              <div class="plan-card" onclick="selectPlan(this,'Enterprise')">
                <div class="plan-check"><i class="bi bi-check"></i></div>
                <div class="plan-icon" style="background:var(--gold-dim);color:var(--gold)"><i class="bi bi-gem"></i></div>
                <div class="plan-name">Enterprise</div>
                <div class="plan-price" style="color:var(--gold)">₹12,000 <small>/ month</small></div>
                <ul class="plan-features">
                  <li><i class="bi bi-check-circle-fill"></i> Unlimited staff</li>
                  <li><i class="bi bi-check-circle-fill"></i> Unlimited everything</li>
                  <li><i class="bi bi-check-circle-fill"></i> WA + Email reminders</li>
                  <li><i class="bi bi-check-circle-fill"></i> Full analytics suite</li>
                  <li><i class="bi bi-check-circle-fill"></i> Multi-branch (unlimited)</li>
                  <li><i class="bi bi-check-circle-fill"></i> Custom domain</li>
                  <li><i class="bi bi-check-circle-fill"></i> Priority support</li>
                </ul>
              </div>
            </div>

            <!-- BILLING CYCLE -->
            <div class="divider-label">Billing Cycle</div>
            <div class="row g-3">
              <div class="col-md-4">
                <div class="plan-card selected" id="bill-monthly" onclick="selectBilling('monthly')" style="padding:1rem 1.2rem;flex-direction:row;display:flex;align-items:center;gap:.8rem">
                  <div class="plan-check"><i class="bi bi-check"></i></div>
                  <div>
                    <div style="font-size:.82rem;font-weight:500;color:var(--text)">Monthly</div>
                    <div style="font-size:.68rem;color:var(--text-3)">Pay month to month</div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="plan-card" id="bill-quarterly" onclick="selectBilling('quarterly')" style="padding:1rem 1.2rem;display:flex;align-items:center;gap:.8rem">
                  <div class="plan-check"><i class="bi bi-check"></i></div>
                  <div>
                    <div style="font-size:.82rem;font-weight:500;color:var(--text)">Quarterly <span style="background:var(--teal-dim);color:var(--teal-light);font-size:.6rem;padding:.15rem .4rem;border-radius:4px;margin-left:.3rem">-10%</span></div>
                    <div style="font-size:.68rem;color:var(--text-3)">Every 3 months</div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="plan-card" id="bill-annual" onclick="selectBilling('annual')" style="padding:1rem 1.2rem;display:flex;align-items:center;gap:.8rem">
                  <div class="plan-check"><i class="bi bi-check"></i></div>
                  <div>
                    <div style="font-size:.82rem;font-weight:500;color:var(--text)">Annual <span style="background:var(--gold-dim);color:var(--gold);font-size:.6rem;padding:.15rem .4rem;border-radius:4px;margin-left:.3rem">-20%</span></div>
                    <div style="font-size:.68rem;color:var(--text-3)">Best value</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TRIAL TOGGLE -->
            <div class="divider-label">Trial Period</div>
            <div class="toggle-row">
              <div>
                <div class="toggle-info-title">Enable Free Trial</div>
                <div class="toggle-info-sub">Give the tenant a complimentary 14-day trial before billing starts</div>
              </div>
              <div class="toggle-switch on" id="trialToggle" onclick="this.classList.toggle('on')"></div>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-info-title">Send Invoice via Email</div>
                <div class="toggle-info-sub">Auto-send billing invoices to owner email on each cycle</div>
              </div>
              <div class="toggle-switch on" onclick="this.classList.toggle('on')"></div>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <button class="btn-ghost" onclick="goTo(1)"><i class="bi bi-arrow-left"></i> Back</button>
            <button class="btn-gold" onclick="goTo(3)">Next: Branding <i class="bi bi-arrow-right"></i></button>
          </div>
        </div>

        <!-- ══ STEP 3: BRANDING ══ -->
        <div class="form-panel" id="panel-3">
          <div class="form-card fade-in-up">
            <div class="form-card-title">Business Branding</div>
            <div class="form-card-sub">Customize the salon's look and feel on the platform</div>

            <!-- LOGO UPLOAD -->
            <div style="margin-bottom:1.5rem">
              <div style="font-size:.68rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--text-3);margin-bottom:.8rem">Salon Logo</div>
              <div class="upload-zone" id="logoZone">
                <input type="file" accept="image/*" onchange="previewLogo(this)" />
                <div class="upload-icon"><i class="bi bi-image"></i></div>
                <div class="upload-title">Drop logo here or click to browse</div>
                <div class="upload-sub">PNG, JPG, SVG up to 5MB · Recommended 400×400px</div>
              </div>
            </div>

            <!-- COVER UPLOAD -->
            <div style="margin-bottom:1.5rem">
              <div style="font-size:.68rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--text-3);margin-bottom:.8rem">Cover / Banner Image</div>
              <div class="upload-zone">
                <input type="file" accept="image/*" />
                <div class="upload-icon"><i class="bi bi-panorama"></i></div>
                <div class="upload-title">Drop banner here or click to browse</div>
                <div class="upload-sub">PNG, JPG up to 10MB · Recommended 1200×400px</div>
              </div>
            </div>

            <!-- BRAND COLORS -->
            <div style="margin-bottom:1.5rem">
              <div style="font-size:.68rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--text-3);margin-bottom:.8rem">Primary Brand Color</div>
              <div class="color-row" id="colorRow">
                <div class="color-swatch selected" style="background:#c9a96e" onclick="selectColor(this)"></div>
                <div class="color-swatch" style="background:#3a9e8d" onclick="selectColor(this)"></div>
                <div class="color-swatch" style="background:#a78bfa" onclick="selectColor(this)"></div>
                <div class="color-swatch" style="background:#f43f5e" onclick="selectColor(this)"></div>
                <div class="color-swatch" style="background:#10b981" onclick="selectColor(this)"></div>
                <div class="color-swatch" style="background:#60a5fa" onclick="selectColor(this)"></div>
                <div class="color-swatch" style="background:#f97316" onclick="selectColor(this)"></div>
                <div class="color-swatch" style="background:#e879f9" onclick="selectColor(this)"></div>
                <div class="color-swatch" style="background:#1e293b;border:1px solid var(--border-2)" onclick="selectColor(this)"></div>
              </div>
              <div style="display:flex;align-items:center;gap:.8rem;margin-top:.8rem">
                <span style="font-size:.72rem;color:var(--text-3)">Custom hex:</span>
                <div class="fl-group" style="margin:0;flex:1;max-width:160px">
                  <input type="text" placeholder="#c9a96e" style="padding:.5rem .8rem;font-family:monospace;font-size:.8rem" />
                </div>
              </div>
            </div>

            <!-- ADDITIONAL SETTINGS -->
            <div class="divider-label">Platform Settings</div>
            <div class="toggle-row">
              <div><div class="toggle-info-title">WhatsApp Reminders</div><div class="toggle-info-sub">Enable automated appointment reminders via WhatsApp Business</div></div>
              <div class="toggle-switch on" onclick="this.classList.toggle('on')"></div>
            </div>
            <div class="toggle-row">
              <div><div class="toggle-info-title">Email Notifications</div><div class="toggle-info-sub">Send booking confirmations & reminders via email</div></div>
              <div class="toggle-switch on" onclick="this.classList.toggle('on')"></div>
            </div>
            <div class="toggle-row">
              <div><div class="toggle-info-title">Low Stock Alerts</div><div class="toggle-info-sub">Alert salon owner when inventory falls below threshold</div></div>
              <div class="toggle-switch on" onclick="this.classList.toggle('on')"></div>
            </div>
            <div class="toggle-row">
              <div><div class="toggle-info-title">Online Booking Portal</div><div class="toggle-info-sub">Enable public booking page for this tenant</div></div>
              <div class="toggle-switch on" onclick="this.classList.toggle('on')"></div>
            </div>
            <div class="toggle-row">
              <div><div class="toggle-info-title">Customer Reviews</div><div class="toggle-info-sub">Allow customers to leave reviews after appointments</div></div>
              <div class="toggle-switch" onclick="this.classList.toggle('on')"></div>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <button class="btn-ghost" onclick="goTo(2)"><i class="bi bi-arrow-left"></i> Back</button>
            <button class="btn-gold" onclick="goTo(4)">Review & Launch <i class="bi bi-arrow-right"></i></button>
          </div>
        </div>

        <!-- ══ STEP 4: REVIEW & LAUNCH ══ -->
        <div class="form-panel" id="panel-4">
          <div class="form-card fade-in-up">
            <div class="form-card-title">Review & Confirm</div>
            <div class="form-card-sub">Please verify all details before launching the tenant account</div>

            <div class="review-block">
              <div class="review-label">Business Information</div>
              <div class="review-row"><span class="review-key">Salon Name</span><span class="review-val" style="color:var(--gold)">Aura Glow Studio</span></div>
              <div class="review-row"><span class="review-key">Subdomain</span><span class="review-val"><span style="font-family:monospace;color:var(--teal-light)">aura-glow.lumiere.app</span></span></div>
              <div class="review-row"><span class="review-key">Owner</span><span class="review-val">Sunita Sharma</span></div>
              <div class="review-row"><span class="review-key">Email</span><span class="review-val">sunita@auraglow.in</span></div>
              <div class="review-row"><span class="review-key">Phone</span><span class="review-val">+91 99887 76655</span></div>
              <div class="review-row"><span class="review-key">City</span><span class="review-val">Jaipur</span></div>
              <div class="review-row"><span class="review-key">Type</span><span class="review-val">Beauty Parlour</span></div>
            </div>

            <div class="review-block">
              <div class="review-label">Subscription Details</div>
              <div class="review-row"><span class="review-key">Selected Plan</span><span class="review-val"><span style="background:var(--purple-dim);color:#a78bfa;padding:.2rem .6rem;border-radius:20px;font-size:.72rem;font-weight:600">Pro</span></span></div>
              <div class="review-row"><span class="review-key">Billing Cycle</span><span class="review-val">Monthly</span></div>
              <div class="review-row"><span class="review-key">Amount</span><span class="review-val" style="color:var(--emerald);font-weight:500">₹5,999 / month</span></div>
              <div class="review-row"><span class="review-key">Free Trial</span><span class="review-val" style="color:var(--emerald)"><i class="bi bi-check-circle-fill"></i> 14 days enabled</span></div>
              <div class="review-row"><span class="review-key">First Billing</span><span class="review-val">8 Jun 2026</span></div>
            </div>

            <div class="review-block">
              <div class="review-label">Features & Integrations</div>
              <div class="review-row"><span class="review-key">WhatsApp Reminders</span><span class="review-val" style="color:var(--emerald)"><i class="bi bi-check-circle-fill"></i> Enabled</span></div>
              <div class="review-row"><span class="review-key">Email Notifications</span><span class="review-val" style="color:var(--emerald)"><i class="bi bi-check-circle-fill"></i> Enabled</span></div>
              <div class="review-row"><span class="review-key">Low Stock Alerts</span><span class="review-val" style="color:var(--emerald)"><i class="bi bi-check-circle-fill"></i> Enabled</span></div>
              <div class="review-row"><span class="review-key">Online Booking Portal</span><span class="review-val" style="color:var(--emerald)"><i class="bi bi-check-circle-fill"></i> Enabled</span></div>
              <div class="review-row"><span class="review-key">Brand Color</span><span class="review-val"><span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:#c9a96e;vertical-align:middle;margin-right:.4rem"></span>#c9a96e</span></div>
            </div>

            <!-- WHAT HAPPENS NEXT -->
            <div style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.15);border-radius:10px;padding:1.2rem;margin-top:1rem">
              <div style="font-size:.72rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--emerald);margin-bottom:.8rem"><i class="bi bi-lightning-fill" style="margin-right:.4rem"></i>What happens next</div>
              <div style="display:flex;flex-direction:column;gap:.5rem">
                <div style="display:flex;align-items:center;gap:.7rem;font-size:.78rem;color:var(--text-2)"><div style="width:22px;height:22px;border-radius:50%;background:var(--emerald-dim);color:var(--emerald);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;flex-shrink:0">1</div>Tenant DB record created with unique <code style="background:rgba(255,255,255,.08);padding:.1rem .3rem;border-radius:3px;font-size:.72rem">tenant_id</code></div>
                <div style="display:flex;align-items:center;gap:.7rem;font-size:.78rem;color:var(--text-2)"><div style="width:22px;height:22px;border-radius:50%;background:var(--emerald-dim);color:var(--emerald);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;flex-shrink:0">2</div>Welcome email dispatched via queue to owner inbox</div>
                <div style="display:flex;align-items:center;gap:.7rem;font-size:.78rem;color:var(--text-2)"><div style="width:22px;height:22px;border-radius:50%;background:var(--emerald-dim);color:var(--emerald);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;flex-shrink:0">3</div>Subdomain <span style="color:var(--teal-light);font-family:monospace;font-size:.75rem">aura-glow.lumiere.app</span> goes live instantly</div>
                <div style="display:flex;align-items:center;gap:.7rem;font-size:.78rem;color:var(--text-2)"><div style="width:22px;height:22px;border-radius:50%;background:var(--emerald-dim);color:var(--emerald);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;flex-shrink:0">4</div>Scheduler begins monitoring subscription & reminder jobs</div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <button class="btn-ghost" onclick="goTo(3)"><i class="bi bi-arrow-left"></i> Back</button>
            <div style="display:flex;align-items:center;gap:.8rem">
              <span style="font-size:.72rem;color:var(--text-3)">All systems ready</span>
              <div style="width:8px;height:8px;border-radius:50%;background:var(--emerald);box-shadow:0 0 8px var(--emerald)"></div>
              <button class="btn-gold" onclick="launchTenant()" style="padding:.75rem 2rem;font-size:.8rem">
                <i class="bi bi-rocket-takeoff-fill"></i> Launch Tenant
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- SUCCESS OVERLAY -->
<div class="success-overlay" id="successOverlay">
  <div class="success-circle"><i class="bi bi-check-lg"></i></div>
  <div style="font-family:var(--ff-display);font-size:2.5rem;font-weight:400;color:var(--text);margin-bottom:.5rem">Tenant Launched!</div>
  <div style="font-size:.88rem;color:var(--text-3);max-width:380px;line-height:1.8;margin-bottom:2rem;text-align:center">
    <strong style="color:var(--gold)">Aura Glow Studio</strong> has been onboarded successfully.<br>
    Welcome email has been dispatched to Sunita Sharma.
  </div>
  <div style="background:rgba(255,255,255,.04);border:1px solid var(--border-2);border-radius:10px;padding:1rem 1.5rem;margin-bottom:2rem;font-family:monospace;font-size:.8rem;color:var(--teal-light);text-align:left;min-width:280px">
    <div>tenant_id: <span style="color:var(--gold)">TEN-00105</span></div>
    <div>subdomain: <span style="color:var(--gold)">aura-glow.lumiere.app</span></div>
    <div>status: <span style="color:var(--emerald)">active</span></div>
    <div>trial_ends: <span style="color:var(--gold)">2026-06-08</span></div>
  </div>
  <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center">
    <a href="tenant-detail.html" class="btn-gold"><i class="bi bi-eye"></i> View Tenant</a>
    <a href="tenants.html" class="btn-ghost">Back to Tenants</a>
    <a href="create-tenant.html" class="btn-ghost"><i class="bi bi-plus-lg"></i> Add Another</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentStep = 1;
const totalSteps = 4;

function goTo(step) {
  if (step < 1 || step > totalSteps) return;

  // Mark previous steps as done
  for (let i = 1; i < step; i++) {
    const dot = document.getElementById('step-dot-' + i);
    const circ = document.getElementById('step-circle-' + i);
    dot.classList.remove('active');
    dot.classList.add('done');
    circ.innerHTML = '<i class="bi bi-check-lg" style="font-size:.75rem"></i>';
  }
  // Reset future steps
  for (let i = step + 1; i <= totalSteps; i++) {
    const dot = document.getElementById('step-dot-' + i);
    const circ = document.getElementById('step-circle-' + i);
    dot.classList.remove('active', 'done');
    circ.innerHTML = i;
  }
  // Set current
  const curr = document.getElementById('step-dot-' + step);
  const currCirc = document.getElementById('step-circle-' + step);
  curr.classList.add('active');
  curr.classList.remove('done');
  currCirc.innerHTML = step;

  // Show panel
  document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-' + step).classList.add('active');

  // Update progress bar
  document.getElementById('progressFill').style.width = (step / totalSteps * 100) + '%';

  currentStep = step;

  // Scroll to top of content
  document.querySelector('.page-content').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function selectPlan(el, name) {
  document.querySelectorAll('.plan-grid .plan-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
}

function selectBilling(type) {
  ['monthly','quarterly','annual'].forEach(t => {
    document.getElementById('bill-' + t).classList.remove('selected');
  });
  document.getElementById('bill-' + type).classList.add('selected');
}

function selectColor(el) {
  document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
  el.classList.add('selected');
}

function launchTenant() {
  const overlay = document.getElementById('successOverlay');
  overlay.classList.add('show');
}

// Slug auto-generation from salon name
document.getElementById('salonName').addEventListener('input', function() {
  const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  document.getElementById('slugInput').value = slug;
  document.getElementById('slugPreview').textContent = (slug || 'your-salon') + '.lumiere.app';
});

// Floating label activation for selects
document.querySelectorAll('.fl-group select').forEach(sel => {
  sel.addEventListener('change', function() {
    if (this.value) this.closest('.fl-group').classList.add('active');
    else this.closest('.fl-group').classList.remove('active');
  });
});

// Real-time input validation & floating labels
document.querySelectorAll('.fl-group input, .fl-group textarea').forEach(inp => {
  inp.addEventListener('input', function() {
    const group = this.closest('.fl-group');
    if (this.value.length > 0) {
      group.classList.add('active');
      if (this.value.length >= 3) {
        group.classList.add('valid');
        group.classList.remove('error');
      }
    } else {
      group.classList.remove('active', 'valid');
    }
  });
});

// Drag & drop on upload zones
document.querySelectorAll('.upload-zone').forEach(zone => {
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
  zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('dragover'); });
});
</script>
</body>
</html>
