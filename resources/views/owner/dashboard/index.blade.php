@extends('layouts.owner')

@section('title', 'Dashboard Gateway')
@section('page-title', 'Overview Terminal')
@section('breadcrumb', 'Workspace / Dashboard')

@section('topbar-actions')
<a href="{{ route('owner.appointments.create') }}" class="btn-lux-gold btn-sm">
    <i class="bi bi-plus-lg" aria-hidden="true"></i> New Booking Entry
</a>
@endsection

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

<!-- Premium Strategic Brand Hero Context Component Frame -->
<section class="card-lux p-3 mb-3 fade-up" aria-labelledby="hero-greeting" style="background: linear-gradient(135deg, var(--gold-glow-2), transparent 70%); border-left: 3px solid var(--gold);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-3);">
        <div>
            <h2 class="serif gold-text" style="font-size: 1.6rem; font-weight: 400; margin-bottom: 0.2rem;" id="hero-greeting">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
                <span style="color: var(--text);">{{ explode(' ', auth()->user()->name)[0] }}</span> ✦
            </h2>
            <p style="font-size: var(--text-xs); color: var(--text-3); margin-bottom: 0;">
                {{ auth()->user()->tenant?->name ?? 'System' }} Gateway Panel · Operational Execution Scope Log
            </p>
        </div>

        <!-- Live Action Telemetry Tags Framework -->
        <div style="display: flex; flex-wrap: wrap; gap: var(--space-2);">
            <span class="trend-up" style="font-size: 0.65rem; padding: 0.2rem 0.6rem; border-radius: var(--r-pill); display: inline-flex; align-items: center; gap: var(--space-2);">
                <span class="live-dot"></span>
                {{ $stats['upcoming_today'] ?? 0 }} Upcoming Today
            </span>
            <span class="trend-flat" style="font-size: 0.65rem; padding: 0.2rem 0.6rem; border-radius: var(--r-pill);">
                ₹{{ number_format($stats['month_revenue'] ?? 0) }} This Month
            </span>
            @if(isset($stats['low_stock_alerts']) && $stats['low_stock_alerts'] > 0)
            <span class="trend-down" style="font-size: 0.65rem; padding: 0.2rem 0.6rem; border-radius: var(--r-pill);">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $stats['low_stock_alerts'] }} Deficit Items
            </span>
            @endif
        </div>
    </div>
</section>

