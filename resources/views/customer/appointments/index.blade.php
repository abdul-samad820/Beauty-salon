@extends('customer.layouts.app')
@section('title', 'My Bookings')

@push('styles')
<style>
  .appt-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:all .3s;position:relative;}
  .appt-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;}
  .appt-card.confirmed::before{background:var(--gold);}
  .appt-card.completed::before{background:var(--emerald);}
  .appt-card.cancelled::before{background:var(--rose);}
  .appt-card.pending::before{background:var(--amber);}
  .appt-card:hover{border-color:rgba(201,169,110,.2);box-shadow:0 8px 30px rgba(0,0,0,.4);}
  .appt-body{padding:1.2rem 1.4rem;}
  .appt-date-badge{display:inline-flex;align-items:center;gap:.4rem;background:var(--gold-dim);border:1px solid rgba(201,169,110,.15);border-radius:20px;padding:.2rem .7rem;font-size:.65rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);margin-bottom:.7rem;}
  .appt-service{font-size:.95rem;font-weight:500;color:var(--text);margin-bottom:.3rem;}
  .appt-meta{font-size:.72rem;color:var(--text-3);display:flex;flex-wrap:wrap;gap:.8rem;margin-bottom:.9rem;}
  .appt-meta span{display:flex;align-items:center;gap:.3rem;}
  .empty-state{text-align:center;padding:4rem 2rem;color:var(--text-3);}
  .empty-icon{font-size:3rem;display:block;margin-bottom:1rem;opacity:.4;}
  .empty-title{font-family:var(--ff-display);font-size:1.4rem;color:var(--text-2);margin-bottom:.5rem;}
</style>
@endpush

@section('content')

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.8rem;" class="fade-up s1">
  <div>
    <div style="font-family:var(--ff-display);font-size:1.6rem;color:var(--text);">My Bookings</div>
    <div style="font-size:.72rem;color:var(--text-3);">Aapki saari appointments</div>
  </div>
  <a href="{{ route('customer.home', $subdomain) }}" class="btn-cust-gold">
    <i class="bi bi-plus-lg"></i> New Booking
  </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3 fade-up s1">
    <div class="c-card" style="border-top:2px solid var(--gold)">
      <div class="c-card-body">
        <div style="font-size:.62rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--text-3);margin-bottom:.5rem">Total</div>
        <div style="font-family:var(--ff-display);font-size:2rem;color:var(--gold)">{{ $stats['total'] }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3 fade-up s2">
    <div class="c-card" style="border-top:2px solid var(--amber)">
      <div class="c-card-body">
        <div style="font-size:.62rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--text-3);margin-bottom:.5rem">Upcoming</div>
        <div style="font-family:var(--ff-display);font-size:2rem;color:var(--amber)">{{ $stats['upcoming'] }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3 fade-up s3">
    <div class="c-card" style="border-top:2px solid var(--emerald)">
      <div class="c-card-body">
        <div style="font-size:.62rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--text-3);margin-bottom:.5rem">Completed</div>
        <div style="font-family:var(--ff-display);font-size:2rem;color:var(--emerald)">{{ $stats['completed'] }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3 fade-up s4">
    <div class="c-card" style="border-top:2px solid var(--rose)">
      <div class="c-card-body">
        <div style="font-size:.62rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--text-3);margin-bottom:.5rem">Cancelled</div>
        <div style="font-family:var(--ff-display);font-size:2rem;color:var(--rose)">{{ $stats['cancelled'] }}</div>
      </div>
    </div>
  </div>
</div>

{{-- Appointments list --}}
@if($appointments->isEmpty())
  <div class="empty-state fade-up s2">
    <i class="bi bi-calendar-x empty-icon"></i>
    <div class="empty-title">Koi booking nahi mili</div>
    <p style="font-size:.8rem;margin-bottom:1.5rem;">Apni pehli appointment book karein!</p>
    <a href="{{ route('customer.home', $subdomain) }}" class="btn-cust-gold">
      <i class="bi bi-plus-lg"></i> Book Now
    </a>
  </div>
@else
  <div style="display:flex;flex-direction:column;gap:.9rem;">
    @foreach($appointments as $a)
    <div class="appt-card {{ $a->status }} fade-up s2">
      <div class="appt-body">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
          <div>
            <div class="appt-date-badge">
              <i class="bi bi-calendar3"></i>
              {{ \Carbon\Carbon::parse($a->appointment_date)->format('d M Y') }}
              &nbsp;·&nbsp;
              {{ \Carbon\Carbon::parse($a->start_time)->format('h:i A') }}
            </div>
            <div class="appt-service">{{ $a->service?->name }}</div>
            <div class="appt-meta">
              <span><i class="bi bi-person-fill"></i> {{ $a->staff?->user?->name }}</span>
              <span><i class="bi bi-clock"></i> {{ $a->service?->duration_minutes }} min</span>
              <span><i class="bi bi-currency-rupee"></i> {{ number_format($a->service?->price ?? 0) }}</span>
            </div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem">
            <span class="c-badge {{ match($a->status){ 'completed'=>'cb-green','cancelled'=>'cb-red','confirmed'=>'cb-gold',default=>'cb-amber' } }}">
              <i class="bi bi-circle-fill" style="font-size:.35rem"></i>
              {{ ucfirst($a->status) }}
            </span>
            @if(!in_array($a->status, ['completed','cancelled']) && \Carbon\Carbon::parse($a->appointment_date)->isFuture())
              <form method="POST" action="{{ route('customer.appointments.cancel', [$subdomain, $a->id]) }}">
                @csrf
                <button type="submit" class="btn-cust-danger" onclick="return confirm('Cancel karein?')">
                  <i class="bi bi-x-circle"></i> Cancel
                </button>
              </form>
            @endif
          </div>
        </div>
        @if($a->notes)
          <div style="margin-top:.6rem;font-size:.72rem;color:var(--text-3);background:rgba(255,255,255,.02);border-radius:6px;padding:.5rem .8rem;">
            <i class="bi bi-sticky-fill" style="color:var(--gold)"></i> {{ $a->notes }}
          </div>
        @endif
      </div>
    </div>
    @endforeach
  </div>

  {{-- Pagination --}}
  <div style="display:flex;justify-content:center;gap:.3rem;margin-top:1.5rem;flex-wrap:wrap;">
    @if(!$appointments->onFirstPage())
      <a href="{{ $appointments->previousPageUrl() }}" style="width:32px;height:32px;border-radius:7px;border:1px solid var(--border-2);display:flex;align-items:center;justify-content:center;color:var(--text-2);text-decoration:none;font-size:.8rem"><i class="bi bi-chevron-left"></i></a>
    @endif
    @foreach($appointments->getUrlRange(1, $appointments->lastPage()) as $pg => $url)
      <a href="{{ $url }}" style="width:32px;height:32px;border-radius:7px;border:1px solid {{ $pg==$appointments->currentPage() ? 'var(--gold)' : 'var(--border-2)' }};background:{{ $pg==$appointments->currentPage() ? 'var(--gold)' : 'transparent' }};color:{{ $pg==$appointments->currentPage() ? '#1a1400' : 'var(--text-2)' }};display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.78rem">{{ $pg }}</a>
    @endforeach
    @if($appointments->hasMorePages())
      <a href="{{ $appointments->nextPageUrl() }}" style="width:32px;height:32px;border-radius:7px;border:1px solid var(--border-2);display:flex;align-items:center;justify-content:center;color:var(--text-2);text-decoration:none;font-size:.8rem"><i class="bi bi-chevron-right"></i></a>
    @endif
  </div>
@endif
@endsection
