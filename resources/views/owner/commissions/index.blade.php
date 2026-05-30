@extends('owner.layouts.app')
@section('title','Commissions')
@section('page-title','Commissions')
@section('page-sub', $currentMonth)
@section('topbar-actions')
  <form method="GET" action="{{ route('owner.commissions.index') }}" style="display:flex;gap:.5rem;align-items:center">
    <select name="month" style="background:var(--bg-input);border:1px solid var(--border-2);border-radius:7px;color:var(--text-2);font-family:var(--ff-body);font-size:.75rem;padding:.42rem .8rem;outline:none" onchange="this.form.submit()">
      @foreach($months as $m)
        <option value="{{ $m['month'] }}" data-year="{{ $m['year'] }}" {{ $m['month']==$month && $m['year']==$year ? 'selected' : '' }}>{{ $m['label'] }}</option>
      @endforeach
    </select>
    <input type="hidden" name="year" id="yearInput" value="{{ $year }}" />
  </form>
@endsection

@push('styles')
<style>
  .staff-comm-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1.5rem;transition:all var(--transition);position:relative;overflow:hidden}
  .staff-comm-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent)}
  .staff-comm-card:hover{border-color:rgba(201,169,110,.2);box-shadow:0 8px 40px rgba(0,0,0,.4)}
  .comm-av{width:48px;height:48px;border-radius:50%;background:var(--gold-grad);display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:1.1rem;color:#1a1400;flex-shrink:0}
</style>
@endpush

@section('content')
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3 fade-up s1"><div class="card-lux kpi-pad gold-border"><div class="kpi-label">Total Commission</div><div class="kpi-value" style="color:var(--gold)">₹{{ number_format($stats['total_commission']) }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s2"><div class="card-lux kpi-pad" style="border-top:2px solid var(--rose)"><div class="kpi-label">Pending Payout</div><div class="kpi-value" style="color:var(--rose)">₹{{ number_format($stats['pending']) }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s3"><div class="card-lux kpi-pad" style="border-top:2px solid var(--emerald)"><div class="kpi-label">Paid Out</div><div class="kpi-value" style="color:var(--emerald)">₹{{ number_format($stats['paid']) }}</div><span class="kpi-trend trend-up">{{ $stats['staff_paid_count'] }} staff paid</span></div></div>
  <div class="col-6 col-md-3 fade-up s4"><div class="card-lux kpi-pad" style="border-top:2px solid var(--amber)"><div class="kpi-label">Pending Staff</div><div class="kpi-value" style="color:var(--amber)">{{ $stats['staff_pending_count'] }}</div><span class="kpi-trend trend-down">Need payout</span></div></div>
</div>

<div class="row g-3 fade-up s2">
  @forelse($summary as $item)
  <div class="col-md-6 col-lg-4">
    <div class="staff-comm-card">
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.2rem">
        <div class="comm-av">{{ strtoupper(substr($item['staff']->user?->name ?? 'S', 0, 2)) }}</div>
        <div style="flex:1">
          <div style="font-size:.92rem;font-weight:500;color:var(--text)">{{ $item['staff']->user?->name }}</div>
          <div style="font-size:.68rem;color:var(--text-3)">{{ $item['total_services'] }} services · {{ $item['staff']->commission_percent }}% rate</div>
        </div>
        <div style="text-align:right">
          <div style="font-size:.6rem;color:var(--text-3);letter-spacing:.15em;text-transform:uppercase">Commission</div>
          <div style="font-family:var(--ff-display);font-size:1.6rem;color:var(--gold)">{{ $item['staff']->commission_percent }}%</div>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-bottom:.8rem">
        <div><div style="font-size:.6rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.1em">Total Earned</div><div style="font-size:1rem;font-family:var(--ff-display);color:var(--text)">₹{{ number_format($item['total_earned']) }}</div></div>
        <div style="text-align:right"><div style="font-size:.6rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.1em">Pending</div><div style="font-size:1rem;font-family:var(--ff-display);color:var(--rose)">₹{{ number_format($item['pending_amount']) }}</div></div>
        <div style="text-align:right"><div style="font-size:.6rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.1em">Paid</div><div style="font-size:1rem;font-family:var(--ff-display);color:var(--emerald)">₹{{ number_format($item['paid_amount']) }}</div></div>
      </div>
      @if($item['has_pending'])
        <form method="POST" action="{{ route('owner.commissions.mark-paid', $item['staff']->id) }}">
          @csrf
          <input type="hidden" name="month" value="{{ $month }}" />
          <input type="hidden" name="year"  value="{{ $year }}" />
          <button type="submit" class="btn-gold-sm w-100 justify-content-center" onclick="return confirm('Mark as paid karein?')">
            <i class="bi bi-check-circle-fill"></i> Mark Paid — ₹{{ number_format($item['pending_amount']) }}
          </button>
        </form>
      @else
        <div style="text-align:center;font-size:.75rem;color:var(--emerald);padding:.5rem 0">
          <i class="bi bi-check-circle-fill"></i> All paid for {{ $currentMonth }}
        </div>
      @endif
    </div>
  </div>
  @empty
  <div class="col-12" style="text-align:center;padding:3rem;color:var(--text-3)">
    <i class="bi bi-cash-stack" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
    {{ $currentMonth }} mein koi commission nahi
  </div>
  @endforelse
</div>
@endsection

@push('scripts')
<script>
document.querySelector('select[name="month"]')?.addEventListener('change', function() {
  const opt = this.options[this.selectedIndex];
  document.getElementById('yearInput').value = opt.dataset.year;
  this.form.submit();
});
</script>
@endpush
