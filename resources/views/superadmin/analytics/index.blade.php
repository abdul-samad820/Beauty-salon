@extends('layouts.superadmin')

@section('title', 'Platform Analytics')
@section('page-title', 'Platform Analytics')
@section('page-sub', 'Across all tenants · All parlours')

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('styles')
<style>
    .period-tab {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .38rem .85rem;
        border-radius: 7px;
        font-size: .7rem;
        font-weight: 500;
        letter-spacing: .06em;
        text-decoration: none;
        border: 1px solid var(--border-2);
        color: var(--text-3);
        transition: all .2s;
    }

    .period-tab.active,
    .period-tab:hover {
        background: var(--gold-dim);
        color: var(--gold);
        border-color: rgba(201, 169, 110, .3);
    }

    .chart-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1.5rem;
    }

    .chart-title {
        font-family: var(--ff-display);
        font-size: 1.05rem;
        color: var(--text);
        margin-bottom: .25rem;
    }

    .chart-sub {
        font-size: .68rem;
        color: var(--text-3);
    }

    /* Custom Scroller for Table */
    .lux-scroller::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(201, 169, 110, 0.2);
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb:hover {
        background: var(--gold);
    }

</style>
@endpush

@section('content')

{{-- Period Filters --}}
<div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.8rem;" class="fade-up s1">
    @foreach(['7'=>'7 Days','30'=>'30 Days','90'=>'3 Months','365'=>'1 Year'] as $val=>$label)
    <a href="{{ route('superadmin.analytics', ['period' => $val]) }}" class="period-tab {{ $period === $val ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- FIXED KPI Row (Icon Top, Text Bottom) --}}
<div class="row g-3 mb-4">
    @php
    $kpis = [
    ['label'=>'Total Tenants', 'value'=> number_format($stats['total_tenants']), 'icon'=>'bi-buildings-fill', 'color'=>'var(--gold)', 'bg'=>'var(--gold-dim)', 'trend'=> $stats['new_tenants'].' new'],
    ['label'=>'Active Salons', 'value'=> number_format($stats['active_tenants']), 'icon'=>'bi-check-circle-fill','color'=>'var(--emerald)', 'bg'=>'var(--emerald-dim)', 'trend'=>'this period'],
    ['label'=>'Total Bookings', 'value'=> number_format($stats['total_bookings']), 'icon'=>'bi-calendar-check', 'color'=>'var(--purple)', 'bg'=>'var(--purple-dim)', 'trend'=> $stats['completion_rate'].'% completed'],
    ['label'=>'New Customers', 'value'=> number_format($stats['total_customers']), 'icon'=>'bi-people-fill', 'color'=>'var(--teal-light)','bg'=>'var(--teal-dim)', 'trend'=>'registered'],
    ['label'=>'Platform Revenue', 'value'=>'₹'.number_format($stats['total_revenue'],0,'.',','), 'icon'=>'bi-currency-rupee','color'=>'var(--amber)','bg'=>'var(--amber-dim)','trend'=>'completed appointments'],
    ['label'=>'Completion Rate', 'value'=> $stats['completion_rate'].'%', 'icon'=>'bi-graph-up-arrow', 'color'=>'var(--emerald)', 'bg'=>'var(--emerald-dim)', 'trend'=> number_format($stats['completed_bookings']).' done'],
    ];
    @endphp

    @foreach($kpis as $i => $kpi)
    <div class="col-xl-2 col-md-4 col-6 fade-up" style="animation-delay:{{ $i * .05 }}s">
        <div class="card-lux p-3" style="height:100%; display: flex; flex-direction: column; justify-content: space-between;">

            {{-- Icon Section (Top) --}}
            <div style="width: 42px; height: 42px; border-radius: 12px; background: {{ $kpi['bg'] }}; color: {{ $kpi['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 1.2rem; border: 1px solid rgba(255,255,255,0.02);">
                <i class="bi {{ $kpi['icon'] }}"></i>
            </div>

            {{-- Details Section (Bottom) --}}
            <div>
                <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; line-height: 1.2;">
                    {{ $kpi['label'] }}
                </div>

                <div style="font-family: var(--ff-display); font-size: 1.6rem; color: {{ $kpi['color'] }}; font-weight: 600; line-height: 1; margin-bottom: 0.5rem;">
                    {{ $kpi['value'] }}
                </div>

                <div style="font-size: 0.65rem; color: var(--text-2); background: rgba(255,255,255,0.03); padding: 0.25rem 0.5rem; border-radius: 6px; display: inline-block;">
                    {{ $kpi['trend'] }}
                </div>
            </div>

        </div>
    </div>
    @endforeach
</div>

