@extends('layouts.superadmin')

@section('title', 'Platform Revenue')
@section('page-title', 'Revenue')
@section('page-sub', 'Subscription & booking revenue across all tenants')

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- Filters --}}
<form method="GET" action="{{ route('superadmin.revenue') }}" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;margin-bottom:1.8rem;" class="fade-up s1">

    <div>
        <label class="lux-label">Year</label>
        <div style="position: relative;">
            <select name="year" class="lux-input" style="width:110px; padding-right: 2rem; color-scheme: dark; background: var(--bg-input); color: var(--text);" onchange="this.form.submit()">
                @foreach($years as $y)
                <option value="{{ $y }}" style="background: var(--bg-card); color: var(--text);" {{ $year == $y ? 'selected':'' }}>{{ $y }}</option>
                @endforeach
            </select>
            <div style="position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
            </div>
        </div>
    </div>

    <div>
        <label class="lux-label">Month</label>
        <div style="position: relative;">
            <select name="month" class="lux-input" style="width:140px; padding-right: 2rem; color-scheme: dark; background: var(--bg-input); color: var(--text);" onchange="this.form.submit()">
                <option value="all" style="background: var(--bg-card); color: var(--text);" {{ $month==='all'?'selected':'' }}>All Months</option>
                @foreach(range(1,12) as $m)
                <option value="{{ $m }}" style="background: var(--bg-card); color: var(--text);" {{ $month == $m ? 'selected':'' }}>
                    {{ \Carbon\Carbon::create(null,$m)->format('F') }}
                </option>
                @endforeach
            </select>
            <div style="position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-lux-gold btn-sm mb-1"><i class="bi bi-funnel"></i> Filter</button>
    <a href="{{ route('superadmin.revenue') }}" class="btn-lux-ghost btn-sm faint border-0 mb-1">Reset</a>
</form>

{{-- KPI Row --}}
<div class="row g-3 mb-4">
    @php
    $growthClass = $stats['revenue_growth'] >= 0 ? 'trend-up' : 'trend-down';
    $growthIcon = $stats['revenue_growth'] >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
    @endphp

    <div class="col-lg-3 col-md-6 fade-up s1">
        <div class="card-lux kpi-pad gold-border h-100" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="kpi-icon-abs" style="background:var(--gold-dim);color:var(--gold);">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <div class="kpi-label" style="padding-right: 35px;">Total Revenue</div>
            <div class="kpi-value" style="color:var(--gold); font-size:1.8rem; margin-bottom:0.5rem;">₹{{ number_format($stats['total_revenue'],0,'.',',') }}</div>
            <div>
                <span class="kpi-trend {{ $growthClass }}" style="font-size: 0.65rem;">
                    <i class="bi {{ $growthIcon }}"></i> {{ abs($stats['revenue_growth']) }}% YoY
                </span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 fade-up s2">
        <div class="card-lux kpi-pad h-100" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="kpi-icon-abs" style="background:var(--emerald-dim);color:var(--emerald);">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="kpi-label" style="padding-right: 35px;">Total Bookings</div>
            <div class="kpi-value" style="color:var(--emerald); font-size:1.8rem; margin-bottom:0;">{{ number_format($stats['total_bookings']) }}</div>
            <div style="font-size:.65rem;color:var(--text-3);margin-top:.3rem;">Completed appointments</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 fade-up s3">
        <div class="card-lux kpi-pad h-100" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="kpi-icon-abs" style="background:var(--purple-dim);color:var(--purple);">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="kpi-label" style="padding-right: 35px;">Average Booking Value</div>
            <div class="kpi-value" style="color:var(--purple); font-size:1.8rem; margin-bottom:0;">₹{{ number_format($stats['avg_booking_value'],0) }}</div>
            <div style="font-size:.65rem;color:var(--text-3);margin-top:.3rem;">Per completed booking</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 fade-up s4">
        <div class="card-lux kpi-pad h-100" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div class="kpi-icon-abs" style="background:var(--teal-dim);color:var(--teal-light);">
                <i class="bi bi-buildings-fill"></i>
            </div>
            <div class="kpi-label" style="padding-right: 35px;">Active Tenants</div>
            <div class="kpi-value" style="color:var(--teal-light); font-size:1.8rem; margin-bottom:0;">{{ $tenantRevenue->count() }}</div>
            <div style="font-size:.65rem;color:var(--text-3);margin-top:.3rem;">of {{ $stats['active_tenants'] }} total</div>
        </div>
    </div>
</div>

