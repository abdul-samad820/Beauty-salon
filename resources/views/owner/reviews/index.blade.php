@extends('layouts.owner')

@section('title', 'Reviews')
@section('page-title', 'Customer Reviews')
@section('breadcrumb', 'Manage / Reviews')

@section('content')

{{-- Stats --}}
<div class="mb-4 fade-up s1">
    <x-cards.stat-row :stats="[
        ['label' => 'Total Reviews',   'value' => $reviews->total(),  'color' => 'var(--gold)'],
        ['label' => 'Pending Reviews', 'value' => $pendingCount,      'color' => 'var(--rose)'],
    ]" />
</div>

{{-- Reviews Table --}}
<div class="card-lux fade-up s2">
    <div class="lux-table-wrapper">
        <table class="lux-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--text); font-size: 0.85rem;">
                            {{ $review->customer?->name ?? '—' }}
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-3);">
                            {{ $review->customer?->email ?? '' }}
                        </div>
                    </td>
                    <td style="font-size: 0.8rem; color: var(--text-2);">
                        {{ $review->appointment?->service?->name ?? '—' }}
                    </td>
                    <td>
                        <div style="color: var(--gold); font-size: 0.85rem; letter-spacing: 0.1em;">
                            @for($i = 1; $i <= 5; $i++) {{ $i <= $review->rating ? '★' : '☆' }} @endfor </div>
                    </td>
                    <td style="font-size: 0.78rem; color: var(--text-2); max-width: 250px; line-height: 1.5;">
                        {{ Str::limit($review->comment, 80) }}
                    </td>
                    <td style="font-size: 0.75rem; color: var(--text-3); font-family: monospace;">
                        {{ \Carbon\Carbon::parse($review->created_at)->format('d M Y') }}
                    </td>
                    <td>
                        <span class="status-badge {{ match($review->status) {
                            'approved' => 'badge-active',
                            'rejected' => 'badge-suspended',
                            default    => 'badge-trial'
                        } }}">
                            {{ ucfirst($review->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                            @if($review->status === 'pending')
                            <form action="{{ route('owner.reviews.approve', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn-icon-action" style="color: var(--emerald);" title="Approve Review">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <form action="{{ route('owner.reviews.reject', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn-icon-action" style="color: var(--rose);" title="Reject Review">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @elseif($review->status === 'approved')
                            <form action="{{ route('owner.reviews.reject', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn-icon-action" style="color: var(--rose);" title="Reject Review">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @elseif($review->status === 'rejected')
                            <form action="{{ route('owner.reviews.approve', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn-icon-action" style="color: var(--emerald);" title="Approve Review">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 4rem 2rem;">
                        <i class="bi bi-star faint d-block mb-3" style="font-size: 2rem;"></i>
                        <h4 class="faint" style="font-size: var(--text-sm);">No reviews logged yet</h4>
                        <p class="muted" style="font-size: var(--text-xs); margin: 0 auto;">Customer feedback and ratings will appear here once submitted.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reviews->hasPages())
    <div class="lux-pagination-wrapper border-top" style="border-color: var(--border) !important; padding: 1rem 1.5rem;">
        <x-tables.pagination :paginator="$reviews" />
    </div>
    @endif
</div>

@endsection