{{-- Charts Row 1 --}}
<div class="row g-3 mb-4">
    {{-- Tenant Growth --}}
    <div class="col-lg-8 fade-up s2">
        <div class="chart-card">
            <div class="sec-hdr">
                <div>
                    <div class="chart-title">Tenant Growth</div>
                    <div class="chart-sub">New salons joined per month (last 12 months)</div>
                </div>
            </div>
            <div style="height:240px;"><canvas id="tenantGrowthChart"></canvas></div>
        </div>
    </div>

    {{-- Plan Distribution --}}
    <div class="col-lg-4 fade-up s3">
        <div class="chart-card" style="height:100%;">
            <div class="sec-hdr">
                <div>
                    <div class="chart-title">Plan Distribution</div>
                    <div class="chart-sub">Tenants by subscription plan</div>
                </div>
            </div>
            <div style="height:180px;"><canvas id="planChart"></canvas></div>
            <div style="margin-top:1rem;">
                @foreach($planDistribution as $plan => $count)
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div style="width:8px;height:8px;border-radius:50%;background:
                           {{ $plan==='premium'?'var(--gold)':($plan==='basic'?'var(--purple)':'var(--teal-light)') }};"></div>
                        <span style="font-size:.75rem;color:var(--text-2);text-transform:capitalize;">{{ $plan }}</span>
                    </div>
                    <span style="font-size:.75rem;color:var(--text);font-weight:500;">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Charts Row 2 --}}
<div class="row g-3 mb-4">
    {{-- Revenue Growth --}}
    <div class="col-lg-6 fade-up s2">
        <div class="chart-card">
            <div class="sec-hdr">
                <div>
                    <div class="chart-title">Revenue Trend</div>
                    <div class="chart-sub">Monthly platform revenue (last 12 months)</div>
                </div>
            </div>
            <div style="height:220px;"><canvas id="revenueChart"></canvas></div>
        </div>
    </div>

    {{-- Booking Status --}}
    <div class="col-lg-3 fade-up s3">
        <div class="chart-card" style="height:100%;">
            <div class="chart-title" style="margin-bottom:.25rem;">Booking Status</div>
            <div class="chart-sub" style="margin-bottom:1rem;">Current period</div>
            <div style="height:160px;"><canvas id="statusChart"></canvas></div>
        </div>
    </div>

    {{-- Daily Bookings --}}
    <div class="col-lg-3 fade-up s4">
        <div class="chart-card" style="height:100%;">
            <div class="chart-title" style="margin-bottom:.25rem;">Daily Bookings</div>
            <div class="chart-sub" style="margin-bottom:1rem;">Last 30 days</div>
            <div style="height:160px;"><canvas id="dailyChart"></canvas></div>
        </div>
    </div>
</div>

