@extends('layouts.customer')

@section('title', 'My Appointments')

@push('styles')
<style>
    .appt-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        overflow: hidden;
        position: relative;
        transition: border-color var(--transition);
    }

    .appt-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .07), transparent);
    }

    .appt-card:hover {
        border-color: rgba(201, 169, 110, .2);
    }

    /* Unpaid razorpay appointments ke liye warning border */
    .appt-card.payment-pending-card {
        border-color: rgba(251, 191, 36, 0.35);
    }

    .appt-card.payment-pending-card::before {
        background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.15), transparent);
    }

    .appt-date-block {
        background: var(--gold-dim);
        border-right: 1px solid rgba(201, 169, 110, .15);
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        flex-shrink: 0;
    }

    .appt-day {
        font-family: var(--ff-display);
        font-size: 1.6rem;
        font-weight: 400;
        color: var(--gold);
        line-height: 1;
    }

    .appt-mon {
        font-size: .6rem;
        font-weight: 700;
        letter-spacing: .2em;
        text-transform: uppercase;
        color: var(--text-3);
        margin-top: .2rem;
    }

    .btn-cust-danger {
        background: transparent;
        border: 1px solid var(--rose);
        color: var(--rose);
        padding: 0.4rem 0.8rem;
        border-radius: var(--r-sm);
        font-size: 0.7rem;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-cust-danger:hover {
        background: var(--rose);
        color: #fff;
    }

    .btn-review {
        background: transparent;
        border: 1px solid var(--teal);
        color: var(--teal);
        padding: 0.4rem 0.8rem;
        border-radius: var(--r-sm);
        font-size: 0.7rem;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        text-decoration: none;
    }

    .btn-review:hover {
        background: var(--teal);
        color: #fff;
    }

    .btn-pay-now {
        background: var(--gold);
        border: none;
        color: #000;
        padding: 0.4rem 0.9rem;
        border-radius: var(--r-sm);
        font-size: 0.7rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.2s;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-pay-now:hover {
        background: #e6be5a;
        color: #000;
    }

</style>
@endpush

@section('content')

{{-- Page header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;" class="fade-up s1">
    <div>
        <h2 class="serif" style="font-size:1.4rem;color:var(--text);margin-bottom:0.2rem;">My Appointments</h2>
        <p style="font-size:.75rem;color:var(--text-3);">{{ $tenant->name }}</p>
    </div>
    <a href="{{ route('customer.home', $subdomain) }}" class="btn-lux-gold btn-sm">
        <i class="bi bi-plus-lg"></i> New Booking
    </a>
</div>

{{-- ── Payment Pending Alert Banner ─────────────────────────────────────── --}}
@if(isset($unpaidAppointments) && $unpaidAppointments->count() > 0)
<div class="fade-up s1" style="
    background: rgba(251,191,36,0.08);
    border: 1px solid rgba(251,191,36,0.3);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
">
    <div style="color:#fbbf24;font-size:1.3rem;flex-shrink:0;margin-top:0.1rem;">
        <i class="bi bi-exclamation-triangle-fill"></i>
    </div>
    <div style="flex:1;">
        <div style="font-size:0.85rem;font-weight:600;color:#fbbf24;margin-bottom:0.3rem;">
            Payment Pending — {{ $unpaidAppointments->count() }} {{ $unpaidAppointments->count() === 1 ? 'Booking' : 'Bookings' }} Awaiting Payment
        </div>
        <p style="font-size:0.75rem;color:var(--text-3);margin:0 0 0.75rem 0;line-height:1.5;">
            Your slot is reserved but your booking is <strong style="color:#fbbf24;">not confirmed</strong> until payment is completed.
        </p>
        {{-- List each unpaid appointment with Pay Now button --}}
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            @foreach($unpaidAppointments as $ua)
            <div style="display:flex;align-items:center;justify-content:space-between;background:rgba(251,191,36,0.06);border:1px solid rgba(251,191,36,0.15);border-radius:8px;padding:0.6rem 0.9rem;">
                <div>
                    <div style="font-size:0.8rem;font-weight:600;color:var(--text);">{{ $ua->service?->name }}</div>
                    <div style="font-size:0.68rem;color:var(--text-3);margin-top:0.1rem;">
                        {{ \Carbon\Carbon::parse($ua->appointment_date)->format('d M Y') }} ·
                        {{ \Carbon\Carbon::parse($ua->start_time)->format('h:i A') }} ·
                        <span style="color:var(--gold);">₹{{ number_format($ua->amount) }}</span>
                    </div>
                </div>
                <a href="{{ route('customer.book.confirmed', [$subdomain, $ua->id]) }}" class="btn-pay-now">
                    <i class="bi bi-credit-card"></i> Pay Now
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Status filter tabs --}}
<div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.5rem;" class="fade-up s2" role="tablist">
    @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'checked_in' => 'Checked In', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No Show'] as $val => $lbl)
    <a href="{{ route('customer.appointments', $subdomain) }}?status={{ $val }}" style="padding:0.4rem 1rem;border-radius:20px;font-size:0.75rem;text-decoration:none;transition:0.3s;
              {{ request('status', 'all') === $val ? 'background:var(--gold);color:#1a1400;' : 'background:var(--bg-input);color:var(--text-2);' }}" role="tab">
        {{ $lbl }}
        @if($val === 'pending' && isset($pendingCount) && $pendingCount > 0)
        <span style="background:{{ request('status')==='pending' ? '#1a1400' : 'var(--gold)' }};color:{{ request('status')==='pending' ? 'var(--gold)' : '#1a1400' }};border-radius:20px;font-size:.6rem;font-weight:700;padding:0.1rem 0.4rem;margin-left:.3rem;">
            {{ $pendingCount }}
        </span>
        @endif
    </a>
    @endforeach
</div>

{{-- Appointments list --}}
<div style="display:flex;flex-direction:column;gap:0.8rem;" class="fade-up s3">
    @forelse($appointments as $a)
    @php $isUnpaidRazorpay = $a->payment_method === 'razorpay' && $a->payment_status !== 'paid'; @endphp
    <article class="appt-card {{ $isUnpaidRazorpay ? 'payment-pending-card' : '' }}" aria-label="{{ $a->service?->name }}">
        <div style="display:flex;min-height:90px;">
            <div class="appt-date-block">
                <div class="appt-day">{{ \Carbon\Carbon::parse($a->appointment_date)->format('d') }}</div>
                <div class="appt-mon">{{ \Carbon\Carbon::parse($a->appointment_date)->format('M') }}</div>
            </div>

            <div style="flex:1;padding:1rem;display:flex;align-items:center;gap:1rem;min-width:0;">
                {{-- Top row: name + status --}}
              {{-- Left: info --}}
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.3rem;">
                        <h3 style="font-size:0.9rem;font-weight:500;color:var(--text);margin:0;">{{ $a->service?->name }}</h3>
                        <span class="status-badge {{ match($a->status) { 'completed' => 'badge-active', 'checked_in' => 'badge-active', 'cancelled' => 'badge-suspended', 'no_show' => 'badge-suspended', default => 'badge-trial' } }}" style="font-size:0.6rem;">
                            @if($a->status === 'pending' && $a->payment_status === 'paid')
                                Paid — Awaiting Confirmation
                            @elseif($a->status === 'no_show')
                                No Show
                            @else
                                {{ ucfirst(str_replace('_', ' ', $a->status)) }}
                            @endif
                        </span>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:0.8rem;font-size:0.7rem;color:var(--text-3);">
                        <span><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($a->start_time)->format('h:i A') }}</span>
                        <span><i class="bi bi-person"></i> {{ $a->staff?->user?->name ?? 'TBD' }}</span>
                        <span style="color:var(--gold);"><i class="bi bi-currency-rupee"></i> {{ number_format($a->amount) }}</span>
                    </div>
                </div>

                {{-- Right: action buttons --}}
                <div style="display:flex;align-items:center;gap:0.5rem;flex-shrink:0;">

                    {{-- Pay Now — sirf unpaid razorpay ke liye --}}
                    @if($isUnpaidRazorpay)
                    <a href="{{ route('customer.book.confirmed', [$subdomain, $a->id]) }}" class="btn-pay-now">
                        <i class="bi bi-credit-card"></i> Pay Now
                    </a>

                    {{-- Cancel / Lock --}}
                    @elseif(in_array($a->status, ['pending', 'confirmed']))
                    @php
                        $apptDateTime = \Carbon\Carbon::parse(\Carbon\Carbon::parse($a->appointment_date)->toDateString().' '.$a->start_time);
                        $canCancel = \Carbon\Carbon::now()->diffInHours($apptDateTime, false) >= 2;
                    @endphp
                    @if($canCancel)
                    <form action="{{ route('customer.appointments.cancel', [$subdomain, $a->id]) }}" method="POST" class="cancel-form">
                        @csrf
                        <button type="submit" class="btn-cust-danger">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    </form>
                    @else
                    <span style="font-size:0.65rem;color:var(--text-3);text-align:center;"><i class="bi bi-lock"></i><br>2hr lock</span>
                    @endif

                    {{-- Review --}}
                    @elseif($a->status === 'completed' && !$a->review)
                    <a href="{{ route('customer.review.create', [$subdomain, $a->id]) }}" class="btn-review">
                        <i class="bi bi-star"></i> Review
                    </a>
                    @endif

                    {{-- Invoice --}}
                    @if($a->payment_status === 'paid')
                    <a href="{{ route('customer.invoice.download', [$subdomain, $a->id]) }}" class="btn-review" style="border-color:var(--gold);color:var(--gold);">
                        <i class="bi bi-download"></i> Invoice
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </article>
    @empty
    <div style="text-align:center;padding:3rem 1rem;color:var(--text-3);">
        <i class="bi bi-calendar-x" style="font-size:2rem;opacity:0.4;"></i>
        <p style="font-size:0.8rem;margin-top:1rem;">No appointments found.</p>
        <a href="{{ route('customer.home', $subdomain) }}" class="btn-lux-gold btn-sm" style="display:inline-flex;margin-top:1rem;">View Services</a>
    </div>
    @endforelse
</div>

<div style="margin-top:1.5rem;">
    <x-tables.pagination :paginator="$appointments" />
</div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.cancel-form').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm('Are you sure you want to cancel this appointment?')) e.preventDefault();
        });
    });

</script>
@endpush
