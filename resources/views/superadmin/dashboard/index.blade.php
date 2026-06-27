@extends('layouts.superadmin')

@section('title', 'Platform Dashboard')
@section('page-title', 'Platform Overview')
@section('page-sub', 'Real-time SaaS metrics')

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- Custom Scrollbar Styles for Premium Look --}}
<style>
    .lux-scroller::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .lux-scroller::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb:hover {
        background: rgba(201, 169, 110, 0.5);
        /* Gold hover effect */
    }

</style>

{{-- Hero Section --}}
<section class="card-lux p-3 mb-3 fade-up" aria-label="Platform status" style="background: linear-gradient(135deg, var(--gold-glow-2), transparent 70%); border-left: 3px solid var(--gold);">
    <h2 class="serif" style="font-size: 1.5rem; font-weight: 400; color: var(--gold); margin-bottom: 0.2rem;">
        Platform Status — <em>Live</em>
    </h2>
    <p style="font-size: var(--text-xs); color: var(--text-3); margin-bottom: 0.8rem;">
        LUMIÈRE SaaS · <span id="live-clock"></span>
    </p>
    <div style="display:flex; flex-wrap:wrap; gap: var(--space-2);">
        <span class="user-chip" style="font-size: 0.65rem; padding: 0.2rem 0.6rem; border-radius: var(--r-pill); display:inline-flex; align-items:center; gap: var(--space-2);">
            <span class="live-dot" aria-hidden="true"></span>
            <span id="stat-active-salons">{{ $stats['active_tenants'] ?? 0 }} active salons</span>
        </span>
        {{-- <span id="stat-bookings-today" class="user-chip" style="font-size: 0.65rem; padding: 0.2rem 0.6rem; border-radius: var(--r-pill);">
    <i class="bi bi-calendar-check" aria-hidden="true"></i>
    {{ $stats['total_bookings_today'] ?? 0 }} bookings today
        </span> --}}
        <span id="stat-revenue-month" class="trend-up" style="font-size: 0.65rem; padding: 0.2rem 0.6rem; border-radius: var(--r-pill);">
            <i class="bi bi-currency-rupee" aria-hidden="true"></i>
            {{ number_format($stats['platform_revenue_month'] ?? 0) }} this month
        </span>
    </div>
</section>

{{-- KPI row --}}
<div class="row g-3 mb-4 fade-up s1">
    @php
    $kpis = [
    ['label'=>'Total Tenants', 'val'=>$stats['total_tenants']??0, 'color'=>'var(--gold)', 'bg'=>'var(--gold-dim)', 'icon'=>'bi-buildings-fill', 'trend'=>($stats['new_this_month']??0).' this month', 'id'=>'sp1'],
    ['label'=>'Active Salons', 'val'=>$stats['active_tenants']??0, 'color'=>'var(--emerald)', 'bg'=>'var(--emerald-dim)', 'icon'=>'bi-check-circle-fill', 'trend'=>round((($stats['active_tenants']??0)/(($stats['total_tenants']??1) ?: 1))*100).'% active', 'id'=>'sp2'],
    ['label'=>'Total Bookings', 'val'=>number_format($stats['total_bookings']??0), 'color'=>'var(--purple)', 'bg'=>'var(--purple-dim)', 'icon'=>'bi-calendar-check-fill', 'trend'=>($stats['total_bookings_today']??0).' today', 'id'=>'sp3'],
    ['label'=>'Pending Plans', 'val'=>$stats['trial_tenants']??0, 'color'=>'var(--amber)', 'bg'=>'var(--amber-dim)', 'icon'=>'bi-hourglass-split', 'trend'=>'Need upgrade', 'id'=>'sp4'],
    ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="col-6 col-lg-3">
        <div class="card-lux p-3 h-100" style="position: relative; display: flex; flex-direction: column;">
            <div style="position:absolute; top:1rem; right:1rem; width:30px; height:30px; border-radius:8px; background:{{$kpi['bg']}}; color:{{$kpi['color']}}; display:flex; align-items:center; justify-content:center; font-size:0.85rem;">
                <i class="bi {{$kpi['icon']}}"></i>
            </div>
            <div style="font-size:0.65rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-3); margin-bottom:0.2rem;">
                {{$kpi['label']}}
            </div>
            <div style="font-family:var(--ff-display); font-size:1.6rem; line-height:1; color:{{$kpi['color']}}; margin-bottom:0.3rem;">
                {{$kpi['val']}}
            </div>
            <div style="font-size:0.65rem; color:var(--text-3); margin-bottom:0.8rem;">
                {{$kpi['trend']}}
            </div>
            <div style="height:25px; width:100%; margin-top:auto;">
                <canvas id="{{$kpi['id']}}"></canvas>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts Matrix Layer --}}
