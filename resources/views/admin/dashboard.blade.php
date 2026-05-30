<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LUMIÈRE · Owner Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Jost:wght@200;300;400;500;600&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="_base.css" />
  <style>
    /* ── PAGE SPECIFIC ── */
    .page-hero {
      background: linear-gradient(135deg, rgba(201, 169, 110, 0.06) 0%, transparent 60%);
      border: 1px solid rgba(201, 169, 110, 0.12);
      border-radius: 16px;
      padding: 1.8rem 2rem;
      margin-bottom: 1.8rem;
      position: relative;
      overflow: hidden;
    }

    .page-hero::before {
      content: 'DASHBOARD';
      position: absolute;
      font-family: var(--ff-display);
      font-size: clamp(4rem, 12vw, 9rem);
      font-weight: 300;
      color: rgba(201, 169, 110, 0.04);
      right: -1rem;
      top: 50%;
      transform: translateY(-50%);
      white-space: nowrap;
      pointer-events: none;
      letter-spacing: -0.03em;
    }

    .hero-greeting {
      font-family: var(--ff-display);
      font-size: clamp(1.6rem, 4vw, 2.6rem);
      font-weight: 300;
      color: var(--text);
      line-height: 1.15;
      margin-bottom: 0.4rem;
    }

    .hero-greeting em {
      font-style: italic;
      color: var(--gold);
    }

    /* Queue/Reminder Status strip */
    .status-strip {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      align-items: center;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border-2);
      border-radius: 20px;
      padding: 0.3rem 0.9rem;
      font-size: 0.7rem;
      font-weight: 400;
      color: var(--text-2);
    }

    .status-pill .dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    /* today appt row */
    .appt-row {
      display: flex;
      align-items: center;
      gap: 0.9rem;
      padding: 0.85rem 1.2rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
      transition: background 0.2s;
    }

    .appt-row:hover {
      background: rgba(255, 255, 255, 0.02);
    }

    .appt-row:last-child {
      border-bottom: none;
    }

    .appt-time {
      font-family: var(--ff-display);
      font-size: 1rem;
      font-weight: 400;
      color: var(--gold);
      min-width: 52px;
    }

    .appt-av {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: var(--gold-dim);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--ff-display);
      font-size: 0.85rem;
      color: var(--gold);
      flex-shrink: 0;
    }

    .appt-name {
      font-size: 0.84rem;
      font-weight: 500;
      color: var(--text);
    }

    .appt-service {
      font-size: 0.7rem;
      color: var(--text-3);
      margin-top: 0.1rem;
    }

    /* staff performance */
    .staff-bar-item {
      margin-bottom: 1rem;
    }

    .staff-bar-item:last-child {
      margin-bottom: 0;
    }

    /* Low stock */
    .stock-row {
      display: flex;
      align-items: center;
      gap: 0.9rem;
      padding: 0.75rem 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }

    .stock-row:last-child {
      border-bottom: none;
    }

    .stock-ic {
      width: 32px;
      height: 32px;
      border-radius: 7px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      flex-shrink: 0;
    }
    .kpi-spark{display:block;height:38px;max-height:38px;margin-top:0.6rem;position:relative;overflow:hidden}
    .kpi-spark canvas{position:absolute!important;top:0;left:0;width:100%!important;height:100%!important}
  </style>
</head>

