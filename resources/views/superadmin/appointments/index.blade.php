@extends('layouts.superadmin')

@section('title', 'Appointments Monitor')
@section('page-title', 'Appointments Monitor')
@section('page-sub', 'All appointments across all parlours')

@section('content')

{{-- FIXED Stats Row (Icon Top, Text Bottom) --}}
<div class="row g-3 mb-4">
    @php
    $sts = [
    ['label'=>'Total Bookings', 'val'=>number_format($stats['total'] ?? 0), 'color'=>'var(--gold)', 'bg'=>'var(--gold-dim)', 'icon'=>'bi-calendar3'],
    ['label'=>'Today', 'val'=>number_format($stats['today'] ?? 0), 'color'=>'var(--purple)', 'bg'=>'var(--purple-dim)', 'icon'=>'bi-calendar-day'],
    ['label'=>'Pending / Confirm','val'=>number_format($stats['pending'] ?? 0),'color'=>'var(--amber)', 'bg'=>'var(--amber-dim)', 'icon'=>'bi-hourglass-split'],
    ['label'=>'Completed', 'val'=>number_format($stats['completed'] ?? 0),'color'=>'var(--emerald)','bg'=>'var(--emerald-dim)', 'icon'=>'bi-check-circle-fill'],
    ['label'=>'Cancelled', 'val'=>number_format($stats['cancelled'] ?? 0),'color'=>'var(--rose)', 'bg'=>'var(--rose-dim)', 'icon'=>'bi-x-circle'],
    ['label'=>'Total Revenue', 'val'=>'₹'.number_format($stats['revenue'] ?? 0,0,'.',','),'color'=>'var(--gold)','bg'=>'var(--gold-dim)','icon'=>'bi-currency-rupee'],
    ];
    @endphp
    @foreach($sts as $i => $s)
    <div class="col-xl-2 col-md-4 col-6 fade-up s{{ $i + 1 }}">
        <div class="card-lux p-3" style="height:100%; display: flex; flex-direction: column; justify-content: space-between;">

            {{-- Icon Section (Top) --}}
            <div style="width: 42px; height: 42px; border-radius: 12px; background: {{ $s['bg'] }}; color: {{ $s['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 1.2rem; border: 1px solid rgba(255,255,255,0.02);">
                <i class="bi {{ $s['icon'] }}"></i>
            </div>

            {{-- Details Section (Bottom) --}}
            <div>
                <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; line-height: 1.2;">
                    {{ $s['label'] }}
                </div>
                <div style="font-family: var(--ff-display); font-size: 1.6rem; color: {{ $s['color'] }}; font-weight: 600; line-height: 1; margin-bottom: 0;">
                    {{ $s['val'] }}
                </div>
            </div>

        </div>
    </div>
    @endforeach
</div>

