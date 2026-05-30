@extends('owner.layouts.app')
@section('title',"Today's Bookings")
@section('page-title',"Today's Bookings")
@section('page-sub', now()->format('l, d M Y'))
@section('topbar-actions')
  <button class="btn-gold-sm" onclick="document.getElementById('bookModal').style.display='flex'">
    <i class="bi bi-plus-lg"></i> Quick Book
  </button>
@endsection

@push('styles')
<style>
  .appt-block{background:var(--bg-card-2);border:1px solid var(--border-2);border-radius:10px;padding:.85rem 1rem;display:flex;align-items:center;gap:.85rem;transition:all .25s;position:relative;overflow:hidden;margin-bottom:.6rem}
  .appt-block::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:0 2px 2px 0}
  .appt-block.confirmed::before{background:var(--gold)}
  .appt-block.completed::before{background:var(--emerald)}
  .appt-block.cancelled::before{background:var(--rose)}
  .appt-block.pending::before{background:var(--amber)}
  .appt-block:hover{border-color:var(--border-2);background:rgba(255,255,255,.02)}
  .ab-time{font-family:var(--ff-display);font-size:1.05rem;color:var(--gold);min-width:54px;flex-shrink:0}
  .ab-av{width:36px;height:36px;border-radius:50%;background:var(--gold-dim);display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:.9rem;color:var(--gold);flex-shrink:0}
  .ab-name{font-size:.84rem;font-weight:500;color:var(--text)}
  .ab-detail{font-size:.7rem;color:var(--text-3);margin-top:.1rem}
  .ab-actions{display:flex;gap:.3rem;margin-left:auto;flex-shrink:0}
  .aa-btn{width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;color:var(--text-3);cursor:pointer;transition:all .2s;font-size:.75rem}
  .aa-btn:hover{border-color:var(--gold);color:var(--gold);background:var(--gold-dim)}
  .aa-btn.danger:hover{border-color:var(--rose);color:var(--rose);background:var(--rose-dim)}
  .time-slot-hdr{font-size:.6rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:var(--text-3);margin:1.2rem 0 .6rem;display:flex;align-items:center;gap:.7rem}
  .time-slot-hdr::after{content:'';flex:1;height:1px;background:var(--border)}
  .fl-group{margin-bottom:1rem}
  .fl-group label{display:block;font-size:.65rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-3);margin-bottom:.4rem}
  .fl-group input,.fl-group select{width:100%;background:var(--bg-input);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.82rem;padding:.7rem 1rem;outline:none}
  .fl-group input:focus,.fl-group select:focus{border-color:var(--gold)}
  .fl-group select option{background:var(--bg-card)}
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);z-index:500;align-items:center;justify-content:center;padding:1rem}
  .modal-box{background:var(--bg-card);border:1px solid var(--border-2);border-radius:16px;padding:2rem;width:100%;max-width:480px;max-height:90vh;overflow-y:auto}
</style>
@endpush

