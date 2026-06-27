@extends('layouts.staff')

@section('title', 'My Commissions')

@section('content')
<div class="page-header mb-4 fade-up s1">
    <h1 class="page-title">My Commissions</h1>
    <p class="page-subtitle" style="color: var(--text-3);">Track your earnings and settlement status</p>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4 fade-up s2">
    <div class="col-6 col-md-4">
        <x-cards.kpi-card label="Total Earned" value="₹{{ number_format($summary['total_earned']) }}" icon="bi-trophy" color="var(--gold)" bg="var(--gold-dim)" />
    </div>
    <div class="col-6 col-md-4">
        <x-cards.kpi-card label="Pending Payout" value="₹{{ number_format($summary['pending']) }}" icon="bi-hourglass-split" color="var(--amber)" bg="var(--amber-dim)" />
    </div>
    <div class="col-6 col-md-4">
        <x-cards.kpi-card label="Total Paid" value="₹{{ number_format($summary['paid']) }}" icon="bi-check2-circle" color="var(--emerald)" bg="var(--emerald-dim)" />
    </div>
</div>

{{-- Commissions Table --}}
<div class="card-lux fade-up s3">
    <div class="lux-table-wrapper lux-scroller" style="max-height: 450px; overflow-y: auto;">
        <table class="lux-table">
            <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10;">
                <tr>
                    <th>Date</th>
                    <th>Service</th>
                    <th>Amount</th>
                    <th>Commission</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commissions as $c)
                <tr>
                    <td>{{ $c->created_at->format('d M Y') }}</td>
                    <td>{{ $c->appointment?->service?->name ?? '—' }}</td>
                    <td style="color:var(--text-2);">₹{{ number_format($c->appointment?->amount ?? 0) }}</td>
                    <td style="color:var(--gold);font-weight:600;">₹{{ number_format($c->commission_amount) }}</td>
                    <td>
                        <span class="status-badge {{ $c->status === 'paid' ? 'badge-active' : 'badge-trial' }}">
                            {{ ucfirst($c->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 3rem; color: var(--text-3);">
                        <i class="bi bi-cash-stack d-block mb-2" style="font-size: 1.5rem; opacity: 0.5;"></i>
                        No commissions yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($commissions->hasPages())
    <div class="border-top" style="padding:1rem;">
        {{ $commissions->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* Scroller */
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