<!-- Financial & Asset Telemetry Grid Panel Row -->
<div class="row g-3 mb-4 fade-up s1">
    @php
    $kpis = [
    ['label'=>"Today's Registry", 'val'=>$stats['today_bookings']??0, 'color'=>'var(--emerald)', 'bg'=>'var(--emerald-dim)', 'icon'=>'bi-calendar-check', 'trend'=>($stats['upcoming_today']??0).' slots active', 'id'=>'sp1'],
    ['label'=>'Monthly Gross Value', 'val'=>'₹'.number_format($stats['top_revenue'] ?? $stats['month_revenue'] ?? 0), 'color'=>'var(--gold)', 'bg'=>'var(--gold-dim)', 'icon'=>'bi-currency-rupee', 'trend'=>'This Month Cycle', 'id'=>'sp2'],
    ['label'=>'Active Customer Base', 'val'=>$stats['total_customers']??0, 'color'=>'var(--purple)', 'bg'=>'var(--purple-dim)', 'icon'=>'bi-people', 'trend'=>'Registered Accounts', 'id'=>'sp3'],
    ['label'=>'Deficit Materials', 'val'=>$stats['low_stock_alerts']??0, 'color'=>'var(--amber)', 'bg'=>'var(--amber-dim)', 'icon'=>'bi-box-seam', 'trend'=>'Items Alert Level', 'id'=>'sp4'],
    ['label'=>'Pending Reviews', 'val'=>$stats['pending_reviews']??0, 'color'=>'var(--teal)', 'bg'=>'var(--teal-dim)', 'icon'=>'bi-star-half', 'trend'=>'Awaiting Approval', 'id'=>'sp5'],
    ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="col-6 col-xl-3">
        <div class="card-lux p-3 h-100" style="position: relative; display: flex; flex-direction: column;">
            <div style="position:absolute; top:1rem; right:1rem; width:30px; height:30px; border-radius:8px; background:{{$kpi['bg']}}; color:{{$kpi['color']}}; display:flex; align-items:center; justify-content:center; font-size:0.85rem;">
                <i class="bi {{$kpi['icon']}}"></i>
            </div>

            <div style="font-size:0.65rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-3); margin-bottom:0.2rem; padding-right: 40px; white-space: normal; line-height: 1.3;">
                {{$kpi['label']}}
            </div>

            <div style="font-family:var(--ff-display); font-size:1.8rem; line-height:1; color:{{$kpi['color']}}; margin-bottom:0.3rem;">
                {{$kpi['val']}}
            </div>

            <div style="font-size:0.65rem; color:var(--text-3); margin-bottom:1rem;">
                {{$kpi['trend']}}
            </div>

            <div style="position: relative; height:35px; width:100%; margin-top:auto;">
                <canvas id="{{$kpi['id']}}"></canvas>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Real-time Chart Optimization Layer Engine Blocks Matrix -->
<div class="row g-3 mb-4 fade-up s2">
    <div class="col-12 col-lg-8">
        <div class="card-lux p-3 h-100">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom:0;">Revenue Overview</h3>
                <p style="font-size: 0.65rem; color: var(--text-3); margin-bottom:0;">Chronological breakdown of gross generated payout streams.</p>
            </div>
            <div style="position: relative; height: 200px; width: 100%;">
                <canvas id="revChart" aria-label="Monthly revenue visualization chart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card-lux p-3 h-100 d-flex flex-column justify-content-between">
            <div>
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                    <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom:0;">Top Performance Services</h3>
                    <p style="font-size: 0.65rem; color: var(--text-3); margin-bottom:0;">Categorized via operational checkout volumes.</p>
                </div>
                <div style="position: relative; height: 130px; width: 100%; margin: 0 auto;">
                    <canvas id="servChart" aria-label="Top performance treatments category chart"></canvas>
                </div>
            </div>

            <table class="lux-table mt-auto" style="margin-top: 1rem;">
                <tbody>
                    @forelse($topServices ?? [] as $svc)
                    <tr>
                        <td style="font-size: 0.75rem; padding: 0.3rem 0; border: none; color: var(--text-2);">{{ $svc['service_name'] ?? $svc['name'] }}</td>
                        <td style="text-align: right; font-size: 0.75rem; color: var(--gold); font-weight: 500; padding: 0.3rem 0; border: none;">{{ $svc['total_bookings'] ?? $svc['total'] }} checks</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" style="font-size: 0.75rem; padding: 0.3rem 0; border: none; color: var(--text-3); text-align: center;">No services booked yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Dashboard Lower Operations Management Logs Terminal -->
<div class="row g-3 fade-up s3">
    <div class="col-12 col-xl-4">
        <div class="card-lux h-100 d-flex flex-column">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border);">
                <div>
                    <h3 class="serif" style="font-size: 1.1rem; margin-bottom:0;">Today's Schedule</h3>
                    <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">{{ now()->format('d M Y') }} timeline</p>
                </div>
                <a href="{{ route('owner.appointments.today') }}" class="btn-lux-ghost btn-sm border-0">View System</a>
            </div>

            {{-- ADDED lux-scroller HERE --}}
            <div class="lux-scroller" style="overflow-y: auto; flex: 1 1 auto; max-height: 280px; padding-right: 5px;">
                @forelse($todayAppointments ?? [] as $appt)
                <div style="display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.02);">
                    <div style="font-family: monospace; font-size: 0.7rem; color: var(--gold); font-weight: bold;">
                        {{ \Carbon\Carbon::parse($appt->start_time)->format('h:i A') }}
                    </div>
                    <div class="user-chip-av" style="width: 28px; height: 28px; font-size: 0.65rem; background: var(--bg-2); border: 1px solid var(--border); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        {{ strtoupper(substr($appt->customer?->name ?? 'C', 0, 2)) }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.8rem; font-weight: 500; color: var(--text);" class="truncate">{{ $appt->customer?->name ?? 'Walk-in Client' }}</div>
                        <div style="font-size: 0.65rem; color: var(--text-3);" class="truncate">{{ $appt->service?->name }} · {{ $appt->staff?->user?->name }}</div>
                    </div>
                    <span class="status-badge {{ $appt->status === 'completed' ? 'badge-active' : ($appt->status === 'cancelled' ? 'badge-suspended' : 'badge-trial') }} px-2 py-1" style="font-size: 0.6rem;">
                        {{ ucfirst($appt->status) }}
                    </span>
                </div>
                @empty
                <div style="padding: 3rem 1rem; text-align: center;">
                    <i class="bi bi-calendar-x faint" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    <p style="font-size: 0.75rem; color: var(--text-3); margin:0;">No active appointment logs for today.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-4">
        <div class="card-lux p-3 h-100">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <div>
                    <h3 class="serif" style="font-size: 1.1rem; margin-bottom:0;">Staff Performance</h3>
                    <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">Completed checkouts this month.</p>
                </div>
                <a href="{{ route('owner.staff.index') }}" class="btn-lux-ghost btn-sm border-0">Details</a>
            </div>

            {{-- ADDED lux-scroller HERE --}}
            <div class="lux-scroller" style="overflow-y: auto; max-height: 250px; display: flex; flex-direction: column; gap: 0.8rem; padding-right: 5px;">
                @php $maxCompleted = collect($staffPerformance ?? [])->max('completed') ?: 1; @endphp
                @forelse($staffPerformance ?? [] as $s)
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; margin-bottom: 0.3rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0;">
                            <div style="width: 20px; height: 20px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-2); display: flex; align-items: center; justify-content: center; font-size: 9px; color: var(--text-2);">
                                {{ $s['initials'] }}
                            </div>
                            <span style="font-weight: 500; color: var(--text-2);" class="truncate">{{ $s['name'] }}</span>
                        </div>
                        <span style="color: var(--gold); font-weight: 600;">{{ $s['completed'] }} jobs</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.04); border-radius: var(--r-pill); overflow: hidden;">
                        <div style="height: 100%; background: var(--gold-grad); width: {{ round(($s['completed'] / $maxCompleted) * 100) }}%; border-radius: var(--r-pill); transition: width 0.4s ease;"></div>
                    </div>
                </div>
                @empty
                <p style="font-size: 0.75rem; color: var(--text-3); text-align: center; padding: 2rem 0;">Performance matrix is empty.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-4">
        <div class="card-lux p-3 h-100 d-flex flex-column gap-3">
            {{-- ADDED lux-scroller HERE --}}
            <div class="lux-scroller" style="flex: 1; overflow-y: auto; max-height: 180px; padding-right: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 0.8rem;">
                    <div>
                        <h3 class="serif" style="font-size: 1.1rem; margin-bottom:0;">Deficit Materials</h3>
                        <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">Critical restock threshold items.</p>
                    </div>
                    <a href="{{ route('owner.inventory.index') }}" class="btn-lux-ghost btn-sm border-0">Replenish</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                    @forelse($lowStockProducts ?? [] as $prod)
                    <div style="display: flex; align-items: center; gap: 0.8rem; padding-bottom: 0.6rem; border-bottom: 1px solid rgba(255,255,255,0.02);">
                        <div style="width: 28px; height: 28px; border-radius: 6px; background: var(--rose-dim); color: var(--rose); display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 0.75rem; font-weight: 500; color: var(--text);" class="truncate">{{ $prod->name }}</div>
                            <div style="font-size: 0.65rem; color: var(--rose);" class="truncate">{{ $prod->quantity }} remaining · limit {{ $prod->low_stock_threshold }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 1.5rem 0;">
                        <i class="bi bi-check2-circle" style="color: var(--emerald); font-size: 1.5rem;"></i>
                        <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.3rem;">All lines stocked inside optimal ranges.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            @if(isset($stats['pending_commissions']) && (float)filter_var($stats['pending_commissions'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) > 0)
            <div style="background: var(--gold-dim); border: 1px solid rgba(201,169,110,0.15); border-radius: var(--r-md); padding: 1rem; margin-top: auto;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h4 class="label-caps" style="color: var(--text-3); margin-bottom: 0;">Pending Commissions</h4>
                        <p style="font-size: 0.65rem; color: var(--text-3); margin-top: 0.1rem; margin-bottom: 0;">Awaiting staff settlement.</p>
                    </div>
                    <i class="bi bi-wallet2" style="color: var(--gold); font-size: 1.1rem;"></i>
                </div>
                <div class="serif" style="font-size: 1.6rem; color: var(--gold); margin-top: 0.3rem; line-height: 1;">
                    {{ $stats['pending_commissions'] }}
                </div>
                <a href="{{ route('owner.commissions.index') }}" class="btn-lux-gold btn-sm" style="width: 100%; margin-top: 0.8rem; font-size: 0.7rem; justify-content: center;">
                    <i class="bi bi-cash-stack"></i> Disburse Payouts
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Premium Scroller for internal dashboard cards */
    .lux-scroller::-webkit-scrollbar {
        width: 4px;
    }

    .lux-scroller::-webkit-scrollbar-track {
        background: transparent;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(201, 169, 110, 0.3);
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb:hover {
        background: var(--gold);
    }

</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const colors = {
            gold: '#c9a96e'
            , teal: '#2d7d6f'
            , purple: '#8b5cf6'
            , emerald: '#10b981'
            , amber: '#f59e0b'
        };
        const gridColor = 'rgba(255,255,255,0.04)';

        // ==========================================
        // MINI SPARKLINE GENERATOR (Fix for blank KPI cards)
        // ==========================================
        function createMiniSparkline(canvasId, dataArr, colorHex) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;

            const maxVal = Math.max(...dataArr);
            const minVal = Math.min(...dataArr);

            new Chart(ctx.getContext('2d'), {
                type: 'line'
                , data: {
                    labels: ['A', 'B', 'C']
                    , datasets: [{
                        data: dataArr
                        , borderColor: colorHex
                        , backgroundColor: colorHex + '20', // added light fill for premium look
                        borderWidth: 2
                        , tension: 0.3
                        , pointRadius: 0
                        , fill: true
                    }]
                }
                , options: {
                    responsive: true
                    , maintainAspectRatio: false
                    , plugins: {
                        legend: {
                            display: false
                        }
                        , tooltip: {
                            enabled: false
                        }
                    }
                    , scales: {
                        x: {
                            display: false
                        }
                        , y: {
                            display: false
                            , min: minVal === 0 ? 0 : minVal * 0.8
                            , suggestedMax: maxVal === 0 ? 10 : maxVal * 1.1
                        }
                    }
                    , layout: {
                        padding: 0
                    }
                }
            });
        }

        // Apply sparklines
        const d1 = parseInt("{{ $stats['today_bookings'] ?? 0 }}") || 0;
        const d2 = parseInt("{{ preg_replace('/[^0-9]/', '', $stats['top_revenue'] ?? $stats['month_revenue'] ?? 0) }}") || 0;
        const d3 = parseInt("{{ $stats['total_customers'] ?? 0 }}") || 0;
        const d4 = parseInt("{{ $stats['low_stock_alerts'] ?? 0 }}") || 0;

        const sparklines = @json($sparklines);

        const sp1Data = sparklines.bookings.length === 3 ? sparklines.bookings : [0, 0, d1];
        const sp2Data = sparklines.revenue.length === 3 ? sparklines.revenue : [0, 0, d2];
        const sp3Data = sparklines.customers.length === 3 ? sparklines.customers : [0, 0, d3];
        const sp4Data = sparklines.lowstock.length === 3 ? sparklines.lowstock : [0, 0, d4];
        const d5 = parseInt("{{ $stats['pending_reviews'] ?? 0 }}") || 0;
        const sp5Data = sparklines.reviews.length === 3 ? sparklines.reviews : [0, 0, d5];

        createMiniSparkline('sp1', sp1Data, colors.emerald);
        createMiniSparkline('sp2', sp2Data, colors.gold);
        createMiniSparkline('sp3', sp3Data, colors.purple);
        createMiniSparkline('sp4', sp4Data, colors.amber);
        createMiniSparkline('sp5', sp5Data, colors.teal);

        // ==========================================
        // MAIN CHARTS
        // ==========================================
        const revRaw = @json($monthlyRevenue ? ? []);
        const svcRaw = @json($topServices ? ? []);

        // Safe Mapping
        const revLabels = (Array.isArray(revRaw) && revRaw.length > 0) ? revRaw.map(r => r.month) : Object.keys(revRaw).length > 0 ? Object.keys(revRaw) : ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
        const revValues = (Array.isArray(revRaw) && revRaw.length > 0) ? revRaw.map(r => r.revenue) : Object.values(revRaw).length > 0 ? Object.values(revRaw) : [0, 0, 0, 0, 0];

        const svcLabels = Array.isArray(svcRaw) && svcRaw.length > 0 ? svcRaw.map(s => s.service_name || s.name) : ['No Services'];
        const svcValues = Array.isArray(svcRaw) && svcRaw.length > 0 ? svcRaw.map(s => s.total_bookings || s.total) : [1]; // 1 is fallback to draw empty doughnut

        const revCtx = document.getElementById('revChart');
        if (revCtx) {
            new Chart(revCtx.getContext('2d'), {
                type: 'bar'
                , data: {
                    labels: revLabels
                    , datasets: [{
                        label: 'Revenue ₹'
                        , data: revValues
                        , backgroundColor: colors.gold + '55'
                        , borderColor: colors.gold
                        , borderWidth: 1
                        , borderRadius: 4
                    }]
                }
                , options: {
                    responsive: true
                    , maintainAspectRatio: false
                    , plugins: {
                        legend: {
                            display: false
                        }
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
                                color: gridColor
                            }
                            , ticks: {
                                color: 'rgba(255,255,255,0.42)'
                                , font: {
                                    size: 10
                                }
                                , callback: v => '₹' + v.toLocaleString('en-IN')
                            }
                        }
                    }
                }
            });
        }

        const svcCtx = document.getElementById('servChart');
        if (svcCtx) {
            new Chart(svcCtx.getContext('2d'), {
                type: 'doughnut'
                , data: {
                    labels: svcLabels
                    , datasets: [{
                        data: svcValues
                        , backgroundColor: svcRaw.length > 0 ? [colors.gold, colors.emerald, colors.purple, colors.teal] : ['rgba(255,255,255,0.05)']
                        , borderWidth: 0
                    }]
                }
                , options: {
                    responsive: true
                    , maintainAspectRatio: false
                    , cutout: '75%'
                    , plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });

</script>
@endpush