{{-- Top Parlours --}}
<div class="card-lux p-0 fade-up s4">
    <div class="p-4 pb-2 sec-hdr border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
        <div>
            <h3 class="sec-title" style="color: var(--gold);">Top Parlours by Revenue</h3>
            <p class="sec-sub">Highest earning tenants this period</p>
        </div>
    </div>
    <div class="lux-table-wrapper lux-scroller" style="overflow-x:auto;">
        <table class="lux-table mb-0">
            <thead style="background: rgba(0,0,0,0.2);">
                <tr>
                    <th style="padding-left: 1.5rem; width: 60px;">Rank</th>
                    <th>Parlour</th>
                    <th>Plan</th>
                    <th>Bookings</th>
                    <th>Revenue</th>
                    <th style="padding-right: 1.5rem;">Avg. Per Booking</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topParlours as $i => $p)
                <tr style="transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                    <td style="padding-left: 1.5rem;">
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $i === 0 ? 'var(--gold-dim)' : 'rgba(255,255,255,0.05)' }}; color: {{ $i === 0 ? 'var(--gold)' : 'var(--text-3)' }}; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; border: 1px solid {{ $i === 0 ? 'rgba(201,169,110,0.3)' : 'transparent' }};">
                            #{{ $i + 1 }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size:.82rem;font-weight: 600; color:var(--text);">{{ $p['name'] }}</div>
                        <div style="font-size:.65rem;color:var(--text-3);"><i class="bi bi-link-45deg"></i> {{ $p['subdomain'] }}</div>
                    </td>
                    <td>
                        <span class="plan-badge plan-{{ $p['plan'] }}" style="padding: 0.2rem 0.6rem; font-size: 0.65rem;">{{ ucfirst($p['plan']) }}</span>
                    </td>
                    <td><span style="font-weight: 500; color: var(--text-2);">{{ number_format($p['bookings']) }}</span></td>
                    <td style="color:var(--gold); font-weight: 600; font-family: var(--ff-display); font-size: 1rem;">₹{{ number_format($p['revenue'], 0, '.', ',') }}</td>
                    <td style="color:var(--text-3); font-size: 0.8rem; padding-right: 1.5rem;">
                        ₹{{ $p['bookings'] > 0 ? number_format($p['revenue'] / $p['bookings'], 0) : '0' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--text-3);padding:3rem;">
                        <i class="bi bi-building-slash" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                        No data available at the moment.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const CHART_COLORS = {
        gold: '#c9a96e'
        , emerald: '#10b981'
        , purple: '#8b5cf6'
        , teal: '#3a9e8d'
        , amber: '#f59e0b'
        , rose: '#f43f5e'
    };
    const CHART_GRID = 'rgba(255,255,255,0.04)';
    const CHART_TT = {
        backgroundColor: '#1a1a24'
        , borderColor: 'rgba(255,255,255,0.1)'
        , borderWidth: 1
    };

    const tgData = @json($tenantGrowth);
    new Chart(document.getElementById('tenantGrowthChart'), {
        type: 'bar'
        , data: {
            labels: Object.keys(tgData)
            , datasets: [{
                label: 'New Tenants'
                , data: Object.values(tgData)
                , backgroundColor: CHART_COLORS.gold + '55'
                , borderColor: CHART_COLORS.gold
                , borderWidth: 1
                , borderRadius: 4
            , }]
        }
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
                        color: 'rgba(255,255,255,0.3)'
                        , font: {
                            size: 10
                        }
                    }
                }
                , y: {
                    grid: {
                        color: CHART_GRID
                    }
                    , ticks: {
                        stepSize: 1
                        , color: 'rgba(255,255,255,0.3)'
                    }
                }
            , }
        }
    });

    const planData = @json($planDistribution);
    new Chart(document.getElementById('planChart'), {
        type: 'doughnut'
        , data: {
            labels: Object.keys(planData).map(p => p.charAt(0).toUpperCase() + p.slice(1))
            , datasets: [{
                data: Object.values(planData)
                , backgroundColor: [CHART_COLORS.teal + 'cc', CHART_COLORS.purple + 'cc', CHART_COLORS.gold + 'cc']
                , borderColor: '#111116'
                , borderWidth: 2
            , }]
        }
        , options: {
            responsive: true
            , maintainAspectRatio: false
            , cutout: '65%'
            , plugins: {
                legend: {
                    display: false
                }
                , tooltip: CHART_TT
            }
        }
    });

    const revData = @json($revenueGrowth);
    new Chart(document.getElementById('revenueChart'), {
        type: 'line'
        , data: {
            labels: Object.keys(revData)
            , datasets: [{
                label: 'Revenue ₹'
                , data: Object.values(revData)
                , borderColor: CHART_COLORS.emerald
                , backgroundColor: CHART_COLORS.emerald + '18'
                , borderWidth: 2
                , fill: true
                , tension: 0.4
                , pointBackgroundColor: CHART_COLORS.emerald
                , pointRadius: 3
            , }]
        }
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
                }
                , y: {
                    grid: {
                        color: CHART_GRID
                    }
                    , ticks: {
                        callback: v => '₹' + v.toLocaleString('en-IN')
                    }
                }
            , }
        }
    });

    // ✅ YEH DAALO — key-based approach, label aur color saath map hote hain
    const statusData = @json($bookingStatus);

    const STATUS_MAP = {
        pending: {
            label: 'Pending'
            , color: CHART_COLORS.amber
        }
        , confirmed: {
            label: 'Confirmed'
            , color: CHART_COLORS.teal
        }
        , checked_in: {
            label: 'Check In'
            , color: CHART_COLORS.gold
        }
        , completed: {
            label: 'Completed'
            , color: CHART_COLORS.emerald
        }
        , cancelled: {
            label: 'Cancelled'
            , color: CHART_COLORS.rose
        }
        , no_show: {
            label: 'No Show'
            , color: CHART_COLORS.purple
        }
    , };

    const statusKeys = Object.keys(statusData);
    const statusLabels = statusKeys.map(k => STATUS_MAP[k] ? .label ?? k);
    const statusBg = statusKeys.map(k => (STATUS_MAP[k] ? .color ?? CHART_COLORS.gold) + 'cc');
    const statusValues = statusKeys.map(k => statusData[k]);

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut'
        , data: {
            labels: statusLabels
            , datasets: [{
                data: statusValues
                , backgroundColor: statusBg
                , borderColor: '#111116'
                , borderWidth: 2
            , }]
        }
        , options: {
            responsive: true
            , maintainAspectRatio: false
            , cutout: '55%'
            , plugins: {
                legend: {
                    display: false
                }
                , tooltip: CHART_TT
            }
        }
    });

    const dailyData = @json($dailyBookings);
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar'
        , data: {
            labels: Object.keys(dailyData)
            , datasets: [{
                data: Object.values(dailyData)
                , backgroundColor: CHART_COLORS.purple + '66'
                , borderColor: CHART_COLORS.purple
                , borderWidth: 1
                , borderRadius: 2
            , }]
        }
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
                    display: false
                }
                , y: {
                    grid: {
                        color: CHART_GRID
                    }
                    , ticks: {
                        stepSize: 1
                    }
                }
            , }
        }
    });

</script>
@endpush
