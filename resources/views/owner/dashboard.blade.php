@extends('owner.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-sub', now()->format('l, d M Y'))

@section('topbar-actions')
  <a href="{{ route('owner.appointments.index') }}" class="btn-gold-sm">
    <i class="bi bi-plus-lg"></i> New Booking
  </a>
@endsection

@push('styles')
<style>
  .page-hero { background:linear-gradient(135deg,rgba(201,169,110,0.06),transparent 60%); border:1px solid rgba(201,169,110,0.12); border-radius:16px; padding:1.8rem 2rem; margin-bottom:1.8rem; position:relative; overflow:hidden; }
  .page-hero::before { content:'DASHBOARD'; position:absolute; font-family:var(--ff-display); font-size:clamp(4rem,12vw,9rem); font-weight:300; color:rgba(201,169,110,0.04); right:-1rem; top:50%; transform:translateY(-50%); white-space:nowrap; pointer-events:none; letter-spacing:-0.03em; }
  .hero-greeting { font-family:var(--ff-display); font-size:clamp(1.6rem,4vw,2.6rem); font-weight:300; color:var(--text); line-height:1.15; margin-bottom:0.4rem; }
  .hero-greeting em { font-style:italic; color:var(--gold); }
  .status-pill { display:inline-flex; align-items:center; gap:0.45rem; background:rgba(255,255,255,0.04); border:1px solid var(--border-2); border-radius:20px; padding:0.3rem 0.9rem; font-size:0.7rem; color:var(--text-2); }
  .status-pill .dot { width:6px; height:6px; border-radius:50%; }
  .appt-row { display:flex; align-items:center; gap:0.9rem; padding:0.85rem 1.2rem; border-bottom:1px solid rgba(255,255,255,0.03); transition:background 0.2s; }
  .appt-row:hover { background:rgba(255,255,255,0.02); }
  .appt-row:last-child { border-bottom:none; }
  .appt-time { font-family:var(--ff-display); font-size:1rem; font-weight:400; color:var(--gold); min-width:52px; }
  .appt-av { width:34px; height:34px; border-radius:50%; background:var(--gold-dim); display:flex; align-items:center; justify-content:center; font-family:var(--ff-display); font-size:0.85rem; color:var(--gold); flex-shrink:0; }
  .appt-name { font-size:0.84rem; font-weight:500; color:var(--text); }
  .appt-service { font-size:0.7rem; color:var(--text-3); margin-top:0.1rem; }
  .staff-bar-item { margin-bottom:1rem; }
  .staff-bar-item:last-child { margin-bottom:0; }
  .stock-row { display:flex; align-items:center; gap:0.9rem; padding:0.75rem 0; border-bottom:1px solid rgba(255,255,255,0.03); }
  .stock-row:last-child { border-bottom:none; }
  .stock-ic { width:32px; height:32px; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:0.85rem; flex-shrink:0; }
</style>
@endpush