<div class="row g-3 mb-4 fade-up s2">
    <div class="col-lg-8">
        <div class="card-lux p-3 h-100">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom:0;">Tenant Growth</h3>
                <p style="font-size: 0.65rem; color: var(--text-3); margin-bottom:0;">New registrations chronologically per month</p>
            </div>
            <div style="position: relative; height: 180px; width: 100%;">
                <canvas id="growthChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-lux p-3 h-100 d-flex flex-column">
            <div>
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                    <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom:0;">Plan Distribution</h3>
                    <p style="font-size: 0.65rem; color: var(--text-3); margin-bottom:0;">By active subscription tiers</p>
                </div>
                <div style="position: relative; height: 110px; width: 100%;">
                    <canvas id="planChart"></canvas>
                </div>
            </div>

            <table class="lux-table mt-auto" style="margin-top: 1rem;">
                <tbody>
                    @forelse($planDistribution ?? [] as $plan => $count)
                    <tr>
                        <td style="font-size: 0.75rem; padding: 0.3rem 0; border: none; color: var(--text-2);">{{ ucfirst($plan) }}</td>
                        <td style="text-align: right; font-size: 0.75rem; color: var(--gold); font-weight: 500; padding: 0.3rem 0; border: none;">{{ $count }} {{ Str::plural('salon', $count) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td style="font-size: 0.75rem; padding: 0.3rem 0; border: none; color: var(--text-3);">No active plans</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Bottom row --}}
