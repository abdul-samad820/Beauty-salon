@extends('owner.layouts.app')
@section('title','Analytics')
@section('page-title','Analytics')
@section('page-sub', now()->format('F Y') . ' · Studio Performance Report')

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('styles')
<style>
  .metric-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:1.4rem;transition:all var(--transition)}
  .metric-card:hover{border-color:rgba(201,169,110,.2);box-shadow:0 8px 40px rgba(0,0,0,.4)}
  .metric-val{font-family:var(--ff-display);font-size:2rem;font-weight:400;margin:.3rem 0 .2rem}
  .metric-label{font-size:.62rem;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:var(--text-3)}
  .insight-card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:1rem 1.2rem;display:flex;align-items:flex-start;gap:.9rem;margin-bottom:.8rem}
  .insight-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0}
  .insight-title{font-size:.82rem;font-weight:500;color:var(--text);margin-bottom:.2rem}
  .insight-desc{font-size:.72rem;color:var(--text-3);line-height:1.5}
  .analysis-header{background:linear-gradient(135deg,rgba(201,169,110,.06),transparent 60%);border:1px solid rgba(201,169,110,.12);border-radius:16px;padding:1.6rem 2rem;margin-bottom:1.5rem;position:relative;overflow:hidden}
  .analysis-header::before{content:'ANALYTICS';position:absolute;font-family:var(--ff-display);font-size:clamp(3rem,10vw,7rem);font-weight:300;color:rgba(201,169,110,.04);right:-1rem;top:50%;transform:translateY(-50%);white-space:nowrap;pointer-events:none}
</style>
@endpush

@section('content')
<div class="analysis-header fade-up s1">
  <div style="font-family:var(--ff-display);font-size:clamp(1.4rem,3vw,2rem);font-weight:300;color:var(--text);margin-bottom:.3rem">
    Performance Insights
  </div>
  <div style="font-size:.78rem;color:var(--text-3)">
    ₹{{ number_format($stats['total_revenue']) }} total revenue · {{ number_format($stats['total_appts']) }} appointments · {{ $stats['total_customers'] }} customers · {{ $stats['retention_rate'] }}% retention
  </div>
</div>