@section('content')
{{-- Stats row --}}
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3 fade-up s1"><div class="card-lux kpi-pad gold-border"><div class="kpi-label"><span class="live-dot"></span> Total Today</div><div class="kpi-value" style="color:var(--gold)">{{ $stats['total'] }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s2"><div class="card-lux kpi-pad" style="border-top:2px solid var(--amber)"><div class="kpi-label">Pending</div><div class="kpi-value" style="color:var(--amber)">{{ $stats['pending'] }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s3"><div class="card-lux kpi-pad" style="border-top:2px solid var(--emerald)"><div class="kpi-label">Completed</div><div class="kpi-value" style="color:var(--emerald)">{{ $stats['completed'] }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s4"><div class="card-lux kpi-pad" style="border-top:2px solid var(--rose)"><div class="kpi-label">Cancelled</div><div class="kpi-value" style="color:var(--rose)">{{ $stats['cancelled'] }}</div></div></div>
</div>

<div class="card-lux p-4 fade-up s2">
  @if($appointments->isEmpty())
    <div style="text-align:center;padding:3rem;color:var(--text-3)">
      <i class="bi bi-calendar-x" style="font-size:2.5rem;display:block;margin-bottom:1rem"></i>
      Aaj koi booking nahi hai
    </div>
  @else
    @php
      $grouped = $appointments->groupBy(fn($a) => \Carbon\Carbon::parse($a->start_time)->format('A') === 'AM' ? 'Morning' : (\Carbon\Carbon::parse($a->start_time)->hour < 17 ? 'Afternoon' : 'Evening'));
    @endphp
    @foreach($grouped as $period => $appts)
      <div class="time-slot-hdr">{{ $period }} · {{ $appts->count() }} bookings</div>
      @foreach($appts as $a)
        <div class="appt-block {{ $a->status }}">
          <div class="ab-time">{{ \Carbon\Carbon::parse($a->start_time)->format('h:i') }}<div style="font-size:.6rem;color:var(--text-3)">{{ \Carbon\Carbon::parse($a->start_time)->format('A') }}</div></div>
          <div class="ab-av">{{ strtoupper(substr($a->customer?->name ?? 'W', 0, 2)) }}</div>
          <div style="flex:1;min-width:0">
            <div class="ab-name">{{ $a->customer?->name ?? 'Walk-in' }}</div>
            <div class="ab-detail">{{ $a->service?->name }} · {{ $a->staff?->user?->name }} · {{ $a->service?->duration_minutes }}min</div>
          </div>
          <span class="lux-badge {{ match($a->status){ 'completed'=>'lb-green','cancelled'=>'lb-red','confirmed'=>'lb-gold',default=>'lb-amber' } }}" style="flex-shrink:0">{{ ucfirst($a->status) }}</span>
          <div class="ab-actions">
            @if(!in_array($a->status, ['completed','cancelled']))
              <form method="POST" action="{{ route('owner.appointments.status', $a->id) }}" style="display:contents">
                @csrf @method('POST')
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="aa-btn" title="Mark Done"><i class="bi bi-check-lg"></i></button>
              </form>
              <form method="POST" action="{{ route('owner.appointments.status', $a->id) }}" style="display:contents">
                @csrf @method('POST')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="aa-btn danger" title="Cancel" onclick="return confirm('Cancel karein?')"><i class="bi bi-x-lg"></i></button>
              </form>
            @endif
          </div>
        </div>
      @endforeach
    @endforeach
  @endif
</div>

{{-- Quick Book Modal --}}
<div class="modal-overlay" id="bookModal" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
      <div style="font-family:var(--ff-display);font-size:1.3rem;color:var(--text)">Quick Book</div>
      <button onclick="document.getElementById('bookModal').style.display='none'" style="background:none;border:none;color:var(--text-3);font-size:1.2rem;cursor:pointer"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="{{ route('owner.appointments.store') }}">
      @csrf
      <div class="fl-group">
        <label>Customer *</label>
        <select name="customer_id" required>
          <option value="">Select Customer</option>
          @foreach(\App\Models\User::where('tenant_id',auth()->user()->tenant_id)->role('customer')->get() as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="row g-2">
        <div class="col-6"><div class="fl-group"><label>Service *</label><select name="service_id" required><option value="">Select</option>@foreach(\App\Models\Service::where('tenant_id',auth()->user()->tenant_id)->where('is_active',true)->get() as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div></div>
        <div class="col-6"><div class="fl-group"><label>Staff *</label><select name="staff_id" required><option value="">Select</option>@foreach(\App\Models\Staff::with('user')->where('tenant_id',auth()->user()->tenant_id)->where('is_available',true)->get() as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach</select></div></div>
        <div class="col-6"><div class="fl-group"><label>Date *</label><input type="date" name="appointment_date" value="{{ date('Y-m-d') }}" required /></div></div>
        <div class="col-6"><div class="fl-group"><label>Time *</label><input type="time" name="start_time" required /></div></div>
      </div>
      <div style="display:flex;gap:.8rem;margin-top:.8rem">
        <button type="button" onclick="document.getElementById('bookModal').style.display='none'" class="btn-ghost-sm" style="flex:1;justify-content:center">Cancel</button>
        <button type="submit" class="btn-gold-sm" style="flex:2;justify-content:center"><i class="bi bi-calendar-check"></i> Book Now</button>
      </div>
    </form>
  </div>
</div>
@endsection
