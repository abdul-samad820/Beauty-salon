@extends('layouts.owner')

@section('title', 'Commissions Ledger')
@section('page-title', 'Staff Commissions Matrix')
@section('breadcrumb', 'Manage / Commissions')
@push('styles')
<style>
    /* Premium Scroller */
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
        margin: 0;
    }

    .pagination .page-link {
        background: var(--bg-card);
        border: 1px solid var(--border);
        color: var(--text-2);
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }

    .pagination .page-item.active .page-link {
        background: var(--gold);
        border-color: var(--gold);
        color: var(--charcoal);
    }

</style>
@endpush
@section('content')

<div class="row g-3 mb-4 fade-up s1">
    <div class="col-12 col-md-4">
        <x-cards.kpi-card label="Total Accumulated Payouts" :value="'₹' . number_format($stats['total_accumulated'] ?? 0, 2)" icon="bi-cash-stack" color="var(--gold)" bg="var(--gold-dim)" />
    </div>
    <div class="col-12 col-md-4">
        <x-cards.kpi-card label="Pending Settlement Balance" :value="'₹' . number_format($stats['total_pending'] ?? 0, 2)" icon="bi-hourglass-split" color="var(--amber)" bg="var(--amber-dim)" />
    </div>
    <div class="col-12 col-md-4">
        <x-cards.kpi-card label="Settled Payout Disbursed" :value="'₹' . number_format($stats['total_settled'] ?? 0, 2)" icon="bi-check-circle" color="var(--emerald)" bg="var(--emerald-dim)" />
    </div>
</div>

<div class="card-lux p-3 mb-4 fade-up s2">
    <form method="GET" action="{{ route('owner.commissions.index') }}" id="commissionsFilterForm" role="search" class="row g-3 align-items-center">

        <div class="col-12 col-md-4 position-relative">
            <select name="staff_id" class="lux-input" style="padding-right: 2.5rem; color-scheme: dark; background: var(--bg-input); color: var(--text); cursor: pointer;" aria-label="Filter by staff">
                <option value="" style="background: var(--bg-card); color: var(--text-3);">All Staff Executives</option>
                @foreach($staffList as $s)
                <option value="{{ $s->id }}" style="background: var(--bg-card); color: var(--text);" {{ request('staff_id') == $s->id ? 'selected' : '' }}>
                    {{ $s->user?->name }}
                </option>
                @endforeach
            </select>
            <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
            </div>
        </div>

        <div class="col-12 col-md-4 position-relative">
            <select name="status" class="lux-input" style="padding-right: 2.5rem; color-scheme: dark; background: var(--bg-input); color: var(--text); cursor: pointer;" aria-label="Filter by settlement status">
                <option value="all" style="background: var(--bg-card); color: var(--text-3);" {{ request('status','all') === 'all' ? 'selected' : '' }}>All Settlement Statuses</option>
                <option value="pending" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Balance</option>
                <option value="settled" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'settled' ? 'selected' : '' }}>Settled / Disbursed</option>
            </select>
            <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
            </div>
        </div>

        <div class="col-12 col-md-4 d-flex gap-2 justify-content-md-end">
            <button type="submit" class="btn-lux-ghost btn-sm border-0">Apply</button>
            <a href="{{ route('owner.commissions.index') }}" class="btn-lux-ghost btn-sm faint border-0">Clear</a>
        </div>
    </form>
</div>

<div class="card-lux fade-up s3">
    {{-- lux-scroller class add ki aur overflow control set kiya --}}
    <div class="lux-table-wrapper lux-scroller" style="max-height: 500px; overflow-y: auto;">
        <table class="lux-table">
            <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10; border-bottom: 1px solid var(--border);">
                <tr>
                    <th>Staff Member Profile</th>
                    <th>Associated Treatment</th>
                    <th>Total Ticket Value</th>
                    <th>Split Share Ratio</th>
                    <th>Calculated Payout</th>
                    <th>Settlement State</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commissions as $comm)
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--text);">{{ $comm->staff?->user?->name ?? 'Deleted' }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 400; color: var(--text-2);">{{ $comm->appointment?->service?->name ?? 'Manual Adjustment' }}</div>
                        <div class="faint" style="font-size: 0.65rem;">{{ \Carbon\Carbon::parse($comm->created_at)->format('d M Y · h:i A') }}</div>
                    </td>
                    <td class="serif" style="color: var(--text-2); font-size: 1rem;">₹{{ number_format($comm->appointment?->amount ?? 0, 2) }}</td>
                    <td style="color: var(--purple); font-weight: 500;">{{ $comm->commission_percent }}%</td>
                    <td class="serif" style="font-size: 1.1rem; color: var(--gold);">₹{{ number_format($comm->commission_amount, 2) }}</td>
                    <td>
                        <span class="status-badge {{ $comm->status === 'settled' ? 'badge-active' : 'badge-trial' }}">
                            {{ ucfirst($comm->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        @if(($comm->status ?? 'pending') === 'pending')
                        <form method="POST" action="{{ route('owner.commissions.settle', $comm->id) }}" onsubmit="return confirm('Confirm disbursement?');">
                            @csrf
                            <button type="submit" class="btn-lux-gold btn-sm" style="padding: 0.25rem 0.6rem; font-size: 0.7rem;">Disburse</button>
                        </form>
                        @else
                        <span class="faint" style="font-size: 0.75rem;"><i class="bi bi-check-all text-emerald"></i> Closed</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">No commissions data found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($commissions->hasPages())
    <div class="lux-pagination-wrapper border-top" style="padding: 1rem;">
        {{ $commissions->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
