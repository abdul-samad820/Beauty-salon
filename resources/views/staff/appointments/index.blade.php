@extends('layouts.staff')

@section('title', 'My Appointments')

@section('content')
<div class="page-header mb-4 fade-up s1">
    <h1 class="page-title">My Appointments</h1>
    <p class="page-subtitle" style="color: var(--text-3);">View your scheduled treatment sessions</p>
</div>

{{-- Status Filters --}}
<div class="d-flex gap-2 flex-wrap mb-4 fade-up s2">
    @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'checked_in' => 'Checked In', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No Show'] as $val => $lbl)
    <a href="{{ route('staff.appointments') }}?status={{ $val }}" class="btn-sm" style="padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.75rem; text-decoration: none; transition: all 0.3s;
       {{ $status === $val ? 'background:var(--gold); color:#1a1400; font-weight:600;' : 'background:var(--bg-card); color:var(--text-3); border:1px solid var(--border);' }}">
        {{ $lbl }}
    </a>
    @endforeach
</div>

{{-- Appointments Table --}}
<div class="card-lux fade-up s3">
    <div class="lux-table-wrapper lux-scroller" style="max-height: 500px; overflow-y: auto;">
        <table class="lux-table">
            <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 10; border-bottom: 1px solid var(--border);">
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $a)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($a->appointment_date)->format('d M Y') }}</td>
                    <td style="font-family:monospace; color:var(--gold);">
                        {{ \Carbon\Carbon::parse($a->start_time)->format('h:i A') }}
                    </td>
                    <td>{{ $a->customer?->name ?? 'Walk-in' }}</td>
                    <td>{{ $a->service?->name }}</td>
                    <td style="color:var(--gold);">₹{{ number_format($a->amount) }}</td>
                    <td>
                        <span class="status-badge {{ match($a->status) { 'completed' => 'badge-active', 'checked_in' => 'badge-active', 'cancelled' => 'badge-suspended', 'no_show' => 'badge-suspended', default => 'badge-trial' } }}">
                            {{ $a->status === 'no_show' ? 'No Show' : ucfirst(str_replace('_', ' ', $a->status)) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 3rem; color: var(--text-3);">
                        <i class="bi bi-calendar-x d-block mb-2" style="font-size: 1.5rem; opacity: 0.5;"></i>
                        No appointments found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($appointments->hasPages())
    <div class="border-top" style="padding:1rem;">
        {{ $appointments->links('pagination::bootstrap-5') }}
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
