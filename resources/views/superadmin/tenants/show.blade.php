@extends('layouts.superadmin')

@section('title', $tenant->name)
@section('breadcrumb')
<a href="{{ route('superadmin.tenants.index') }}" style="color:var(--text-3);text-decoration:none;">Tenants</a>
<i class="bi bi-chevron-right" style="font-size:0.55rem;margin:0 0.4rem;"></i>
<span style="color:var(--text-2);">{{ $tenant->name }}</span>
@endsection
@section('page-title', $tenant->name)

@section('topbar-actions')
<a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="btn-lux-ghost btn-sm border-0">
    <i class="bi bi-pencil"></i> Edit
</a>

@if($tenant->status !== 'active')
<form method="POST" action="{{ route('superadmin.tenants.status', $tenant) }}" style="display:inline;">
    @csrf @method('PATCH')
    <input type="hidden" name="status" value="active">
    <button type="submit" class="btn-lux-ghost btn-sm" style="border-color:var(--emerald);color:var(--emerald);background:var(--emerald-dim);">
        <i class="bi bi-play-fill"></i> Activate
    </button>
</form>
@else
<form method="POST" action="{{ route('superadmin.tenants.status', $tenant) }}" style="display:inline;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" value="suspended">
    <button type="submit" class="btn-lux-danger btn-sm">
        <i class="bi bi-pause-fill"></i> Suspend
    </button>
</form>
@endif
@endsection