{{-- Revenue Chart + Plan Breakdown --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8 fade-up s2">
        <div class="card-lux p-4 h-100">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <div>
                    <h3 class="serif" style="font-size: 1.2rem; margin-bottom:0;">Monthly Revenue — {{ $year }}</h3>
                    <p class="faint" style="font-size: 0.65rem; margin-bottom:0;">Booking revenue per month</p>
                </div>
            </div>
            <div style="height:260px; position:relative; width:100%;"><canvas id="monthlyRevChart"></canvas></div>
        </div>
    </div>

    <div class="col-lg-4 fade-up s3">
        <div class="card-lux p-4" style="height:100%;">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1.5rem;">
                <h3 class="serif" style="font-size: 1.2rem; margin-bottom:0;">Plan-wise Revenue</h3>
            </div>

            @forelse($planRevenue as $plan => $data)
            @php
            $total = $stats['total_revenue'] ?: 1;
            $pct = round(($data['revenue'] / $total) * 100, 1);
            $color = match(strtolower($plan)) {
            'premium' => 'var(--gold)',
            'basic' => 'var(--purple)',
            'free' => 'var(--teal-light)',
            default => 'var(--text-3)'
            };
            @endphp
            <div style="margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.4rem; align-items: flex-end;">
                    <span style="font-size:.75rem;color:var(--text-2);text-transform:capitalize;">
                        {{ $plan }} <span class="faint" style="font-size: 0.65rem;">({{ $data['count'] }} tenants)</span>
                    </span>
                    <span style="font-size:.85rem; font-weight: 500; color:{{ $color }};">
                        ₹{{ number_format($data['revenue'],0,'.',',') }}
                    </span>
                </div>
                <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $pct }}%; background: {{ $color }}; border-radius: 3px; transition: width 1s ease;"></div>
                </div>
                <div style="font-size:.65rem;color:var(--text-3);margin-top:.4rem;">{{ $pct }}% of total</div>
            </div>
            @empty
            <div style="text-align: center; padding: 2rem 0;">
                <p class="muted" style="font-size: 0.75rem;">No revenue data available for plans.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Tenant-wise Revenue Table --}}
<div class="card-lux p-0 fade-up s4">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-4); border-bottom: 1px solid var(--border);">
        <div>
            <h3 class="serif" style="font-size: 1.1rem; margin-bottom:0;">Tenant-wise Revenue</h3>
            <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">{{ $month === 'all' ? $year : \Carbon\Carbon::create(null, $month)->format('F').' '.$year }}</p>
        </div>
        <span class="status-badge" style="background: var(--gold-dim); color: var(--gold);">{{ $tenantRevenue->count() }} tenants</span>
    </div>
    <div class="lux-table-wrapper">
        <table class="lux-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Parlour</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Bookings</th>
                    <th>Revenue</th>
                    <th>Avg / Booking</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenantRevenue as $i => $t)
                <tr>
                    <td class="faint" style="font-size:.7rem;">{{ $i+1 }}</td>
                    <td>
                        <div style="font-weight: 500; color:var(--text);">{{ $t['name'] }}</div>
                        <div class="faint" style="font-size:.65rem; font-family: monospace;">{{ $t['subdomain'] }}.lumiere.app</div>
                    </td>
                    <td><span class="plan-badge plan-{{ strtolower($t['plan']) }}">{{ ucfirst($t['plan']) }}</span></td>
                    <td>
                        <span class="status-badge {{ match(strtolower($t['status'])) { 'active' => 'badge-active', 'suspended' => 'badge-suspended', default => 'badge-inactive' } }}">
                            {{ ucfirst($t['status']) }}
                        </span>
                    </td>
                    <td style="color: var(--text-2);">{{ number_format($t['bookings']) }}</td>
                    <td style="color:var(--gold);font-weight:500;">₹{{ number_format($t['revenue'],0,'.',',') }}</td>
                    <td style="color: var(--text-2);">₹{{ number_format($t['avg'],0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--text-3);padding:3rem 1rem;">
                        <i class="bi bi-wallet2 faint d-block mb-2" style="font-size:1.5rem;"></i>
                        <span class="muted">No revenue data available for this period.</span>
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
    document.addEventListener('DOMContentLoaded', function() {
        const CHART_COLORS = {
            gold: '#c9a96e'
            , emerald: '#10b981'
            , purple: '#8b5cf6'
            , teal: '#3a9e8d'
        };
        const CHART_GRID = 'rgba(255,255,255,0.04)';
        const CHART_TT = {
            backgroundColor: '#1a1a24'
            , borderColor: 'rgba(255,255,255,0.1)'
            , borderWidth: 1
        };

        const mRevRaw = @json($monthlyRevenue ?? []);
        const mRevLabels = Object.keys(mRevRaw).length > 0 ? Object.keys(mRevRaw) : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const mRevData = Object.values(mRevRaw).length > 0 ? Object.values(mRevRaw) : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        const ctx = document.getElementById('monthlyRevChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'bar'
                , data: {
                    labels: mRevLabels
                    , datasets: [{
                        label: 'Revenue ₹'
                        , data: mRevData
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
                                color: 'rgba(255,255,255,0.5)'
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
                                color: 'rgba(255,255,255,0.5)'
                                , font: {
                                    size: 10
                                }
                                , callback: v => '₹' + v.toLocaleString('en-IN')
                            }
                        }
                    , }
                }
            });
        }
    });

</script>
@endpush