{{-- Metric cards --}}
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3 fade-up s1"><div class="metric-card" style="border-top:2px solid var(--gold)"><div class="metric-label">Total Revenue</div><div class="metric-val" style="color:var(--gold)">₹{{ number_format($stats['total_revenue']) }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s2"><div class="metric-card" style="border-top:2px solid var(--emerald)"><div class="metric-label">This Month</div><div class="metric-val" style="color:var(--emerald)">₹{{ number_format($stats['this_month_rev']) }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s3"><div class="metric-card" style="border-top:2px solid var(--purple)"><div class="metric-label">Customers</div><div class="metric-val" style="color:var(--purple)">{{ $stats['total_customers'] }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s4"><div class="metric-card" style="border-top:2px solid var(--teal-light)"><div class="metric-label">Retention Rate</div><div class="metric-val" style="color:var(--teal-light)">{{ $stats['retention_rate'] }}%</div></div></div>
</div>

{{-- Charts row --}}
<div class="row g-3 mb-3">
  <div class="col-lg-8 fade-up s2">
    <div class="card-lux p-4">
      <div class="sec-hdr"><div><div class="sec-title">Revenue Growth</div><div class="sec-sub">Monthly revenue — last 6 months</div></div></div>
      <div style="height:230px"><canvas id="growthChart"></canvas></div>
    </div>
  </div>
  <div class="col-lg-4 fade-up s3">
    <div class="card-lux p-4">
      <div class="sec-hdr"><div><div class="sec-title">New Customers</div><div class="sec-sub">Monthly acquisition</div></div></div>
      <div style="height:170px"><canvas id="custChart"></canvas></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-4 fade-up s2">
    <div class="card-lux p-4">
      <div class="sec-hdr"><div><div class="sec-title">Staff Performance</div><div class="sec-sub">This month revenue</div></div></div>
      <div style="height:200px"><canvas id="staffChart"></canvas></div>
    </div>
  </div>
  <div class="col-lg-4 fade-up s3">
    <div class="card-lux p-4">
      <div class="sec-hdr"><div><div class="sec-title">Top Services</div><div class="sec-sub">By booking count</div></div></div>
      <div style="height:200px"><canvas id="svcRevChart"></canvas></div>
    </div>
  </div>
  <div class="col-lg-4 fade-up s4">
    <div class="card-lux p-4">
      <div class="sec-hdr"><div><div class="sec-title">Popular Time Slots</div><div class="sec-sub">Peak booking hours</div></div></div>
      <div style="height:200px"><canvas id="slotsChart"></canvas></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8 fade-up s2">
    <div class="card-lux p-4">
      <div class="sec-hdr mb-3"><div class="sec-title">Staff Leaderboard</div><div class="sec-sub">Revenue this month</div></div>
      @php $maxRev = $staffPerf->max('revenue') ?: 1; @endphp
      @foreach($staffPerf as $s)
      <div style="margin-bottom:1rem">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem">
          <div style="display:flex;align-items:center;gap:.6rem">
            <div style="width:30px;height:30px;border-radius:50%;background:var(--gold-dim);display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:.75rem;color:var(--gold)">{{ $s['initials'] }}</div>
            <span style="font-size:.82rem;color:var(--text)">{{ $s['name'] }}</span>
            <span style="font-size:.68rem;color:var(--text-3)">{{ $s['services'] }} services</span>
          </div>
          <span style="font-size:.85rem;color:var(--gold);font-weight:500">₹{{ number_format($s['revenue']) }}</span>
        </div>
        <div class="prog-track"><div class="prog-fill" style="width:{{ $maxRev>0 ? round(($s['revenue']/$maxRev)*100) : 0 }}%;background:var(--gold-grad)"></div></div>
      </div>
      @endforeach
    </div>
  </div>
  <div class="col-lg-4 fade-up s3">
    <div class="card-lux p-4">
      <div class="sec-hdr mb-3"><div class="sec-title">AI Insights</div></div>
      @forelse($insights as $ins)
      <div class="insight-card">
        <div class="insight-icon" style="background:var(--{{ $ins['color'] }}-dim);color:var(--{{ $ins['color'] }})"><i class="bi {{ $ins['icon'] }}"></i></div>
        <div><div class="insight-title">{{ $ins['title'] }}</div><div class="insight-desc">{{ $ins['desc'] }}</div></div>
      </div>
      @empty
      <div style="text-align:center;color:var(--text-3);font-size:.82rem;padding:1rem 0">Zyada data aane par insights dikhenge</div>
      @endforelse
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const gold='#c9a96e',emerald='#10b981',purple='#8b5cf6',teal='#3a9e8d',rose='#f43f5e',amber='#f59e0b';
const gridC='rgba(255,255,255,0.04)', ttOpt={backgroundColor:'#1a1a24',borderColor:'rgba(255,255,255,0.1)',borderWidth:1};

const revData  = @json($monthlyRevenue);
const custData = @json($monthlyCustomers);
const svcData  = @json($topServices);
const staffData= @json($staffPerf);
const slotData = @json($popularSlots);

// Revenue growth
new Chart(document.getElementById('growthChart').getContext('2d'), {
  type:'bar', data:{ labels:Object.keys(revData), datasets:[{ label:'₹ Revenue', data:Object.values(revData), backgroundColor:gold+'99', borderColor:gold, borderWidth:1, borderRadius:4 }] },
  options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false},tooltip:ttOpt}, scales:{ x:{grid:{display:false}}, y:{grid:{color:gridC},ticks:{callback:v=>'₹'+v.toLocaleString('en-IN')}} } }
});
// Customers
new Chart(document.getElementById('custChart').getContext('2d'), {
  type:'line', data:{ labels:Object.keys(custData), datasets:[{ label:'New Customers', data:Object.values(custData), borderColor:purple, borderWidth:2, fill:true, backgroundColor:purple+'22', tension:0.4, pointRadius:3, pointBackgroundColor:purple }] },
  options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false},tooltip:ttOpt}, scales:{ x:{grid:{display:false}}, y:{grid:{color:gridC},ticks:{stepSize:1}} } }
});
// Staff bar
new Chart(document.getElementById('staffChart').getContext('2d'), {
  type:'bar', data:{ labels:staffData.map(s=>s.name), datasets:[{ data:staffData.map(s=>s.revenue), backgroundColor:gold+'99', borderColor:gold, borderWidth:1, borderRadius:4 }] },
  options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false},tooltip:ttOpt}, scales:{ x:{grid:{color:gridC}}, y:{grid:{display:false}} } }
});
// Services doughnut
new Chart(document.getElementById('svcRevChart').getContext('2d'), {
  type:'doughnut', data:{ labels:svcData.map(s=>s.name), datasets:[{ data:svcData.map(s=>s.total), backgroundColor:[gold+'cc',emerald+'cc',purple+'cc',teal+'cc',rose+'cc',amber+'cc'], borderColor:'#111116', borderWidth:2 }] },
  options:{ responsive:true, maintainAspectRatio:false, cutout:'65%', plugins:{legend:{position:'bottom',labels:{color:'rgba(255,255,255,.5)',boxWidth:10,font:{size:9}}},tooltip:ttOpt} }
});
// Slots bar
new Chart(document.getElementById('slotsChart').getContext('2d'), {
  type:'bar', data:{ labels:Object.keys(slotData), datasets:[{ data:Object.values(slotData), backgroundColor:teal+'99', borderColor:teal, borderWidth:1, borderRadius:4 }] },
  options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false},tooltip:ttOpt}, scales:{ x:{grid:{display:false}}, y:{grid:{color:gridC},ticks:{stepSize:1}} } }
});
// Progress bars
document.querySelectorAll('.prog-fill').forEach(el=>{const w=el.style.width;el.style.width='0';setTimeout(()=>el.style.width=w,400)});
</script>
@endpush