<div class="row g-3 fade-up s3">
    <div class="col-lg-8">
        <div class="card-lux">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="border-color: var(--border) !important;">
                <div>
                    <h3 class="serif" style="font-size: 1.1rem; margin-bottom:0;">Recent Tenants</h3>
                    <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">Latest platform workspace registrations</p>
                </div>
                <a href="{{ route('superadmin.tenants.index') }}" class="btn-lux-ghost btn-sm border-0">View All</a>
            </div>

            {{-- YAHAN CHANGE KIYA HAI: lux-scroller add kiya gaya aur max-height 280px --}}
            <div class="lux-table-wrapper lux-scroller" style="max-height: 280px; overflow-y: auto; overflow-x: auto;">
                <table class="lux-table">
                    <thead>
                        <tr style="position: sticky; top: 0; background: var(--bg-card); z-index: 1;">
                            <th scope="col">Salon Workspace</th>
                            <th scope="col">Owner Profile</th>
                            <th scope="col">Plan</th>
                            <th scope="col">Status Gate</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTenants ?? [] as $t)
                        <tr>
                            <td>
                                <div style="font-weight: 500; color: var(--text);">{{ $t->name }}</div>
                                <div style="font-size: 0.65rem; color: var(--text-3); font-family: monospace;">{{ $t->subdomain }}.lumiere.app</div>
                            </td>
                            <td style="font-size: 0.8rem;">{{ $t->owner?->name ?? 'Unassigned Admin' }}</td>
                            <td><span class="plan-badge plan-{{ strtolower($t->plan) }}">{{ ucfirst($t->plan) }}</span></td>
                            <td>
                                <span class="status-badge {{ $t->status === 'active' ? 'badge-active' : ($t->status === 'suspended' ? 'badge-suspended' : 'badge-inactive') }}">
                                    {{ ucfirst($t->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('superadmin.tenants.show', $t->id) }}" class="btn-icon-action">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-3);">No tenants found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-lux p-3 h-100">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <h3 class="serif" style="font-size: 1.1rem; margin-bottom:0;">Platform Activity</h3>
                <p style="font-size: 0.65rem; color: var(--text-3); margin:0;">Real-time telemetry events</p>
            </div>

            {{-- YAHAN CHANGE KIYA HAI: lux-scroller add kiya hai consistency ke liye --}}
            <div class="lux-scroller" style="display: flex; flex-direction: column; gap: 0.8rem; overflow-y: auto; max-height: 280px; padding-right: 5px;">
                @forelse($recentActivity ?? [] as $activity)
                <div style="display: flex; align-items: flex-start; gap: 0.8rem; padding-bottom: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <div style="width: 28px; height: 28px; border-radius: 6px; background: {{ $activity['bg'] ?? 'var(--gold-dim)' }}; color: {{ $activity['color'] ?? 'var(--gold)' }}; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; flex-shrink:0;">
                        <i class="bi {{ $activity['icon'] ?? 'bi-bell' }}"></i>
                    </div>
                    <div>
                        <p style="font-size: 0.8rem; color: var(--text); margin: 0; line-height: 1.3;">{{ $activity['title'] ?? 'System Event' }}</p>
                        <p style="font-size: 0.65rem; color: var(--text-3); margin-top: 0.2rem;">{{ $activity['time'] ?? 'Just now' }}</p>
                    </div>
                </div>
                @empty
                <p style="text-align: center; color: var(--text-3); font-size: 0.8rem; padding: 2rem 0;">
                    No recent platform telemetry activities.
                </p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

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
        // MINI SPARKLINE GENERATOR (NO EXTERNAL DEPS)
        // ==========================================
        function createMiniSparkline(canvasId, dataArr, colorHex) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;
            new Chart(ctx.getContext('2d'), {
                type: 'line'
                , data: {
                    labels: ['A', 'B', 'C']
                    , datasets: [{
                        data: dataArr
                        , borderColor: colorHex
                        , borderWidth: 2
                        , tension: 0.4
                        , pointRadius: 0
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
                            , min: Math.min(...dataArr) * 0.9
                        }
                    }
                    , layout: {
                        padding: 0
                    }
                }
            });
        }

        const tVal = parseInt("{{ $stats['total_tenants'] ?? 0 }}") || 0;
        const aVal = parseInt("{{ $stats['active_tenants'] ?? 0 }}") || 0;
        const bVal = parseInt("{{ $stats['total_bookings'] ?? 0 }}") || 0;
        const trVal = parseInt("{{ $stats['trial_tenants'] ?? 0 }}") || 0;

        const sparklines = @json($sparklines);

        const sp1Data = sparklines.tenants.length === 3 ? sparklines.tenants : [0, 0, tVal];

        const sp2Data = sparklines.active.length === 3 ?
            sparklines.active :
            [0, 0, aVal];

        const sp3Data = sparklines.bookings.length === 3 ? sparklines.bookings : [0, 0, bVal];

        const sp4Data = sparklines.trials.length === 3 ?
            sparklines.trials :
            [0, 0, trVal];

        createMiniSparkline('sp1', sp1Data, colors.gold);
        createMiniSparkline('sp2', sp2Data, colors.emerald);
        createMiniSparkline('sp3', sp3Data, colors.purple);
        createMiniSparkline('sp4', sp4Data, colors.amber);
        // ==========================================
        // MAIN GROWTH CHART
        // ==========================================
        const growthRaw = @json($monthlyGrowth ? ? []);
        const growthLabels = Object.keys(growthRaw).length > 0 ? Object.keys(growthRaw) : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        const growthValues = Object.values(growthRaw).length > 0 ? Object.values(growthRaw) : [0, 0, 0, 0, 0, 0];

        const growthCtx = document.getElementById('growthChart');
        if (growthCtx) {
            new Chart(growthCtx.getContext('2d'), {
                type: 'line'
                , data: {
                    labels: growthLabels
                    , datasets: [{
                        label: 'New Tenants'
                        , data: growthValues
                        , borderColor: colors.gold
                        , backgroundColor: 'rgba(201, 169, 110, 0.05)'
                        , borderWidth: 2
                        , fill: true
                        , tension: 0.4
                        , pointBackgroundColor: colors.gold
                        , pointRadius: 4
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
                            , suggestedMax: 10
                            , grid: {
                                color: gridColor
                            }
                            , ticks: {
                                color: 'rgba(255,255,255,0.42)'
                                , stepSize: 1
                                , precision: 0
                                , font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }

        // ==========================================
        // PLAN DISTRIBUTION CHART
        // ==========================================
        const planRaw = @json($planDistribution ? ? []);
        const planLabels = Object.keys(planRaw).length > 0 ? Object.keys(planRaw).map(p => p.charAt(0).toUpperCase() + p.slice(1)) : ['Free', 'Basic', 'Premium'];
        const planValues = Object.values(planRaw).length > 0 ? Object.values(planRaw) : [1, 1, 1];

        const planCtx = document.getElementById('planChart');
        if (planCtx) {
            new Chart(planCtx.getContext('2d'), {
                type: 'doughnut'
                , data: {
                    labels: planLabels
                    , datasets: [{
                        data: planValues
                        , backgroundColor: [colors.teal, colors.purple, colors.gold]
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

    function updateClock() {
        const now = new Date();
        const options = {
            weekday: 'long'
            , day: '2-digit'
            , month: 'short'
            , year: 'numeric'
            , hour: '2-digit'
            , minute: '2-digit'
            , hour12: false
        };
        document.getElementById('live-clock').textContent =
            now.toLocaleDateString('en-IN', options).replace(',', ' ·');
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Har 60 second me bookings today update karo
    function refreshLiveStats() {
        fetch('{{ route("superadmin.stats.live") }}')
            .then(res => res.json())
            .then(data => {
                // Active salons
                document.getElementById('stat-active-salons').textContent =
                    data.active_tenants + ' active salons';
                // Bookings today
                document.getElementById('stat-bookings-today').innerHTML =
                    '<i class="bi bi-calendar-check"></i> ' + data.total_bookings_today + ' bookings today';
                // Revenue
                document.getElementById('stat-revenue-month').innerHTML =
                    '<i class="bi bi-currency-rupee"></i> ' +
                    parseInt(data.platform_revenue_month).toLocaleString('en-IN') + ' this month';
            })
            .catch(err => console.log('Stats refresh failed:', err));
    }

    setInterval(refreshLiveStats, 60000);

</script>
@endpush