{{-- Filters (Upgraded to Dark Theme) --}}
<div class="card-lux mb-4 fade-up s2" style="padding: 1.5rem;">
    <form method="GET" action="{{ route('superadmin.appointments') }}" class="row g-3 align-items-end" role="search">

        {{-- Parlour Select --}}
        <div class="col-12 col-md-3">
            <label class="lux-label">Parlour</label>
            <div style="position: relative;">
                <select name="tenant_id" class="lux-input w-100" style="padding-right: 2.5rem; color-scheme: dark; background-color: var(--bg-input); color: var(--text); cursor: pointer; -webkit-appearance: none; appearance: none;">
                    <option value="" style="background: var(--bg-card); color: var(--text-3);">All Parlours</option>
                    @foreach($tenants as $t)
                    <option value="{{ $t->id }}" style="background: var(--bg-card); color: var(--text);" {{ request('tenant_id')==$t->id?'selected':'' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
                <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                    <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                </div>
            </div>
        </div>

        {{-- Status Select --}}
        <div class="col-6 col-md-2">
            <label class="lux-label">Status</label>
            <div style="position: relative;">
                <select name="status" class="lux-input w-100" style="padding-right: 2.5rem; color-scheme: dark; background-color: var(--bg-input); color: var(--text); cursor: pointer; -webkit-appearance: none; appearance: none;">
                    <option value="all" style="background: var(--bg-card); color: var(--text-3);">All Statuses</option>
                    @foreach(['pending','confirmed','checked_in','completed','cancelled','no_show'] as $st)
                    <option value="{{ $st }}" style="background: var(--bg-card); color: var(--text);" {{ request('status')==$st?'selected':'' }}>{{ $st === 'no_show' ? 'No Show' : ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
                <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                    <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                </div>
            </div>
        </div>

        {{-- Date From --}}
        <div class="col-6 col-md-2">
            <label class="lux-label">From Date</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="lux-input w-100" style="color-scheme: dark; background-color: var(--bg-input); color: var(--text);">
        </div>

        {{-- Date To --}}
        <div class="col-6 col-md-2">
            <label class="lux-label">To Date</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="lux-input w-100" style="color-scheme: dark; background-color: var(--bg-input); color: var(--text);">
        </div>

        {{-- Buttons --}}
        <div class="col-12 col-md-3 d-flex gap-2 align-items-end">
            <button type="submit" class="btn-lux-gold w-100" style="padding: 0.65rem;"><i class="bi bi-funnel me-2"></i> Filter</button>
            <a href="{{ route('superadmin.appointments') }}" class="btn-lux-ghost w-100 text-center" style="padding: 0.65rem; justify-content: center;">Reset</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card-lux fade-up s3">
    <div class="lux-table-wrapper lux-scroller" style="max-height: 500px; overflow-y: auto;">
        <table class="lux-table">
            <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10;">
                <tr>
                    <th>ID</th>
                    <th>Parlour</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Staff</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appt)
                @php
                $statusClasses = [
                'pending' => 'badge-trial',
                'confirmed' => 'badge-active',
                'completed' => 'badge-active',
                'cancelled' => 'badge-suspended',
                ];
                @endphp
                <tr>
                    <td class="faint" style="font-family: monospace;">#{{ $appt->id }}</td>
                    <td>
                        <div style="font-weight: 500;">{{ $appt->tenant?->name ?? '—' }}</div>
                        <div class="faint" style="font-size: 0.65rem;">{{ $appt->tenant?->subdomain }}</div>
                    </td>
                    <td>
                        <div style="color: var(--text);">{{ $appt->customer?->name ?? '—' }}</div>
                        <div class="faint" style="font-size: 0.65rem;">{{ $appt->customer?->email }}</div>
                    </td>
                    <td style="color: var(--text-2);">{{ $appt->service?->name ?? '—' }}</td>
                    <td style="color: var(--text-2);">{{ $appt->staff?->user?->name ?? '—' }}</td>
                    <td>
                        <div style="color: var(--text);">{{ $appt->appointment_date?->format('d M Y') }}</div>
                        <div class="faint" style="font-size: 0.65rem;">{{ $appt->start_time }} – {{ $appt->end_time }}</div>
                    </td>
                    <td>
                        <span class="status-badge {{ $statusClasses[$appt->status] ?? 'badge-inactive' }}">
                            {{ ucfirst($appt->status) }}
                        </span>
                    </td>
                    <td class="text-end" style="color: var(--gold); font-weight: 500;">
                        {{ $appt->amount > 0 ? '₹'.number_format($appt->amount,0) : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 4rem 1rem;">
                        <i class="bi bi-calendar-x faint d-block mb-3" style="font-size: 2.5rem; opacity: 0.4;"></i>
                        <p class="muted">No appointments found matching your criteria.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($appointments->hasPages())
    <div class="border-top" style="border-color: var(--border) !important; padding: 1.5rem;">
        {{ $appointments->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    /* Scroller Fix */
    .lux-scroller::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(201, 169, 110, 0.3);
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb:hover {
        background: var(--gold);
    }

    /* Pagination Fix */
    .pagination {
        justify-content: center;
        margin-bottom: 0;
    }

    .pagination .page-link {
        background: var(--bg-card);
        border: 1px solid var(--border);
        color: var(--text-2);
        padding: 0.5rem 0.9rem;
    }

    .pagination .page-item.active .page-link {
        background: var(--gold);
        border-color: var(--gold);
        color: var(--charcoal);
    }

    .pagination .page-item.disabled .page-link {
        opacity: 0.4;
    }

</style>
@endpush