@push('head-scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

  {{-- ── HERO ── --}}
  <div class="page-hero fade-up s1">
    <div class="hero-greeting">
      Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
      <em>{{ explode(' ', auth()->user()->name)[0] }}</em> ✦
    </div>
    <div style="font-size:0.78rem;color:var(--text-3);margin-bottom:1rem;">
      {{ auth()->user()->tenant?->name }} · {{ now()->format('l, d M Y') }}
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
      <span class="status-pill"><span class="dot" style="background:var(--emerald);box-shadow:0 0 6px var(--emerald);"></span> {{ $stats['upcoming_today'] }} upcoming today</span>
      <span class="status-pill"><span class="dot" style="background:var(--gold);"></span> ₹{{ number_format($stats['month_revenue']) }} this month</span>
      @if($stats['low_stock'] > 0)
        <span class="status-pill"><span class="dot" style="background:var(--rose);"></span> {{ $stats['low_stock'] }} low stock</span>
      @endif
      @if($stats['pending_commissions'] > 0)
        <span class="status-pill"><span class="dot" style="background:var(--amber);"></span> ₹{{ number_format($stats['pending_commissions']) }} pending payouts</span>
      @endif
    </div>
  </div>

  {{-- ── KPI ROW ── --}}
  <div class="row g-3 mb-3">
    <div class="col-6 col-lg-3 fade-up s1">
      <div class="card-lux kpi-pad gold-border glow-hover">
        <div class="kpi-label"><span class="live-dot"></span> Today's Bookings</div>
        <div class="kpi-value" style="color:var(--gold)">{{ $stats['today_bookings'] }}</div>
        <span class="kpi-trend trend-up"><i class="bi bi-calendar-check-fill"></i> {{ $stats['upcoming_today'] }} upcoming</span>
        <div class="kpi-icon-abs" style="background:var(--gold-dim);color:var(--gold)"><i class="bi bi-calendar-check-fill"></i></div>
        <div class="kpi-spark"><canvas id="sp1"></canvas></div>
      </div>
    </div>
    <div class="col-6 col-lg-3 fade-up s2">
      <div class="card-lux kpi-pad" style="border-top:2px solid var(--emerald)">
        <div class="kpi-label">Monthly Revenue</div>
        <div class="kpi-value" style="color:var(--emerald)">₹{{ number_format($stats['month_revenue']) }}</div>
        <span class="kpi-trend trend-up"><i class="bi bi-currency-rupee"></i> This month</span>
        <div class="kpi-icon-abs" style="background:var(--emerald-dim);color:var(--emerald)"><i class="bi bi-currency-rupee"></i></div>
        <div class="kpi-spark"><canvas id="sp2"></canvas></div>
      </div>
    </div>
    <div class="col-6 col-lg-3 fade-up s3">
      <div class="card-lux kpi-pad" style="border-top:2px solid var(--purple)">
        <div class="kpi-label">Active Customers</div>
        <div class="kpi-value" style="color:var(--purple)">{{ $stats['total_customers'] }}</div>
        <span class="kpi-trend trend-up"><i class="bi bi-people-fill"></i> Registered</span>
        <div class="kpi-icon-abs" style="background:var(--purple-dim);color:var(--purple)"><i class="bi bi-people-fill"></i></div>
        <div class="kpi-spark"><canvas id="sp3"></canvas></div>
      </div>
    </div>
    <div class="col-6 col-lg-3 fade-up s4">
      <div class="card-lux kpi-pad" style="border-top:2px solid var(--teal-light)">
        <div class="kpi-label">Staff Active</div>
        <div class="kpi-value" style="color:var(--teal-light)">{{ $stats['staff_active'] }}/{{ $stats['staff_total'] }}</div>
        <span class="kpi-trend trend-flat"><i class="bi bi-person-badge-fill"></i> Available</span>
        <div class="kpi-icon-abs" style="background:var(--teal-dim);color:var(--teal-light)"><i class="bi bi-person-badge-fill"></i></div>
        <div class="kpi-spark"><canvas id="sp4"></canvas></div>
      </div>
    </div>
  </div>

  {{-- ── CHARTS ROW ── --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-8 fade-up s2">
      <div class="card-lux p-4">
        <div class="sec-hdr">
          <div>
            <div class="sec-title">Revenue Overview</div>
            <div class="sec-sub">Monthly earnings — {{ auth()->user()->tenant?->name }}</div>
          </div>
        </div>
        <div class="chart-box" style="height:220px"><canvas id="revChart"></canvas></div>
      </div>
    </div>
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
          @foreach($topServices as $svc)
          <div style="display:flex;justify-content:space-between;margin-bottom:0.4rem;">
            <span style="font-size:0.75rem;color:var(--text-2)">{{ $svc['name'] }}</span>
            <span style="font-size:0.75rem;color:var(--gold)">{{ $svc['total'] }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- ── BOTTOM ROW ── --}}
  <div class="row g-3">
    {{-- Today's Schedule --}}
    <div class="col-lg-4 fade-up s2">
      <div class="card-lux">
        <div class="sec-hdr p-4 pb-0">
          <div>
            <div class="sec-title">Today's Schedule</div>
            <div class="sec-sub">{{ now()->format('d M Y') }}</div>
          </div>
          <a href="{{ route('owner.appointments.today') }}" class="btn-ghost-sm">View All</a>
        </div>
        <div style="margin-top:0.8rem;">
          @forelse($todayAppointments as $appt)
          <div class="appt-row">
            <div class="appt-time">{{ \Carbon\Carbon::parse($appt->start_time)->format('h:i') }}</div>
            <div class="appt-av">{{ strtoupper(substr($appt->customer?->name ?? 'C', 0, 2)) }}</div>
            <div style="flex:1;min-width:0;">
              <div class="appt-name">{{ $appt->customer?->name ?? 'Walk-in' }}</div>
              <div class="appt-service">{{ $appt->service?->name }} · {{ $appt->staff?->user?->name }}</div>
            </div>
            <span class="lux-badge {{ match($appt->status) { 'completed' => 'lb-green', 'cancelled' => 'lb-red', 'confirmed' => 'lb-gold', default => 'lb-muted' } }}">
              {{ ucfirst($appt->status) }}
            </span>
          </div>
          @empty
          <div style="padding:2rem;text-align:center;color:var(--text-3);font-size:0.82rem;">
            <i class="bi bi-calendar-x" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;"></i>
            Aaj koi booking nahi
          </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Staff Performance --}}
    <div class="col-lg-4 fade-up s3">
      <div class="card-lux p-4">
        <div class="sec-hdr">
          <div>
            <div class="sec-title">Staff Performance</div>
            <div class="sec-sub">Completed services — this month</div>
          </div>
          <a href="{{ route('owner.staff.index') }}" class="btn-ghost-sm">Details</a>
        </div>
        @php $maxCompleted = $staffPerformance->max('completed') ?: 1; @endphp
        @foreach($staffPerformance as $s)
        <div class="staff-bar-item">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div style="display:flex;align-items:center;gap:0.6rem;">
              <div style="width:28px;height:28px;border-radius:50%;background:var(--gold-dim);display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-family:var(--ff-display);color:var(--gold);">{{ $s['initials'] }}</div>
              <span style="font-size:0.8rem;color:var(--text);">{{ $s['name'] }}</span>
            </div>
            <span style="font-size:0.78rem;color:var(--gold);font-weight:500;">{{ $s['completed'] }}</span>
          </div>
          <div class="prog-track">
            <div class="prog-fill" style="width:{{ $maxCompleted > 0 ? round(($s['completed']/$maxCompleted)*100) : 0 }}%;background:var(--gold-grad);"></div>
          </div>
        </div>
        @endforeach
        @if($staffPerformance->isEmpty())
          <div style="text-align:center;color:var(--text-3);font-size:0.82rem;padding:1rem 0;">Koi staff nahi mili</div>
        @endif
      </div>
    </div>

    {{-- Low Stock + Commissions --}}
    <div class="col-lg-4 fade-up s4">
      {{-- Low Stock --}}
      <div class="card-lux p-4 mb-3">
        <div class="sec-hdr">
          <div>
            <div class="sec-title">Low Stock</div>
            <div class="sec-sub">Replenishment needed</div>
          </div>
          <a href="{{ route('owner.inventory.index') }}" class="btn-ghost-sm" style="font-size:0.62rem">Fix</a>
        </div>
        @forelse($lowStockProducts as $prod)
        <div class="stock-row">
          <div class="stock-ic" style="background:var(--rose-dim);color:var(--rose)"><i class="bi bi-box-seam"></i></div>
          <div style="flex:1;">
            <div style="font-size:0.8rem;color:var(--text);">{{ $prod->name }}</div>
            <div style="font-size:0.68rem;color:var(--rose);">{{ $prod->quantity }} left · threshold {{ $prod->low_stock_threshold }}</div>
          </div>
        </div>
        @empty
        <div style="text-align:center;color:var(--text-3);font-size:0.82rem;padding:0.8rem 0;">
          <i class="bi bi-check-circle" style="color:var(--emerald);"></i> All stocked up!
        </div>
        @endforelse
      </div>

      {{-- Pending Commissions --}}
      @if($stats['pending_commissions'] > 0)
      <div class="card-lux p-4">
        <div class="sec-hdr">
          <div>
            <div class="sec-title">Pending Payouts</div>
            <div class="sec-sub">Commission due this month</div>
          </div>
        </div>
        <div style="font-family:var(--ff-display);font-size:2rem;color:var(--amber);margin-bottom:0.5rem;">
          ₹{{ number_format($stats['pending_commissions']) }}
        </div>
        <a href="{{ route('owner.commissions.index') }}" class="btn-gold-sm w-100 justify-content-center">
          <i class="bi bi-cash-stack"></i> View Commissions
        </a>
      </div>
      @endif
    </div>
  </div>

@endsection

@push('scripts')
<script>
const gold    = '#c9a96e', emerald = '#10b981', purple = '#8b5cf6';
const teal    = '#3a9e8d', gridC   = 'rgba(255,255,255,0.04)';
const ttOpts  = { backgroundColor:'#1a1a24', borderColor:'rgba(255,255,255,0.1)', borderWidth:1 };

// Sparklines
function spark(id, color, data) {
  const ctx = document.getElementById(id)?.getContext('2d');
  if (!ctx) return;
  new Chart(ctx, { type:'line', data:{ labels:data.map((_,i)=>i), datasets:[{ data, borderColor:color, borderWidth:1.5, fill:true, backgroundColor:color+'22', tension:0.4, pointRadius:0 }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false},tooltip:{enabled:false}}, scales:{x:{display:false},y:{display:false}} } });
}
spark('sp1', gold,    [5,8,10,9,11,12,10,12]);
spark('sp2', emerald, [40000,52000,60000,70000,78000,84200]);
spark('sp3', purple,  [280,295,310,320,335,342]);
spark('sp4', teal,    [6,7,8,8,7,8,8,8]);

// Revenue chart
const revData  = @json($monthlyRevenue);
new Chart(document.getElementById('revChart').getContext('2d'), {
  type:'bar',
  data:{ labels:Object.keys(revData), datasets:[{ label:'Revenue ₹', data:Object.values(revData), backgroundColor:gold+'99', borderColor:gold, borderWidth:1, borderRadius:4 }] },
  options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, tooltip:ttOpts}, scales:{ x:{grid:{display:false}}, y:{grid:{color:gridC}, ticks:{callback:v=>'₹'+v.toLocaleString('en-IN')}} } }
});

// Services doughnut
const svcData = @json($topServices);
new Chart(document.getElementById('servChart').getContext('2d'), {
  type:'doughnut',
  data:{ labels:svcData.map(s=>s.name), datasets:[{ data:svcData.map(s=>s.total), backgroundColor:[gold+'cc',emerald+'cc',purple+'cc',teal+'cc','#f59e0b'+'cc'], borderColor:'#111116', borderWidth:2 }] },
  options:{ responsive:true, maintainAspectRatio:false, cutout:'68%', plugins:{legend:{display:false}, tooltip:ttOpts} }
});

// Animate progress bars
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.prog-fill').forEach(el => {
    const w = el.style.width; el.style.width = '0'; setTimeout(() => el.style.width = w, 300);
  });
});
</script>
@endpush