@push('styles')
<style>
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.55rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        font-size: 0.82rem;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-key {
        color: var(--text-3);
    }

    .info-val {
        color: var(--text);
        font-weight: 400;
        text-align: right;
    }

    .detail-tabs {
        display: flex;
        gap: 0;
        border-bottom: 1px solid var(--border);
        margin-bottom: 1.5rem;
        overflow-x: auto;
    }

    .detail-tab {
        padding: 0.65rem 1.2rem;
        font-size: 0.78rem;
        color: var(--text-3);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: color 0.25s, border-color 0.25s;
        white-space: nowrap;
    }

    .detail-tab.active {
        color: var(--gold);
        border-bottom-color: var(--gold);
    }

    .detail-tab:hover:not(.active) {
        color: var(--text-2);
    }

    .tab-panel {
        display: none;
    }

    .tab-panel.active {
        display: block;
    }

    .stat-mini {
        background: var(--bg-card-2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 1rem;
    }

    .stat-mini-val {
        font-family: var(--ff-display);
        font-size: 1.8rem;
        font-weight: 400;
        line-height: 1;
        margin-bottom: 0.3rem;
    }

    .stat-mini-label {
        font-size: 0.65rem;
        color: var(--text-3);
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .chart-wrap {
        position: relative;
        height: 200px;
    }

</style>
@endpush

@section('content')

{{-- ── HEADER CARD ── --}}
<div class="card-lux p-4 mb-4 fade-up">
    <div class="row align-items-center g-3">
        <div class="col-auto">
            <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,var(--gold),var(--teal));display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:1.5rem;font-weight:400;color:white;">
                {{ strtoupper(substr($tenant->name, 0, 1)) }}
            </div>
        </div>
        <div class="col">
            <div style="display:flex;align-items:center;gap:0.7rem;flex-wrap:wrap;">
                <span style="font-family:var(--ff-display);font-size:1.4rem;font-weight:400;color:var(--text);">{{ $tenant->name }}</span>
                <span class="plan-badge plan-{{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span>
                <span class="status-badge {{ $tenant->status_badge_class }}">
                    <i class="bi bi-circle-fill" style="font-size:0.35rem;"></i>
                    {{ ucfirst($tenant->status) }}
                </span>
                @if($tenant->onTrial())
                <span class="status-badge badge-trial"><i class="bi bi-hourglass-split"></i> Trial — {{ $tenant->trial_ends_at->diffForHumans() }}</span>
                @endif
            </div>
            <div style="font-size:0.75rem;color:var(--text-3);margin-top:0.3rem;font-family:monospace;">
                {{ $tenant->subdomain }}.lumiere.app &nbsp;·&nbsp; Joined {{ $tenant->created_at->format('d M Y') }}
            </div>
        </div>
        <div class="col-auto d-none d-md-flex" style="gap:1.5rem;">
            <div style="text-align:center;">
                <div style="font-family:var(--ff-display);font-size:1.6rem;color:var(--emerald);">₹{{ number_format($stats['total_revenue']) }}</div>
                <div style="font-size:0.62rem;color:var(--text-3);letter-spacing:0.1em;text-transform:uppercase;">Total Revenue</div>
            </div>
            <div style="text-align:center;">
                <div style="font-family:var(--ff-display);font-size:1.6rem;color:var(--gold);">{{ number_format($stats['total_appointments']) }}</div>
                <div style="font-size:0.62rem;color:var(--text-3);letter-spacing:0.1em;text-transform:uppercase;">Appointments</div>
            </div>
        </div>
    </div>
</div>

{{-- ── TABS ── --}}
<div class="detail-tabs fade-up s1">
    <div class="detail-tab active" onclick="switchTab(this,'tab-overview')">Overview</div>
    <div class="detail-tab" onclick="switchTab(this,'tab-analytics')">Analytics</div>
    <div class="detail-tab" onclick="switchTab(this,'tab-staff')">Staff ({{ $stats['staff_count'] }})</div>
    <div class="detail-tab" onclick="switchTab(this,'tab-services')">Services ({{ $stats['services_count'] }})</div>
    <div class="detail-tab" onclick="switchTab(this,'tab-activity')">Activity</div>
</div>

{{-- ── TAB: OVERVIEW ── --}}
<div class="tab-panel active fade-up s2" id="tab-overview">
    <div class="row g-3">
        {{-- Stats --}}
        <div class="col-6 col-md-3">
            <div class="stat-mini">
                <div class="stat-mini-val" style="color:var(--emerald);">{{ $stats['completed'] }}</div>
                <div class="stat-mini-label">Completed</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini">
                <div class="stat-mini-val" style="color:var(--rose);">{{ $stats['cancelled'] }}</div>
                <div class="stat-mini-label">Cancelled</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini">
                <div class="stat-mini-val" style="color:var(--gold);">{{ $stats['staff_count'] }}</div>
                <div class="stat-mini-label">Staff</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-mini">
                <div class="stat-mini-val" style="color:#a78bfa;">{{ $stats['services_count'] }}</div>
                <div class="stat-mini-label">Services</div>
            </div>
        </div>

        {{-- Revenue chart --}}
        <div class="col-lg-8">
            <div class="card-lux p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div style="font-family:var(--ff-display); font-size:1.2rem;">Revenue Trend</div>
                        <div class="faint" style="font-size:0.75rem;">Monthly revenue — last 6 months</div>
                    </div>
                </div>
                <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
            </div>
        </div>

        {{-- Parlour details --}}
        <div class="col-lg-4">
            <div class="card-lux p-4">
                <div style="font-family:var(--ff-display); font-size:1.2rem; margin-bottom:1rem;">Parlour Details</div>
                <div class="info-row"><span class="info-key">Email</span><span class="info-val">{{ $tenant->email }}</span></div>
                <div class="info-row"><span class="info-key">Phone</span><span class="info-val">{{ $tenant->phone ?? '—' }}</span></div>
                <div class="info-row"><span class="info-key">Address</span><span class="info-val" style="max-width:180px;white-space:normal;word-break:break-word;">{{ $tenant->address ?? '—' }}</span></div>
                <div class="info-row"><span class="info-key">Plan</span><span class="info-val">{{ ucfirst($tenant->plan) }}</span></div>
                <div class="info-row"><span class="info-key">Subdomain</span><span class="info-val" style="font-family:monospace;font-size:0.75rem;color:var(--teal-light);">{{ $tenant->subdomain }}</span></div>
                <div class="info-row">
                    <span class="info-key">Trial Ends</span>
                    <span class="info-val">{{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('d M Y') : '—' }}</span>
                </div>
                <div class="info-row"><span class="info-key">Joined</span><span class="info-val">{{ $tenant->created_at->format('d M Y') }}</span></div>
            </div>
        </div>
    </div>
</div>

{{-- ── TAB: ANALYTICS ── --}}
<div class="tab-panel fade-up s2" id="tab-analytics">
    <div class="row g-3">
        <div class="col-12">
            <div class="card-lux p-4">
                <div style="font-family:var(--ff-display); font-size:1.2rem; margin-bottom:1rem;">Monthly Revenue</div>
                <div class="chart-wrap" style="height:260px;"><canvas id="analyticsChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

{{-- ── TAB: STAFF ── --}}
<div class="tab-panel fade-up s2" id="tab-staff">
    <div class="card-lux">
        <div class="lux-table-wrapper">
            <table class="lux-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenant->users->filter(fn($u) => $u->hasRole('staff')) as $user)
                    <tr>
                        <td style="color:var(--text);font-weight:400;">{{ $user->name }}</td>
                        <td>{{ $user->getRoleNames()->implode(', ') }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>
                            @if($user->is_active)
                            <span class="status-badge badge-active">Active</span>
                            @else
                            <span class="status-badge badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:3rem 1rem;">
                            <i class="bi bi-people faint d-block mb-2" style="font-size:1.5rem;"></i>
                            <span class="muted">No staff members found.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── TAB: SERVICES ── --}}
