@extends('layouts.owner')

@section('title', 'Customers')
@section('page-title', 'Customer Ledger')
@section('breadcrumb', 'Manage / Customers')

@section('content')

{{-- KPI Row --}}
<div class="row g-3 mb-4 fade-up s1">
    <div class="col-6 col-md-4">
        <x-cards.kpi-card label="Total Customers" :value="$stats['total_customers']" icon="bi-people" color="var(--purple)" bg="var(--purple-dim)" />
    </div>
    <div class="col-6 col-md-4">
        <x-cards.kpi-card label="Repeat Customers" :value="$stats['repeat_customers']" icon="bi-arrow-repeat" color="var(--teal-light)" bg="var(--teal-dim)" />
    </div>
    <div class="col-6 col-md-4">
        <x-cards.kpi-card label="Lifetime Revenue" :value="'₹' . number_format($stats['total_lifetime_revenue'], 0)" icon="bi-cash-stack" color="var(--gold)" bg="rgba(212,175,55,0.12)" />
    </div>
</div>

{{-- Search --}}
<div class="card-lux mb-4 fade-up s2" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('owner.customers.index') }}" role="search" class="row g-3 align-items-center">
        <div class="col-12 col-md-6 position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y" style="left: 1rem; font-size: 0.85rem; color: var(--text-3);"></i>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search by name, email, or phone..." class="lux-input w-100" style="padding-left: 2.2rem; background-color: var(--bg-input); color: var(--text); border: 1px solid var(--border);" aria-label="Search customers" />
        </div>
        <div class="col-12 col-md-3">
            <button type="submit" class="btn-lux-gold btn-sm w-100">Search</button>
        </div>
        @if($search)
        <div class="col-12 col-md-3">
            <a href="{{ route('owner.customers.index') }}" class="btn-sm w-100 d-block text-center" style="padding: 0.5rem 1rem; border-radius: var(--r-md); background: rgba(255,255,255,0.03); border: 1px solid var(--border); color: var(--text-2); text-decoration: none;">Clear</a>
        </div>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="card-lux fade-up s3">
    <div class="p-4 border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
        <h3 class="serif" style="font-size: 1.15rem; color: var(--gold); margin-bottom: 0;">Customer Records</h3>
        <p style="font-size: 0.65rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Ranked by Lifetime Spend</p>
    </div>

    <div class="lux-table-wrapper lux-scroller" style="overflow-x: auto;">
        <table class="lux-table mb-0">
            <thead>
                <tr>
                    <th style="padding-left: 1.5rem;">Customer</th>
                    <th>Contact</th>
                    <th class="text-center">Visits</th>
                    <th>Last Visit</th>
                    <th>Lifetime Revenue</th>
                    <th class="text-end" style="padding-right: 1.5rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                <tr style="transition: background 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.015)'" onmouseout="this.style.background='transparent'">
                    <td style="padding-left: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-2); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 600; color: var(--text-2);">
                                {{ strtoupper(substr($c->name ?? 'C', 0, 2)) }}
                            </div>
                            <div style="font-weight: 600; font-size: 0.85rem; color: var(--text);">{{ $c->name }}</div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 0.8rem; color: var(--text-2);">{{ $c->email }}</div>
                        <div style="font-size: 0.7rem; color: var(--text-3); font-family: monospace;">{{ $c->phone ?? '—' }}</div>
                    </td>
                    <td class="text-center">
                        <span class="status-badge {{ $c->visit_count >= 2 ? 'badge-active' : 'badge-trial' }}" style="font-size: 0.7rem; padding: 0.25rem 0.6rem;">
                            {{ $c->visit_count }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 0.8rem; color: var(--text-2);">
                            {{ $c->last_visit_date ? \Carbon\Carbon::parse($c->last_visit_date)->format('d M Y') : '—' }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 0.85rem; color: var(--gold); font-family: monospace; font-weight: 600;">
                            ₹{{ number_format($c->lifetime_revenue ?? 0, 0) }}
                        </div>
                    </td>
                    <td class="text-end" style="padding-right: 1.5rem;">
                        <a href="{{ route('owner.customers.show', $c->id) }}" class="action-btn-pro" title="View History">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 3rem 1rem; color: var(--text-3);">
                        <i class="bi bi-people" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                        No customers found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div class="lux-pagination-wrapper border-top" style="border-color: rgba(255,255,255,0.05) !important; padding: 1rem 1.5rem;">
        <x-tables.pagination :paginator="$customers" />
    </div>
    @endif
</div>

@endsection
