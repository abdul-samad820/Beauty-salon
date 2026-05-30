@extends('superadmin.layouts.app')

@section('title', 'Super Dashboard')
@section('page-title', 'Super Dashboard')
@section('page-sub', now()->format('l, d M Y') . ' · All Systems Operational')

@push('head-scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('topbar-actions')
  <a href="{{ route('superadmin.tenants.create') }}" class="btn-gold">
    <i class="bi bi-plus-lg"></i> New Tenant
  </a>
@endsection

@push('styles')
<style>
  .kpi-card { padding:1.5rem; }
  .kpi-label { font-size:0.65rem; font-weight:600; letter-spacing:0.22em; text-transform:uppercase; color:var(--text-3); margin-bottom:0.8rem; display:flex; align-items:center; gap:0.5rem; }
  .kpi-value { font-family:var(--ff-display); font-size:2.4rem; font-weight:400; line-height:1; margin-bottom:0.6rem; }
  .kpi-trend { display:inline-flex; align-items:center; gap:0.3rem; font-size:0.72rem; font-weight:500; padding:0.2rem 0.6rem; border-radius:20px; }
  .kpi-trend.up   { background:var(--emerald-dim); color:var(--emerald); }
  .kpi-trend.down { background:var(--rose-dim); color:var(--rose); }
  .kpi-trend.flat { background:var(--gold-dim); color:var(--gold); }
  .kpi-icon { position:absolute; top:1.4rem; right:1.4rem; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
  .kpi-sparkline { display:block; height:40px; margin-top:1rem; position:relative; }
  .kpi-sparkline canvas { position:absolute !important; top:0; left:0; width:100% !important; height:40px !important; }
  .chart-wrap { position:relative; height:200px; }
  .chart-wrap.tall { height:260px; }
  .dash-table { width:100%; border-collapse:collapse; }
  .dash-table th { font-size:0.6rem; font-weight:600; letter-spacing:0.22em; text-transform:uppercase; color:var(--text-3); padding:0.6rem 1rem; border-bottom:1px solid var(--border); text-align:left; }
  .dash-table td { padding:0.85rem 1rem; font-size:0.82rem; font-weight:300; color:var(--text-2); border-bottom:1px solid rgba(255,255,255,0.03); }
  .dash-table tr:hover td { background:rgba(255,255,255,0.02); color:var(--text); }
  .dash-table tr:last-child td { border-bottom:none; }
  .tenant-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:0.4rem; }
  .dot-green { background:var(--emerald); box-shadow:0 0 8px var(--emerald); }
  .dot-gold  { background:var(--gold);    box-shadow:0 0 8px var(--gold); }
  .dot-red   { background:var(--rose);    box-shadow:0 0 8px var(--rose); }
  .timeline-item { display:flex; gap:1rem; padding-bottom:1.2rem; position:relative; }
  .timeline-item:not(:last-child)::before { content:''; position:absolute; left:15px; top:30px; bottom:0; width:1px; background:var(--border); }
  .timeline-dot { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.75rem; flex-shrink:0; border:1px solid var(--border-2); }
  .timeline-body { flex:1; }
  .timeline-title { font-size:0.82rem; font-weight:400; color:var(--text); }
  .timeline-meta { font-size:0.68rem; color:var(--text-3); margin-top:0.2rem; }
</style>
@endpush

@section('content')

  {{-- ── KPI ROW ── --}}
  <div class="row g-3 mb-3">
    <div class="col-6 col-lg-3 fade-in-up stagger-1">
      <div class="card-glass kpi-card" style="border-top:2px solid var(--gold);">
        <div class="kpi-label"><span class="live-dot"></span> Total Tenants</div>
        <div class="kpi-value" style="color:var(--gold);">{{ $stats['total_tenants'] }}</div>
        <span class="kpi-trend up"><i class="bi bi-arrow-up-right"></i> +{{ $stats['new_this_month'] }} this month</span>
        <div class="kpi-icon" style="background:var(--gold-dim);color:var(--gold);"><i class="bi bi-buildings-fill"></i></div>
        <div class="kpi-sparkline"><canvas id="spark1"></canvas></div>
      </div>
    </div>
    <div class="col-6 col-lg-3 fade-in-up stagger-2">
      <div class="card-glass kpi-card" style="border-top:2px solid var(--emerald);">
        <div class="kpi-label">Active Tenants</div>
        <div class="kpi-value" style="color:var(--emerald);">{{ $stats['active_tenants'] }}</div>
        <span class="kpi-trend up"><i class="bi bi-circle-fill" style="font-size:0.4rem;"></i> Running</span>
        <div class="kpi-icon" style="background:var(--emerald-dim);color:var(--emerald);"><i class="bi bi-circle-fill"></i></div>
        <div class="kpi-sparkline"><canvas id="spark2"></canvas></div>
      </div>
    </div>
    <div class="col-6 col-lg-3 fade-in-up stagger-3">
      <div class="card-glass kpi-card" style="border-top:2px solid #a78bfa;">
        <div class="kpi-label">Total Users</div>
        <div class="kpi-value" style="color:#a78bfa;">{{ number_format($stats['total_users']) }}</div>
        <span class="kpi-trend up"><i class="bi bi-people-fill"></i> Platform-wide</span>
        <div class="kpi-icon" style="background:var(--purple-dim);color:#a78bfa;"><i class="bi bi-people-fill"></i></div>
        <div class="kpi-sparkline"><canvas id="spark3"></canvas></div>
      </div>
    </div>
    <div class="col-6 col-lg-3 fade-in-up stagger-4">
      <div class="card-glass kpi-card" style="border-top:2px solid var(--rose);">
        <div class="kpi-label">Trial Ending Soon</div>
        <div class="kpi-value" style="color:var(--rose);">{{ $stats['trial_ending'] }}</div>
        <span class="kpi-trend {{ $stats['trial_ending'] > 0 ? 'down' : 'flat' }}">
          <i class="bi bi-hourglass-split"></i> In 3 days
        </span>
        <div class="kpi-icon" style="background:var(--rose-dim);color:var(--rose);"><i class="bi bi-hourglass-split"></i></div>
        <div class="kpi-sparkline"><canvas id="spark4"></canvas></div>
      </div>
    </div>
  </div>

  {{-- ── CHARTS ROW ── --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-8 fade-in-up stagger-2">
      <div class="card-glass p-4">
        <div class="section-hdr">
          <div>
            <div class="section-hdr-title">Tenant Growth</div>
            <div class="section-hdr-sub">Monthly new tenants — last 6 months</div>
          </div>
        </div>
        <div class="chart-wrap">
          <canvas id="growthChart"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-4 fade-in-up stagger-3">
      <div class="card-glass p-4">
        <div class="section-hdr">
          <div>
            <div class="section-hdr-title">Plan Distribution</div>
            <div class="section-hdr-sub">Active subscription tiers</div>
          </div>
        </div>
        <div class="chart-wrap" style="height:180px;">
          <canvas id="planChart"></canvas>
        </div>
        <div class="mt-3" style="display:flex;flex-direction:column;gap:0.4rem;">
          @foreach($planDistribution as $plan => $count)
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.78rem;">
            <span style="color:var(--text-2);text-transform:capitalize;">{{ $plan }}</span>
            <span style="color:var(--text);font-weight:500;">{{ $count }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- ── BOTTOM ROW ── --}}
  <div class="row g-3">
    <div class="col-lg-7 fade-in-up stagger-2">
      <div class="card-glass p-4">
        <div class="section-hdr">
          <div>
            <div class="section-hdr-title">Recent Tenants</div>
            <div class="section-hdr-sub">Latest parlours onboarded</div>
          </div>
          <a href="{{ route('superadmin.tenants.index') }}" class="btn-ghost">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        <table class="dash-table">
          <thead>
            <tr>
              <th>Tenant</th>
              <th>Plan</th>
              <th>Appts</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentTenants as $tenant)
            <tr>
              <td>
                <span class="tenant-dot {{ $tenant->status === 'active' ? 'dot-green' : ($tenant->status === 'suspended' ? 'dot-red' : 'dot-gold') }}"></span>
                <strong style="font-weight:500;color:var(--text);">{{ $tenant->name }}</strong>
              </td>
              <td>
                <span class="plan-badge plan-{{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span>
              </td>
              <td>{{ number_format($tenant->appointments_count) }}</td>
              <td>
                <span class="status-badge {{ $tenant->status_badge_class }}">{{ ucfirst($tenant->status) }}</span>
              </td>
              <td>
                <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="btn-ghost" style="padding:0.3rem 0.7rem;font-size:0.68rem;">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" style="text-align:center;color:var(--text-3);padding:2rem;">Koi tenant nahi mila</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="col-lg-5 fade-in-up stagger-3">
      <div class="card-glass p-4">
        <div class="section-hdr-title mb-3">Platform Activity</div>
        <div class="timeline-item">
          <div class="timeline-dot" style="background:var(--emerald-dim);color:var(--emerald);"><i class="bi bi-building-add" style="font-size:0.7rem;"></i></div>
          <div class="timeline-body">
            <div class="timeline-title">New tenant onboarded</div>
            <div class="timeline-meta">{{ now()->subMinutes(15)->diffForHumans() }}</div>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot" style="background:var(--gold-dim);color:var(--gold);"><i class="bi bi-arrow-up-circle" style="font-size:0.7rem;"></i></div>
          <div class="timeline-body">
            <div class="timeline-title">Plan upgrade — Pro → Enterprise</div>
            <div class="timeline-meta">{{ now()->subHours(2)->diffForHumans() }}</div>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot" style="background:var(--rose-dim);color:var(--rose);"><i class="bi bi-pause-circle" style="font-size:0.7rem;"></i></div>
          <div class="timeline-body">
            <div class="timeline-title">Tenant suspended</div>
            <div class="timeline-meta">{{ now()->subHours(5)->diffForHumans() }}</div>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot" style="background:var(--purple-dim);color:#a78bfa;"><i class="bi bi-bell" style="font-size:0.7rem;"></i></div>
          <div class="timeline-body">
            <div class="timeline-title">{{ number_format(rand(400,600)) }} reminders sent</div>
            <div class="timeline-meta">{{ now()->subHours(8)->diffForHumans() }}</div>
          </div>
        </div>
        <div class="timeline-item" style="padding-bottom:0;">
          <div class="timeline-dot" style="background:var(--teal-dim);color:var(--teal-light);"><i class="bi bi-check2-all" style="font-size:0.7rem;"></i></div>
          <div class="timeline-body">
            <div class="timeline-title">Scheduler ran — all jobs healthy</div>
            <div class="timeline-meta">{{ now()->subHours(12)->diffForHumans() }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script>
const gold    = '#c9a96e';
const emerald = '#10b981';
const purple  = '#a78bfa';
const rose    = '#f43f5e';
const teal    = '#3a9e8d';
const gridColor = 'rgba(255,255,255,0.04)';
const tooltipBg = { backgroundColor:'#1a1a24', borderColor:'rgba(255,255,255,0.1)', borderWidth:1 };

// ── SPARKLINES ──
function sparkline(id, color, data) {
  const ctx = document.getElementById(id)?.getContext('2d');
  if (!ctx) return;
  new Chart(ctx, {
    type:'line',
    data:{ labels:data.map((_,i)=>i), datasets:[{ data, borderColor:color, borderWidth:1.5, fill:true, backgroundColor:color+'22', tension:0.4, pointRadius:0 }] },
    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false},tooltip:{enabled:false}}, scales:{x:{display:false},y:{display:false}}, animation:{duration:1200} }
  });
}
sparkline('spark1', gold,    [80,85,88,91,95,98,100,102,104]);
sparkline('spark2', emerald, [70,74,78,82,88,92,95,96,97]);
sparkline('spark3', purple,  [200,280,350,420,510,600,680,740,800]);
sparkline('spark4', rose,    [2,4,3,5,6,4,3,4,3]);

