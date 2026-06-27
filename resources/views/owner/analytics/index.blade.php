@extends('layouts.owner')

@section('title', 'Analytics')
@section('page-title', 'Analytics')
@section('breadcrumb', 'Overview / Analytics')
@push('styles')
<style>
    /* Premium Scroller for all boxes */
    .lux-scroller::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(201, 169, 110, 0.3);
        /* Gold tint */
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb:hover {
        background: var(--gold);
    }

</style>
@endpush
@section('topbar-actions')
<form method="GET" style="display:inline-flex; align-items:center; gap:.5rem;">
    <div style="position: relative;">
        <select name="period" class="lux-input" style="width:140px; padding-right: 2rem; font-size:.8rem; color-scheme: dark; background: var(--bg-input); color: var(--text); cursor: pointer;" onchange="this.form.submit()" aria-label="Select time period">
            <option value="7" style="background: var(--bg-card); color: var(--text);" {{ request('period','30')==='7'  ? 'selected':'' }}>Last 7 Days</option>
            <option value="30" style="background: var(--bg-card); color: var(--text);" {{ request('period','30')==='30' ? 'selected':'' }}>Last 30 Days</option>
            <option value="90" style="background: var(--bg-card); color: var(--text);" {{ request('period','30')==='90' ? 'selected':'' }}>Last 90 Days</option>
        </select>
        <div style="position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
            <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
        </div>
    </div>