<div class="tab-panel fade-up s2" id="tab-services">
    <div class="card-lux">
        <div class="lux-table-wrapper">
            <table class="lux-table">
                <thead>
                    <tr>
                        <th>Service Name</th>
                        <th>Category</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenant->services as $svc)
                    <tr>
                        <td style="color:var(--text);font-weight:400;">{{ $svc->name }}</td>
                        <td>{{ $svc->category ?? '—' }}</td>
                        <td>{{ $svc->duration_minutes ?? '—' }} min</td>
                        <td style="color:var(--emerald);">₹{{ number_format($svc->price) }}</td>
                        <td>
                            <span class="status-badge {{ $svc->is_active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $svc->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:3rem 1rem;">
                            <i class="bi bi-scissors faint d-block mb-2" style="font-size:1.5rem;"></i>
                            <span class="muted">No services found.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── TAB: ACTIVITY ── --}}
<div class="tab-panel fade-up s2" id="tab-activity">
    <div class="card-lux">
        <div class="p-4 border-bottom" style="border-color: var(--border) !important;">
            <div style="font-family:var(--ff-display); font-size:1.2rem;">Recent Appointments</div>
        </div>
        <div class="lux-table-wrapper">
            <table class="lux-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenant->appointments->take(15) as $appt)
                    <tr>
                        <td style="color:var(--text);">{{ $appt->customer->name ?? '—' }}</td>
                        <td style="color:var(--text);">{{ $appt->customer->name ?? '—' }}</td>
                        <td>{{ $appt->service->name ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($appt->appointment_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($appt->start_time)->format('h:i A') }}</td>
                        <td>
                            <span class="status-badge {{ match($appt->status) { 'completed' => 'badge-active', 'cancelled' => 'badge-suspended', default => 'badge-trial' } }}">
                                {{ ucfirst($appt->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:3rem 1rem;">
                            <i class="bi bi-calendar-x faint d-block mb-2" style="font-size:1.5rem;"></i>
                            <span class="muted">No appointments found.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function switchTab(el, id) {
        document.querySelectorAll('.detail-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        document.getElementById(id).classList.add('active');

        // Lazy-init analytics chart
        if (id === 'tab-analytics' && !window._analyticsInit) {
            window._analyticsInit = true;
            const data = @json($monthlyRevenue);
            new Chart(document.getElementById('analyticsChart').getContext('2d'), {
                type: 'bar'
                , data: {
                    labels: Object.keys(data)
                    , datasets: [{
                        label: 'Revenue (₹)'
                        , data: Object.values(data)
                        , backgroundColor: '#c9a96e99'
                        , borderColor: '#c9a96e'
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
                        }
                        , y: {
                            grid: {
                                color: 'rgba(255,255,255,0.04)'
                            }
                        }
                    }
                }
            });
        }
    }

    // Revenue trend chart (overview tab)
    const revData = @json($monthlyRevenue);
    new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'bar'
        , data: {
            labels: Object.keys(revData)
            , datasets: [{
                label: '₹ Revenue'
                , data: Object.values(revData)
                , backgroundColor: 'rgba(201,169,110,0.75)'
                , borderColor: '#c9a96e'
                , borderWidth: 0
                , borderRadius: 6
                , borderSkipped: false
                , hoverBackgroundColor: '#c9a96e'
            , }]
        }
        , options: {
            responsive: true
            , maintainAspectRatio: false
            , plugins: {
                legend: {
                    display: false
                }
                , tooltip: {
                    callbacks: {
                        label: (item) => ' ₹ ' + Number(item.raw).toLocaleString('en-IN')
                    }
                }
            }
            , scales: {
                x: {
                    grid: {
                        display: false
                    }
                    , ticks: {
                        color: 'rgba(255,255,255,0.4)'
                    }
                    , border: {
                        display: false
                    }
                }
                , y: {
                    grid: {
                        color: 'rgba(255,255,255,0.04)'
                    }
                    , border: {
                        display: false
                    }
                    , ticks: {
                        color: 'rgba(255,255,255,0.4)'
                        , callback: (v) => '₹' + Number(v).toLocaleString('en-IN')
                    }
                }
            }
        }
    });

</script>
@endpush