// ── GROWTH CHART ──
const growthData = @json($monthlyGrowth);
const months = Object.keys(growthData);
const counts  = Object.values(growthData);
new Chart(document.getElementById('growthChart').getContext('2d'), {
  type:'bar',
  data:{ labels:months, datasets:[{ label:'New Tenants', data:counts, backgroundColor:gold+'99', borderColor:gold, borderWidth:1, borderRadius:4 }] },
  options:{ responsive:true, maintainAspectRatio:false, animation:{duration:1200}, plugins:{legend:{display:false}, tooltip:tooltipBg}, scales:{ x:{grid:{display:false}}, y:{grid:{color:gridColor},ticks:{stepSize:1}} } }
});

// ── PLAN DOUGHNUT ──
const planData = @json($planDistribution);
const planLabels = Object.keys(planData).map(p => p.charAt(0).toUpperCase()+p.slice(1));
const planCounts = Object.values(planData);
new Chart(document.getElementById('planChart').getContext('2d'), {
  type:'doughnut',
  data:{ labels:planLabels, datasets:[{ data:planCounts, backgroundColor:[teal+'cc', purple+'cc', gold+'cc'], borderColor:['#0a0a0c'], borderWidth:2 }] },
  options:{ responsive:true, maintainAspectRatio:false, cutout:'70%', plugins:{ legend:{position:'bottom', labels:{color:'rgba(255,255,255,0.5)', boxWidth:10, font:{size:10}}}, tooltip:tooltipBg }, animation:{duration:1200} }
});
</script>
@endpush