</form>
@endsection

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- KPIs --}}
<div class="row g-3 mb-4 fade-up s1">
    @php
    $kpis = [
    ['label'=>'Total Revenue', 'val'=>'₹'.number_format($analytics['revenue'] ?? 0), 'color'=>'var(--gold)', 'bg'=>'var(--gold-dim)', 'icon'=>'bi-currency-rupee', 'trend'=>($analytics['revenue_change'] ?? 0).'% vs prev', 'trendType'=>($analytics['revenue_change']??0)>=0?'up':'down'],
    ['label'=>'Total Bookings', 'val'=>number_format($analytics['bookings'] ?? 0), 'color'=>'var(--emerald)', 'bg'=>'var(--emerald-dim)', 'icon'=>'bi-calendar-check-fill', 'trend'=>($analytics['bookings_change'] ?? 0).'% vs prev', 'trendType'=>($analytics['bookings_change']??0)>=0?'up':'down'],
    ['label'=>'New Customers', 'val'=>number_format($analytics['new_customers'] ?? 0),'color'=>'var(--purple)', 'bg'=>'var(--purple-dim)', 'icon'=>'bi-people-fill', 'trend'=>'Recently joined', 'trendType'=>'flat'],
    ['label'=>'Avg Booking Value', 'val'=>'₹'.number_format($analytics['avg_value'] ?? 0),'color'=>'var(--teal-light)', 'bg'=>'var(--teal-dim)', 'icon'=>'bi-graph-up', 'trend'=>'Per completed booking', 'trendType'=>'flat'],
    ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="col-6 col-lg-3">
        <div class="card-lux p-3 h-100" style="position: relative; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="position:absolute; top:1rem; right:1rem; width:30px; height:30px; border-radius:8px; background:{{$kpi['bg']}}; color:{{$kpi['color']}}; display:flex; align-items:center; justify-content:center; font-size:0.85rem;">
                <i class="bi {{$kpi['icon']}}"></i>
            </div>

            <div style="font-size:0.65rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-3); margin-bottom:0.2rem; padding-right: 40px; white-space: normal; line-height: 1.3;">
                {{$kpi['label']}}
            </div>

            <div style="font-family:var(--ff-display); font-size:1.8rem; line-height:1; color:{{$kpi['color']}}; margin-bottom:0.5rem;">
                {{$kpi['val']}}
            </div>

            <div>
                <span class="trend-{{$kpi['trendType']}}" style="font-size: 0.65rem; padding: 0.2rem 0.5rem; border-radius: var(--r-pill);">
                    @if($kpi['trendType'] === 'up') <i class="bi bi-arrow-up-short"></i>
                    @elseif($kpi['trendType'] === 'down') <i class="bi bi-arrow-down-short"></i>
                    @endif
                    {{$kpi['trend']}}
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Main charts --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8 fade-up s2">
        <div class="card-lux p-3 h-100">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <h3 class="serif" style="font-size: 1.2rem; margin-bottom:0;">Revenue Trend</h3>
                <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">Revenue — last {{ request('period', 30) }} days</p>
            </div>
            <div style="position: relative; height: 220px; width: 100%;">
                <canvas id="revenueChart" aria-label="Revenue trend chart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4 fade-up s3">
        <div class="card-lux p-3 h-100 d-flex flex-column">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <h3 class="serif" style="font-size: 1.2rem; margin-bottom:0;">Booking Status</h3>
                <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">By status split</p>
            </div>

            <div style="position: relative; height: 160px; width: 100%; margin-bottom: 1rem;">
                <canvas id="statusChart" aria-label="Booking status chart"></canvas>
            </div>

            <ul style="list-style:none; padding:0; margin:auto 0 0 0;">
                @forelse($analytics['status_breakdown'] ?? [] as $status => $count)
                <li style="display:flex; justify-content:space-between; align-items: center; padding:.4rem 0; border-bottom:1px solid rgba(255,255,255,0.03); font-size:.75rem;">
                    <span style="color:var(--text-2); text-transform:capitalize;">{{ $status }}</span>
                    <span class="status-badge {{ match(strtolower($status)) { 'completed' => 'badge-active', 'confirmed' => 'badge-active', 'cancelled' => 'badge-suspended', default => 'badge-trial' } }}">
                        {{ number_format($count) }}
                    </span>
                </li>
                @empty
                <li style="text-align: center; color: var(--text-3); font-size: 0.75rem; padding: 1rem 0;">No status data</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="row g-3 fade-up s4">
    {{-- Top services --}}
    <div class="col-lg-6">
        <div class="card-lux p-3 h-100 d-flex flex-column">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1.2rem;">
                <h3 class="serif" style="font-size: 1.2rem; margin-bottom:0;">Top Services</h3>
                <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">By revenue generated</p>
            </div>

            @php $maxRev = collect($analytics['top_services'] ?? [])->max('revenue') ?: 1; @endphp

            <div class="lux-scroller" style="flex: 1; overflow-y: auto; max-height: 250px; padding-right: 10px;">
                @forelse($analytics['top_services'] ?? [] as $i => $svc)
                <div style="margin-bottom: 1.2rem;">
                    <div style="display:flex; justify-content:space-between; align-items: flex-end; margin-bottom:.4rem;">
                        <span style="font-size:.8rem; color:var(--text); font-weight: 500;" class="truncate">{{ $svc['name'] }}</span>
                        <span style="font-size:.85rem; color:var(--gold); font-weight: 500;">₹{{ number_format($svc['revenue']) }}</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; width: {{ round(($svc['revenue']/$maxRev)*100) }}%; background: var(--gold-grad); border-radius: 3px; transition: width 1s ease;"></div>
                    </div>
                    <p style="font-size:.65rem; color:var(--text-3); margin-top:.3rem; margin-bottom:0;">{{ number_format($svc['count']) }} bookings</p>
                </div>
                @empty
                <div style="text-align: center; padding: 3rem 0;">
                    <i class="bi bi-stars faint" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    <p style="color:var(--text-3); font-size:.8rem; margin:0;">No service data available</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Premium Staff Performance UI --}}
    <div class="col-lg-6">
        <div class="card-lux p-3 h-100 d-flex flex-column">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <h3 class="serif" style="font-size: 1.2rem; margin-bottom:0;">Staff Efficiency Metrics</h3>
                <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">Success vs Cancellation ratio</p>
            </div>

            <div class="lux-scroller" style="flex: 1; overflow-y: auto; max-height: 250px; padding-right: 10px; display: flex; flex-direction: column; gap: 0.8rem;">
                @forelse($analytics['staff_performance'] ?? [] as $staff)
                @php
                $completed = (int)($staff['completed'] ?? 0);
                $cancelled = (int)($staff['cancelled'] ?? 0);
                $total = $completed + $cancelled;
                $compPct = $total > 0 ? round(($completed / $total) * 100) : 0;
                $cancPct = $total > 0 ? round(($cancelled / $total) * 100) : 0;
                @endphp
                <div style="background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.03); border-radius: 12px; padding: 1rem; transition: background 0.2s; cursor: default;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='rgba(255,255,255,0.015)'">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem;">
                        <div style="display: flex; align-items: center; gap: 0.8rem;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-2); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; color: var(--gold);">
                                {{ strtoupper(substr($staff['name'] ?? 'S', 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; font-weight: 600; color: var(--text);">{{ $staff['name'] }}</div>
                                <div style="font-size: 0.65rem; color: var(--text-3);">{{ $total }} Total Assignments</div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-family: var(--ff-display); font-weight: 700; line-height: 1;">
                                <span style="font-size: 1.1rem; color: var(--emerald);">{{ $completed }}</span>
                                <span style="font-size: 0.8rem; color: var(--text-3); margin: 0 2px;">/</span>
                                <span style="font-size: 0.95rem; color: var(--rose);">{{ $cancelled }}</span>
                            </div>
                            <div style="font-size: 0.55rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-3); margin-top: 4px;">Done / Drop</div>
                        </div>
                    </div>

                    {{-- Dual Progress Bar --}}
                    <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.05); border-radius: 4px; display: flex; overflow: hidden; margin-bottom: 0.4rem;">
                        <div style="width: {{ $compPct }}%; background: var(--emerald); height: 100%; transition: width 1s ease; border-right: 1px solid var(--bg-card);"></div>
                        <div style="width: {{ $cancPct }}%; background: var(--rose); height: 100%; transition: width 1s ease;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.65rem;">
                        <span style="color: var(--emerald); font-weight: 500;">{{ $compPct }}% Success</span>
                        <span style="color: var(--rose); font-weight: 500;">{{ $cancPct }}% Dropped</span>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 3rem 0; color: var(--text-3);">
                    <i class="bi bi-person-x faint" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                    <p style="font-size: 0.75rem; margin:0;">No staff performance data</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Staff Leaderboard --}}
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card-lux p-4">
            <div style="border-bottom:1px solid var(--border);padding-bottom:.75rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <h3 class="serif" style="font-size:1.2rem;margin-bottom:0;">Staff Leaderboard</h3>
                    <p style="font-size:.65rem;color:var(--text-3);margin:0;">Ranked by revenue generated this period</p>
                </div>
                <span style="font-size:.7rem;color:var(--text-3);">{{ request('period', '30') }} day view</span>
            </div>

            @if(count($staffPerf ?? []) > 0)
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                @foreach($staffPerf as $index => $staff)
                @php
                $rank = $index + 1;
                $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "#{$rank}" };
                $maxRevenue = $staffPerf[0]['revenue'] ?? 1;
                $barWidth = $maxRevenue > 0 ? round(($staff['revenue'] / $maxRevenue) * 100) : 0;
                $barColor = match($rank) { 1 => 'var(--gold)', 2 => '#94a3b8', 3 => '#cd7f32', default => 'var(--purple)' };
                @endphp
                <div style="display:flex;align-items:center;gap:1rem;padding:.75rem;background:var(--bg-card);border-radius:var(--r-sm);border:1px solid {{ $rank === 1 ? 'rgba(201,168,76,.3)' : 'var(--border)' }};">

                    {{-- Rank --}}
                    <div style="width:36px;text-align:center;font-size:{{ $rank <= 3 ? '1.3rem' : '.85rem' }};font-weight:600;color:var(--text-3);flex-shrink:0;">
                        {{ $medal }}
                    </div>

                    {{-- Avatar --}}
                    <div style="width:38px;height:38px;border-radius:50%;background:{{ $barColor }};display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#000;flex-shrink:0;">
                        {{ $staff['initials'] }}
                    </div>

                    {{-- Name + Bar --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;">
                            <span style="font-size:.85rem;font-weight:600;color:var(--text);">{{ $staff['name'] }}</span>
                            <span style="font-size:.8rem;font-weight:700;color:{{ $barColor }};">₹{{ number_format($staff['revenue']) }}</span>
                        </div>
                        <div style="background:var(--bg-input);border-radius:20px;height:5px;overflow:hidden;">
                            <div style="width:{{ $barWidth }}%;height:100%;background:{{ $barColor }};border-radius:20px;transition:width .5s ease;"></div>
                        </div>
                        <div style="display:flex;gap:1rem;margin-top:.3rem;font-size:.65rem;color:var(--text-3);">
                            <span><i class="bi bi-check2-circle"></i> {{ $staff['services'] }} completed</span>
                            <span><i class="bi bi-x-circle"></i> {{ $staff['cancelled'] }} cancelled</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="text-align:center;padding:2rem;color:var(--text-3);font-size:.8rem;">
                <i class="bi bi-bar-chart" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                No staff performance data available yet.
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // 1. SAFE FALLBACK DEFINITIONS (Prevents JS crashes if global vars fail to load)
        const CHART_COLORS = {
            gold: '#c9a96e'
            , emerald: '#10b981'
            , purple: '#8b5cf6'
            , teal: '#3a9e8d'
            , rose: '#f43f5e'
            , amber: '#f59e0b'
        };
        const CHART_GRID = 'rgba(255,255,255,0.04)';
        const CHART_TT = {
            backgroundColor: '#1a1a24'
            , borderColor: 'rgba(255,255,255,0.1)'
            , borderWidth: 1
        };

        const {
            gold
            , emerald
            , purple
            , teal
            , rose
            , amber
        } = CHART_COLORS;

        /* Revenue trend line */
        const revRaw = @json($analytics['daily_revenue'] ? ? []);
        const revLabels = Object.keys(revRaw).length > 0 ? Object.keys(revRaw) : ['N/A'];
        const revValues = Object.values(revRaw).length > 0 ? Object.values(revRaw) : [0];

        const revCtx = document.getElementById('revenueChart');
        if (revCtx) {
            new Chart(revCtx.getContext('2d'), {
                type: 'line'
                , data: {
                    labels: revLabels
                    , datasets: [{
                        label: 'Revenue ₹'
                        , data: revValues
                        , borderColor: gold
                        , backgroundColor: gold + '18'
                        , borderWidth: 2
                        , fill: true
                        , tension: 0.4
                        , pointBackgroundColor: gold
                        , pointRadius: 3
                        , pointHoverRadius: 5
                    , }]
                , }
                , options: {
                    responsive: true
                    , maintainAspectRatio: false
                    , plugins: {
                        legend: {
                            display: false
                        }
                        , tooltip: CHART_TT
                    }
                    , scales: {
                        x: {
                            grid: {
                                display: false
                            }
                            , ticks: {
                                color: 'rgba(255,255,255,0.42)'
                                , font: {
                                    size: 10
                                }
                            }
                        }
                        , y: {
                            beginAtZero: true
                            , grid: {
                                color: CHART_GRID
                            }
                            , ticks: {
                                color: 'rgba(255,255,255,0.42)'
                                , font: {
                                    size: 10
                                }
                                , callback: v => '₹' + v.toLocaleString('en-IN')
                            }
                        }
                    , }
                , }
            , });
        }

        /* Status doughnut */
        const statusData = @json($analytics['status_breakdown'] ? ? []);
        const statusLabels = Object.keys(statusData).length > 0 ? Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1)) : ['No Data'];
        const statusValues = Object.values(statusData).length > 0 ? Object.values(statusData) : [1]; // 1 for empty grey circle

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut'
                , data: {
                    labels: statusLabels
                    , datasets: [{
                        data: statusValues
                        , backgroundColor: Object.keys(statusData).length > 0 ? [emerald + 'cc', gold + 'cc', teal + 'cc', rose + 'cc'] : ['rgba(255,255,255,0.05)']
                        , borderColor: 'transparent'
                        , borderWidth: 0
                    , }]
                , }
                , options: {
                    responsive: true
                    , maintainAspectRatio: false
                    , cutout: '70%'
                    , plugins: {
                        legend: {
                            display: false
                        }
                        , tooltip: CHART_TT
                    }
                , }
            , });
        }

        /* (Deleted the ugly bar chart code here to keep things fast and clean!) */
    });

</script>
@endpush