<body>
  <div class="orb orb-gold"></div>
  <div class="orb orb-teal"></div>

  <!-- ═══════════════ SIDEBAR ═══════════════ -->
  <aside class="lm-sidebar" id="sidebar">
    <div class="sidebar-logo-area">
      <div class="logo-gem">L</div>
      <div>
        <div class="logo-wordmark">LUMIÈRE<span>.</span></div>
        <div class="logo-sub">Beauty Studio</div>
      </div>
    </div>

    <div class="sidebar-scroll">
      <div class="nav-grp-label">Overview</div>
      <a href="dashboard.html" class="nav-item active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
      <a href="analytics.html" class="nav-item"><i class="bi bi-graph-up-arrow"></i> Analytics</a>

      <div class="nav-grp-label">Operations</div>
      <a href="appointments-today.html" class="nav-item"><i class="bi bi-calendar-check-fill"></i> Today's Bookings
        <span class="nav-badge nb-gold">12</span></a>
      <a href="appointments.html" class="nav-item"><i class="bi bi-calendar2-week-fill"></i> All Appointments</a>
      <a href="services.html" class="nav-item"><i class="bi bi-scissors"></i> Services</a>
      <a href="staff.html" class="nav-item"><i class="bi bi-people-fill"></i> Staff</a>

      <div class="nav-grp-label">Business</div>
      <a href="inventory.html" class="nav-item"><i class="bi bi-box-seam-fill"></i> Inventory <span
          class="nav-badge nb-red">3</span></a>
      <a href="commissions.html" class="nav-item"><i class="bi bi-cash-stack"></i> Commissions</a>
      <a href="#" class="nav-item"><i class="bi bi-people"></i> Customers</a>
      <a href="#" class="nav-item"><i class="bi bi-star-fill"></i> Reviews</a>

      <div class="nav-grp-label">System</div>
      <a href="#" class="nav-item"><i class="bi bi-bell-fill"></i> Reminders <span
          class="nav-badge nb-green">OK</span></a>
      <a href="#" class="nav-item"><i class="bi bi-gear-fill"></i> Settings</a>
    </div>

    <div class="sidebar-footer">
      <div class="owner-pill">
        <div class="owner-av">PK</div>
        <div style="flex:1;min-width:0">
          <div class="owner-name">Priya Kapoor</div>
          <div class="owner-role">Salon Owner</div>
        </div>
        <i class="bi bi-chevron-up-down" style="color:var(--text-3);font-size:0.75rem"></i>
      </div>
    </div>
  </aside>

  <!-- ═══════════════ MAIN ═══════════════ -->
  <div class="main-wrap">

    <!-- TOPBAR -->
    <div class="lm-topbar">
      <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i
          class="bi bi-list"></i></button>
      <div>
        <div class="topbar-heading">Dashboard</div>
        <div class="topbar-sub">Sunday, 25 May 2026</div>
      </div>
      <div class="topbar-search">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search bookings, customers…" />
      </div>
      <div class="topbar-actions">
        <div class="tb-icon"><i class="bi bi-bell"></i><span class="tb-dot"></span></div>
        <div class="tb-icon"><i class="bi bi-whatsapp" style="color:var(--emerald)"></i></div>
        <a href="appointments.html" class="btn-gold-sm"><i class="bi bi-plus-lg"></i> New Booking</a>
      </div>
    </div>

    <!-- PAGE BODY -->
    <div class="page-body">

      <!-- ── HERO GREETING ── -->
      <div class="page-hero fade-up s1">
        <div class="row align-items-center">
          <div class="col-lg-7">
            <div class="hero-greeting">Good Morning,<br><em>Priya</em> — Your Studio Shines Today.</div>
            <div style="font-size:0.82rem;color:var(--text-2);margin-top:0.6rem;margin-bottom:1.2rem">Glamour Studio ·
              Connaught Place, Delhi &nbsp;·&nbsp; <span style="color:var(--emerald)">12 appointments</span> scheduled
              today</div>
            <div class="status-strip">
              <div class="status-pill"><span class="dot" style="background:var(--emerald)"></span>WhatsApp Queue ·
                Running</div>
              <div class="status-pill"><span class="dot" style="background:var(--emerald)"></span>Email Reminders ·
                Active</div>
              <div class="status-pill"><span class="dot" style="background:var(--amber)"></span>3 Low Stock Alerts</div>
              <div class="status-pill"><span class="dot" style="background:var(--emerald)"></span>Scheduler · On Time
              </div>
            </div>
          </div>
          <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
            <div
              style="font-family:var(--ff-display);font-size:0.7rem;letter-spacing:0.2em;color:var(--text-3);text-transform:uppercase;margin-bottom:0.3rem">
              May Revenue</div>
            <div style="font-family:var(--ff-display);font-size:3rem;font-weight:300;color:var(--gold);line-height:1">
              ₹84,200</div>
            <div style="font-size:0.72rem;color:var(--emerald);margin-top:0.3rem"><i class="bi bi-arrow-up-right"></i>
              +18.4% vs last month</div>
          </div>
        </div>
      </div>

      <!-- ── KPI ROW ── -->
      <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3 fade-up s1">
          <div class="card-lux kpi-pad gold-border glow-hover">
            <div class="kpi-label"><span class="live-dot"></span> Today's Bookings</div>
            <div class="kpi-value" style="color:var(--gold)">12</div>
            <span class="kpi-trend trend-up"><i class="bi bi-arrow-up-right"></i> 3 walk-ins</span>
            <div class="kpi-icon-abs" style="background:var(--gold-dim);color:var(--gold)"><i
                class="bi bi-calendar-check-fill"></i></div>
            <div class="kpi-spark"><canvas id="s1"></canvas></div>
          </div>
        </div>
        <div class="col-6 col-lg-3 fade-up s2">
          <div class="card-lux kpi-pad" style="border-top:2px solid var(--emerald)">
            <div class="kpi-label">Monthly Revenue</div>
            <div class="kpi-value" style="color:var(--emerald)">₹84.2K</div>
            <span class="kpi-trend trend-up"><i class="bi bi-arrow-up-right"></i> +18.4%</span>
            <div class="kpi-icon-abs" style="background:var(--emerald-dim);color:var(--emerald)"><i
                class="bi bi-currency-rupee"></i></div>
            <div class="kpi-spark"><canvas id="s2"></canvas></div>
          </div>
        </div>
        <div class="col-6 col-lg-3 fade-up s3">
          <div class="card-lux kpi-pad" style="border-top:2px solid var(--purple)">
            <div class="kpi-label">Active Customers</div>
            <div class="kpi-value" style="color:var(--purple)">342</div>
            <span class="kpi-trend trend-up"><i class="bi bi-arrow-up-right"></i> +24 new</span>
            <div class="kpi-icon-abs" style="background:var(--purple-dim);color:var(--purple)"><i
                class="bi bi-people-fill"></i></div>
            <div class="kpi-spark"><canvas id="s3"></canvas></div>
          </div>
        </div>
        <div class="col-6 col-lg-3 fade-up s4">
          <div class="card-lux kpi-pad" style="border-top:2px solid var(--teal-light)">
            <div class="kpi-label">Staff Active</div>
            <div class="kpi-value" style="color:var(--teal-light)">8/10</div>
            <span class="kpi-trend trend-flat"><i class="bi bi-dash"></i> 2 on leave</span>
            <div class="kpi-icon-abs" style="background:var(--teal-dim);color:var(--teal-light)"><i
                class="bi bi-person-badge-fill"></i></div>
            <div class="kpi-spark"><canvas id="s4"></canvas></div>
          </div>
        </div>
      </div>

      <!-- ── CHARTS ROW ── -->
      <div class="row g-3 mb-3">
        <!-- Revenue Chart -->
        <div class="col-lg-8 fade-up s2">
          <div class="card-lux p-4">
            <div class="sec-hdr">
              <div>
                <div class="sec-title">Revenue Overview</div>
                <div class="sec-sub">Monthly earnings — Glamour Studio</div>
              </div>
              <div class="d-flex gap-2">
                <button class="btn-ghost-sm" style="border-color:var(--gold);color:var(--gold);">6M</button>
                <button class="btn-ghost-sm">1Y</button>
              </div>
            </div>
            <div class="chart-box" style="height:220px"><canvas id="revChart"></canvas></div>
          </div>
        </div>

        <!-- Service Popularity -->
        <div class="col-lg-4 fade-up s3">
          <div class="card-lux p-4">
            <div class="sec-hdr">
              <div>
                <div class="sec-title">Top Services</div>
                <div class="sec-sub">By booking count</div>
              </div>
            </div>
            <div class="chart-box" style="height:160px"><canvas id="servChart"></canvas></div>
            <div class="mt-3">
              <div class="d-flex justify-content-between mb-2">
                <span style="font-size:0.75rem;color:var(--text-2)"><span
                    style="display:inline-block;width:10px;height:10px;border-radius:2px;background:var(--gold);margin-right:6px"></span>Bridal</span>
                <span style="font-size:0.75rem;color:var(--gold)">38%</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span style="font-size:0.75rem;color:var(--text-2)"><span
                    style="display:inline-block;width:10px;height:10px;border-radius:2px;background:var(--purple);margin-right:6px"></span>Hair</span>
                <span style="font-size:0.75rem;color:var(--purple)">29%</span>
              </div>
              <div class="d-flex justify-content-between">
                <span style="font-size:0.75rem;color:var(--text-2)"><span
                    style="display:inline-block;width:10px;height:10px;border-radius:2px;background:var(--teal-light);margin-right:6px"></span>Facial</span>
                <span style="font-size:0.75rem;color:var(--teal-light)">18%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── MIDDLE ROW ── -->
      <div class="row g-3 mb-3">
        <!-- Today's Appointments -->
        <div class="col-lg-5 fade-up s2">
          <div class="card-lux" style="height:100%">
            <div class="p-4 pb-2">
              <div class="sec-hdr">
                <div>
                  <div class="sec-title">Today's Schedule</div>
                  <div class="sec-sub">12 appointments · 3 walk-ins</div>
                </div>
                <a href="appointments-today.html" class="btn-ghost-sm">View All</a>
              </div>
            </div>
            <div class="appt-row">
              <div class="appt-time">9:00</div>
              <div class="appt-av">SA</div>
              <div style="flex:1">
                <div class="appt-name">Samantha Arora</div>
                <div class="appt-service">Bridal Makeup · Sunita Mehra</div>
              </div>
              <span class="lux-badge lb-green">In Progress</span>
            </div>
            <div class="appt-row">
              <div class="appt-time">10:30</div>
              <div class="appt-av">MK</div>
              <div style="flex:1">
                <div class="appt-name">Meera Khanna</div>
                <div class="appt-service">Hair Color · Priya Kapoor</div>
              </div>
              <span class="lux-badge lb-gold">Confirmed</span>
            </div>
            <div class="appt-row">
              <div class="appt-time">11:00</div>
              <div class="appt-av">RD</div>
              <div style="flex:1">
                <div class="appt-name">Rina Desai</div>
                <div class="appt-service">Luxury Facial · Anita Rawat</div>
              </div>
              <span class="lux-badge lb-gold">Confirmed</span>
            </div>
            <div class="appt-row">
              <div class="appt-time">12:30</div>
              <div class="appt-av">SP</div>
              <div style="flex:1">
                <div class="appt-name">Shruti Patel</div>
                <div class="appt-service">Nail Art · Anita Rawat</div>
              </div>
              <span class="lux-badge lb-amber">Pending</span>
            </div>
            <div class="appt-row">
              <div class="appt-time">2:00</div>
              <div class="appt-av">NK</div>
              <div style="flex:1">
                <div class="appt-name">Nandini Kumar</div>
                <div class="appt-service">Keratin Treatment · Priya</div>
              </div>
              <span class="lux-badge lb-gold">Confirmed</span>
            </div>
            <div class="appt-row">
              <div class="appt-time">4:00</div>
              <div class="appt-av">TG</div>
              <div style="flex:1">
                <div class="appt-name">Tanvi Gupta</div>
                <div class="appt-service">Bridal Package · Full Team</div>
              </div>
              <span class="lux-badge lb-purple">VIP</span>
            </div>
          </div>
        </div>

        <!-- Staff Performance -->
        <div class="col-lg-4 fade-up s3">
          <div class="card-lux p-4" style="height:100%">
            <div class="sec-hdr">
              <div>
                <div class="sec-title">Staff Performance</div>
                <div class="sec-sub">This month's earnings</div>
              </div>
              <a href="staff.html" class="btn-ghost-sm">Details</a>
            </div>
            <div class="staff-bar-item">
              <div class="d-flex justify-content-between mb-1">
                <span style="font-size:0.8rem;color:var(--text-2)">Priya Kapoor</span>
                <span style="font-size:0.8rem;color:var(--gold)">₹24,800</span>
              </div>
              <div class="prog-track">
                <div class="prog-fill" style="width:92%;background:var(--gold)"></div>
              </div>
            </div>
            <div class="staff-bar-item">
              <div class="d-flex justify-content-between mb-1">
                <span style="font-size:0.8rem;color:var(--text-2)">Sunita Mehra</span>
                <span style="font-size:0.8rem;color:var(--purple)">₹18,200</span>
              </div>
              <div class="prog-track">
                <div class="prog-fill" style="width:68%;background:var(--purple)"></div>
              </div>
            </div>
            <div class="staff-bar-item">
              <div class="d-flex justify-content-between mb-1">
                <span style="font-size:0.8rem;color:var(--text-2)">Anita Rawat</span>
                <span style="font-size:0.8rem;color:var(--teal-light)">₹16,400</span>
              </div>
              <div class="prog-track">
                <div class="prog-fill" style="width:61%;background:var(--teal-light)"></div>
              </div>
            </div>
            <div class="staff-bar-item">
              <div class="d-flex justify-content-between mb-1">
                <span style="font-size:0.8rem;color:var(--text-2)">Rekha Joshi</span>
                <span style="font-size:0.8rem;color:var(--emerald)">₹12,100</span>
              </div>
              <div class="prog-track">
                <div class="prog-fill" style="width:45%;background:var(--emerald)"></div>
              </div>
            </div>
            <div class="staff-bar-item">
              <div class="d-flex justify-content-between mb-1">
                <span style="font-size:0.8rem;color:var(--text-2)">Kavya Singh</span>
                <span style="font-size:0.8rem;color:var(--rose)">₹9,800</span>
              </div>
              <div class="prog-track">
                <div class="prog-fill" style="width:37%;background:var(--rose)"></div>
              </div>
            </div>

            <div style="border-top:1px solid var(--border);margin-top:1.2rem;padding-top:1.2rem">
              <div class="d-flex justify-content-between">
                <span style="font-size:0.72rem;color:var(--text-3)">Total commissions due</span>
                <span style="font-family:var(--ff-display);font-size:1.2rem;color:var(--gold)">₹16,840</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Low Stock + Commission -->
        <div class="col-lg-3 fade-up s4">
          <div class="card-lux p-4 mb-3">
            <div class="sec-hdr mb-2">
              <div>
                <div class="sec-title" style="color:var(--amber)"><i class="bi bi-exclamation-triangle-fill"
                    style="font-size:0.9rem;margin-right:0.4rem"></i>Low Stock</div>
              </div>
              <a href="inventory.html" class="btn-ghost-sm" style="font-size:0.62rem">Fix</a>
            </div>
            <div class="stock-row">
              <div class="stock-ic" style="background:var(--rose-dim);color:var(--rose)"><i
                  class="bi bi-droplet-fill"></i></div>
              <div style="flex:1">
                <div style="font-size:0.78rem;font-weight:500;color:var(--text)">Hair Serum</div>
                <div style="font-size:0.65rem;color:var(--rose)">2 left · Min 10</div>
              </div>
            </div>
            <div class="stock-row">
              <div class="stock-ic" style="background:var(--amber-dim);color:var(--amber)"><i
                  class="bi bi-palette-fill"></i></div>
              <div style="flex:1">
                <div style="font-size:0.78rem;font-weight:500;color:var(--text)">Hair Color</div>
                <div style="font-size:0.65rem;color:var(--amber)">5 left · Min 15</div>
              </div>
            </div>
            <div class="stock-row">
              <div class="stock-ic" style="background:var(--rose-dim);color:var(--rose)"><i class="bi bi-stars"></i>
              </div>
              <div style="flex:1">
                <div style="font-size:0.78rem;font-weight:500;color:var(--text)">Keratin</div>
                <div style="font-size:0.65rem;color:var(--rose)">1 left · Min 8</div>
              </div>
            </div>
          </div>

          <div class="card-lux p-4">
            <div class="sec-title mb-3">Commission Summary</div>
            <div style="text-align:center;padding:0.5rem 0 1rem">
              <div style="font-family:var(--ff-display);font-size:2.4rem;font-weight:300;color:var(--gold)">₹16,840
              </div>
              <div
                style="font-size:0.65rem;letter-spacing:0.2em;color:var(--text-3);text-transform:uppercase;margin-top:0.2rem">
                Due this month</div>
            </div>
            <div class="d-flex justify-content-between"
              style="padding-bottom:0.6rem;border-bottom:1px solid var(--border)">
              <span style="font-size:0.72rem;color:var(--text-3)">Paid</span>
              <span style="font-size:0.75rem;color:var(--emerald)">₹8,400</span>
            </div>
            <div class="d-flex justify-content-between pt-2">
              <span style="font-size:0.72rem;color:var(--text-3)">Pending</span>
              <span style="font-size:0.75rem;color:var(--amber)">₹8,440</span>
            </div>
            <a href="commissions.html" class="btn-gold-sm w-100 mt-3 justify-content-center"
              style="font-size:0.65rem">View Commissions</a>
          </div>
        </div>
      </div>

      <!-- ── RECENT BOOKINGS TABLE ── -->
      <div class="row g-3 mb-3">
        <div class="col-12 fade-up s3">
          <div class="card-lux">
            <div class="p-4 pb-0">
              <div class="sec-hdr">
                <div>
                  <div class="sec-title">Recent Bookings</div>
                  <div class="sec-sub">Last 24 hours</div>
                </div>
                <a href="appointments.html" class="btn-ghost-sm">View All <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
            <div style="overflow-x:auto">
              <table class="lux-table">
                <thead>
                  <tr>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Staff</th>
                    <th>Date & Time</th>
                    <th>Amount</th>
                    <th>Reminder</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <div style="font-weight:500;color:var(--text)">Tanvi Gupta</div>
                      <div style="font-size:0.65rem;color:var(--text-3)">+91 98765 11111</div>
                    </td>
                    <td>Bridal Package</td>
                    <td>Priya Kapoor</td>
                    <td>Today · 4:00 PM</td>
                    <td style="color:var(--emerald);font-weight:500">₹8,500</td>
                    <td><span class="lux-badge lb-green"><i class="bi bi-whatsapp"></i> Sent</span></td>
                    <td><span class="lux-badge lb-gold">Confirmed</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div style="font-weight:500;color:var(--text)">Nandini Kumar</div>
                      <div style="font-size:0.65rem;color:var(--text-3)">+91 98765 22222</div>
                    </td>
                    <td>Keratin Treatment</td>
                    <td>Priya Kapoor</td>
                    <td>Today · 2:00 PM</td>
                    <td style="color:var(--emerald);font-weight:500">₹3,200</td>
                    <td><span class="lux-badge lb-green"><i class="bi bi-whatsapp"></i> Sent</span></td>
                    <td><span class="lux-badge lb-gold">Confirmed</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div style="font-weight:500;color:var(--text)">Shruti Patel</div>
                      <div style="font-size:0.65rem;color:var(--text-3)">+91 98765 33333</div>
                    </td>
                    <td>Nail Art</td>
                    <td>Anita Rawat</td>
                    <td>Today · 12:30 PM</td>
                    <td style="color:var(--text-2)">₹1,200</td>
                    <td><span class="lux-badge lb-amber">Pending</span></td>
                    <td><span class="lux-badge lb-amber">Pending</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div style="font-weight:500;color:var(--text)">Meera Khanna</div>
                      <div style="font-size:0.65rem;color:var(--text-3)">+91 98765 44444</div>
                    </td>
                    <td>Hair Color</td>
                    <td>Priya Kapoor</td>
                    <td>Today · 10:30 AM</td>
                    <td style="color:var(--emerald);font-weight:500">₹2,800</td>
                    <td><span class="lux-badge lb-green"><i class="bi bi-envelope-fill"></i> Sent</span></td>
                    <td><span class="lux-badge lb-green">Completed</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div style="font-weight:500;color:var(--text)">Samantha Arora</div>
                      <div style="font-size:0.65rem;color:var(--text-3)">+91 98765 55555</div>
                    </td>
                    <td>Bridal Makeup</td>
                    <td>Sunita Mehra</td>
                    <td>Today · 9:00 AM</td>
                    <td style="color:var(--emerald);font-weight:500">₹6,000</td>
                    <td><span class="lux-badge lb-green"><i class="bi bi-whatsapp"></i> Sent</span></td>
                    <td><span class="lux-badge lb-green">Completed</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /page-body -->
  </div><!-- /main-wrap -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    Chart.defaults.color = 'rgba(255,255,255,0.38)';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';
    Chart.defaults.font.family = "'Jost',sans-serif";
    Chart.defaults.font.weight = '300';
    const gold = '#c9a96e', emerald = '#10b981', purple = '#8b5cf6', teal = '#3a9e8d', rose = '#f43f5e', amber = '#f59e0b';

    // ── SPARKLINES — gradient created dynamically after canvas is sized
    function spark(id, color, data) {
      new Chart(document.getElementById(id), {
        type: 'line',
        data: { labels: data.map((_, i) => i), datasets: [{ data, borderColor: color, borderWidth: 1.5, fill: true,
          backgroundColor: (ctx) => {
            const g = ctx.chart.canvas.getContext('2d').createLinearGradient(0, 0, 0, 38);
            g.addColorStop(0, color + '55'); g.addColorStop(1, color + '00'); return g;
          }, tension: 0.4, pointRadius: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, layout: { padding: 0 },
          animation: { duration: 1200 }, plugins: { legend: { display: false }, tooltip: { enabled: false } },
          scales: { x: { display: false }, y: { display: false } } }
      });
    }
    spark('s1', gold,    [6, 8, 9, 7, 11, 10, 12, 10, 12]);
    spark('s2', emerald, [52, 61, 58, 68, 72, 69, 78, 80, 84.2]);
    spark('s3', purple,  [280, 295, 300, 310, 318, 325, 328, 338, 342]);
    spark('s4', teal,    [7, 8, 9, 8, 9, 10, 8, 9, 8]);

    // ── REVENUE CHART — gradient via chartArea (Chart.js recommended pattern)
    new Chart(document.getElementById('revChart'), {
      type: 'line',
      data: {
        labels: ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May'],
        datasets: [
          { label: 'Revenue (₹K)', data: [52, 61, 58, 68, 72, 84.2], borderColor: gold, borderWidth: 2, fill: true,
            backgroundColor: (ctx) => { const {chartArea, canvas} = ctx.chart; if (!chartArea) return 'transparent'; const g = canvas.getContext('2d').createLinearGradient(0, chartArea.top, 0, chartArea.bottom); g.addColorStop(0, gold + '44'); g.addColorStop(1, gold + '00'); return g; },
            tension: 0.4, pointRadius: 4, pointBackgroundColor: gold, pointBorderColor: '#111116', pointBorderWidth: 2 },
          { label: 'Target', data: [55, 60, 65, 70, 75, 80], borderColor: emerald + '88', borderWidth: 1.5, borderDash: [5, 4], fill: true,
            backgroundColor: (ctx) => { const {chartArea, canvas} = ctx.chart; if (!chartArea) return 'transparent'; const g = canvas.getContext('2d').createLinearGradient(0, chartArea.top, 0, chartArea.bottom); g.addColorStop(0, emerald + '28'); g.addColorStop(1, emerald + '00'); return g; },
            tension: 0.4, pointRadius: 3, pointBackgroundColor: emerald, pointBorderColor: '#111116', pointBorderWidth: 2 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false, animation: { duration: 1600 },
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxHeight: 6, pointStyleWidth: 10 } }, tooltip: { backgroundColor: '#15151b', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1, padding: 10 } },
        scales: { x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.35)' } }, y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.35)', callback: v => '₹' + v + 'K' }, beginAtZero: false } }
      }
    });

    // ── SERVICE DOUGHNUT
    new Chart(document.getElementById('servChart'), {
      type: 'doughnut',
      data: { labels: ['Bridal', 'Hair', 'Facial', 'Nails', 'Body'], datasets: [{ data: [38, 29, 18, 9, 6], backgroundColor: [gold, purple, teal, emerald, rose], borderColor: '#111116', borderWidth: 3, hoverOffset: 6 }] },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '72%', animation: { duration: 1200, animateScale: true },
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#15151b', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1, padding: 10 } }
      }
    });
  </script>
</body>

</html>