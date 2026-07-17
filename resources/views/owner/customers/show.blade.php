@extends('layouts.owner')

@section('title', $customer->name . ' - Customer History')
@section('page-title', 'Customer Profile')
@section('breadcrumb', 'Manage / Customers / ' . $customer->name)

@section('topbar-actions')
<a href="{{ route('owner.customers.index') }}" class="btn-lux-ghost btn-sm" style="padding: 0.5rem 1rem; border-radius: var(--r-md); background: rgba(255,255,255,0.03); border: 1px solid var(--border); color: var(--text-2); text-decoration: none;">
    <i class="bi bi-arrow-left me-1"></i> Back to List
</a>
@endsection

@section('content')

{{-- Profile Header --}}
<div class="card-lux mb-4 fade-up s1" style="padding: 1.5rem;">
    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div style="width: 56px; height: 56px; flex-shrink: 0; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-2); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 600; color: var(--text-2);">
            {{ strtoupper(substr($customer->name ?? 'C', 0, 2)) }}
        </div>
        <div style="flex: 1; min-width: 200px;">
            <h3 class="serif" style="font-size: 1.3rem; color: var(--text); margin-bottom: 0.2rem;">{{ $customer->name }}</h3>
            <div style="font-size: 0.8rem; color: var(--text-3);">{{ $customer->email }} &middot; {{ $customer->phone ?? '—' }}</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.65rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em;">Customer Since</div>
            <div style="font-size: 0.85rem; color: var(--text-2);">{{ $customer->created_at?->format('d M Y') }}</div>
        </div>
    </div>
</div>

{{-- KPI Row --}}
<div class="row g-3 mb-4 fade-up s2">
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Total Visits" :value="$stats['total_visits']" icon="bi-calendar2-check" color="var(--emerald)" bg="var(--emerald-dim)" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Lifetime Revenue" :value="'₹' . number_format($stats['lifetime_revenue'], 0)" icon="bi-cash-stack" color="var(--gold)" bg="rgba(212,175,55,0.12)" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Avg. Spend / Visit" :value="'₹' . number_format($stats['avg_spend'], 0)" icon="bi-graph-up" color="var(--purple)" bg="var(--purple-dim)" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="No Shows" :value="$stats['no_show_count']" icon="bi-person-x" color="var(--rose)" bg="var(--rose-dim)" />
    </div>
</div>

{{-- Preferences --}}
<div class="row g-3 mb-4 fade-up s3">
    <div class="col-12 col-md-6">
        <div class="card-lux p-4 h-100">
            <p style="font-size: 0.65rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Preferred Stylist</p>
            @if($preferredStaff && $preferredStaff->staff)
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="height: 8px; width: 8px; border-radius: 50%; background: var(--purple); box-shadow: 0 0 6px var(--purple);"></span>
                <span style="font-size: 1rem; color: var(--text); font-weight: 500;">{{ $preferredStaff->staff?->user?->name ?? 'Unallocated' }}</span>
                <span style="font-size: 0.7rem; color: var(--text-3);">({{ $preferredStaff->visit_count }} visits)</span>
            </div>
            @else
            <p style="font-size: 0.85rem; color: var(--text-3); margin: 0;">No completed visits yet.</p>
            @endif
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card-lux p-4 h-100">
            <p style="font-size: 0.65rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Preferred Service</p>
            @if($preferredService && $preferredService->service)
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="height: 8px; width: 8px; border-radius: 50%; background: var(--teal-light); box-shadow: 0 0 6px var(--teal-light);"></span>
                <span style="font-size: 1rem; color: var(--text); font-weight: 500;">{{ $preferredService->service?->name }}</span>
                <span style="font-size: 0.7rem; color: var(--text-3);">({{ $preferredService->booking_count }} bookings)</span>
            </div>
            @else
            <p style="font-size: 0.85rem; color: var(--text-3); margin: 0;">No completed visits yet.</p>
            @endif
        </div>
    </div>
</div>

{{-- Visit History Table --}}
<div class="card-lux fade-up s4">
    <div class="p-4 border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
        <h3 class="serif" style="font-size: 1.15rem; color: var(--gold); margin-bottom: 0;">Visit History</h3>
        <p style="font-size: 0.65rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">All Appointments</p>
    </div>

    <div class="lux-table-wrapper lux-scroller" style="overflow-x: auto;">
        <table class="lux-table mb-0">
            <thead>
                <tr>
                    <th style="padding-left: 1.5rem;">Date</th>
                    <th>Service</th>
                    <th>Stylist</th>
                    <th>Amount</th>
                    <th class="text-end" style="padding-right: 1.5rem;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $a)
                <tr style="transition: background 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.015)'" onmouseout="this.style.background='transparent'">
                    <td style="padding-left: 1.5rem;">
                        <div style="font-size: 0.85rem; color: var(--text);">{{ \Carbon\Carbon::parse($a->appointment_date)->format('d M Y') }}</div>
                        <div style="font-size: 0.7rem; color: var(--gold); font-family: var(--ff-display); font-weight: 600;">{{ \Carbon\Carbon::parse($a->start_time)->format('h:i A') }}</div>
                    </td>
                    <td>
                        <div style="font-size: 0.85rem; color: var(--text-2);">{{ $a->service?->name ?? '—' }}</div>
                    </td>
                    <td>
                        <div style="font-size: 0.8rem; color: var(--text-2);">{{ $a->staff?->user?->name ?? 'Unallocated' }}</div>
                    </td>
                    <td>
                        <div style="font-size: 0.85rem; color: var(--gold); font-family: monospace;">₹{{ number_format($a->amount, 0) }}</div>
                    </td>
                    <td class="text-end" style="padding-right: 1.5rem;">
                        <span class="status-badge {{ match($a->status) { 'completed' => 'badge-active', 'checked_in' => 'badge-active', 'cancelled' => 'badge-suspended', 'no_show' => 'badge-suspended', default => 'badge-trial' } }}" style="font-size: 0.65rem; padding: 0.25rem 0.6rem;">
                            {{ $a->status === 'no_show' ? 'No Show' : ucfirst(str_replace('_', ' ', $a->status)) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 3rem 1rem; color: var(--text-3);">
                        <i class="bi bi-calendar-x" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                        No appointments recorded yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($appointments->hasPages())
    <div class="lux-pagination-wrapper border-top" style="border-color: rgba(255,255,255,0.05) !important; padding: 1rem 1.5rem;">
        <x-tables.pagination :paginator="$appointments" />
    </div>
    @endif
</div>

@endsection
