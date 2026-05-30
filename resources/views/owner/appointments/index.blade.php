@extends('owner.layouts.app')
@section('title','All Appointments')
@section('page-title','All Appointments')
@section('topbar-actions')
  <button class="btn-ghost-sm" onclick="document.getElementById('bookModal').style.display='flex'">
    <i class="bi bi-calendar-plus"></i> Book Now
  </button>
@endsection

@push('styles')
<style>
  .filter-strip{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;margin-bottom:1.4rem}
  .f-input{background:var(--bg-input);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.78rem;padding:.42rem .9rem;outline:none;transition:border-color .25s}
  .f-input:focus{border-color:var(--gold)}
  .f-input::placeholder{color:var(--text-3)}
  select.f-input option{background:var(--bg-card)}
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);z-index:500;align-items:center;justify-content:center;padding:1rem}
  .modal-box{background:var(--bg-card);border:1px solid var(--border-2);border-radius:16px;padding:2rem;width:100%;max-width:520px;position:relative;max-height:90vh;overflow-y:auto}
  .fl-group{position:relative;margin-bottom:1.2rem}
  .fl-group label{display:block;font-size:.65rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-3);margin-bottom:.4rem}
  .fl-group input,.fl-group select,.fl-group textarea{width:100%;background:var(--bg-input);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.82rem;padding:.7rem 1rem;outline:none;transition:border-color .25s}
  .fl-group input:focus,.fl-group select:focus{border-color:var(--gold)}
  .fl-group select option{background:var(--bg-card)}
  .aa-btn{width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:transparent;display:inline-flex;align-items:center;justify-content:center;color:var(--text-3);cursor:pointer;transition:all .2s;font-size:.75rem;text-decoration:none}
  .aa-btn:hover{border-color:var(--gold);color:var(--gold);background:var(--gold-dim)}
  .aa-btn.danger:hover{border-color:var(--rose);color:var(--rose);background:var(--rose-dim)}
  .aa-btn.success:hover{border-color:var(--emerald);color:var(--emerald);background:var(--emerald-dim)}
</style>
@endpush

@section('content')
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3 fade-up s1"><div class="card-lux kpi-pad" style="border-top:2px solid var(--gold)"><div class="kpi-label">Total</div><div class="kpi-value" style="color:var(--gold)">{{ $stats['total'] }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s2"><div class="card-lux kpi-pad" style="border-top:2px solid var(--emerald)"><div class="kpi-label">Today</div><div class="kpi-value" style="color:var(--emerald)">{{ $stats['today'] }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s3"><div class="card-lux kpi-pad" style="border-top:2px solid var(--amber)"><div class="kpi-label">Pending</div><div class="kpi-value" style="color:var(--amber)">{{ $stats['pending'] }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s4"><div class="card-lux kpi-pad" style="border-top:2px solid var(--teal-light)"><div class="kpi-label">Completed</div><div class="kpi-value" style="color:var(--teal-light)">{{ $stats['completed'] }}</div></div></div>
</div>

<form method="GET" action="{{ route('owner.appointments.index') }}" id="filterForm">
  <div class="filter-strip fade-up s2">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer…" class="f-input" style="min-width:180px" />
    <input type="date" name="date" value="{{ request('date') }}" class="f-input" />
    <select name="status" class="f-input" onchange="document.getElementById('filterForm').submit()">
      <option value="all" {{ request('status','all')==='all'?'selected':'' }}>All Status</option>
      <option value="pending"   {{ request('status')==='pending'?'selected':'' }}>Pending</option>
      <option value="confirmed" {{ request('status')==='confirmed'?'selected':'' }}>Confirmed</option>
      <option value="completed" {{ request('status')==='completed'?'selected':'' }}>Completed</option>
      <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>Cancelled</option>
    </select>
    <select name="staff_id" class="f-input" onchange="document.getElementById('filterForm').submit()">
      <option value="">All Staff</option>
      @foreach($staffList as $s)
        <option value="{{ $s->id }}" {{ request('staff_id')==$s->id?'selected':'' }}>{{ $s->user?->name }}</option>
      @endforeach
    </select>
    <button type="submit" class="btn-gold-sm"><i class="bi bi-search"></i> Filter</button>
    <a href="{{ route('owner.appointments.index') }}" class="btn-ghost-sm"><i class="bi bi-x"></i> Clear</a>
  </div>
</form>

<div class="card-lux fade-up s3">
  <div style="overflow-x:auto">
    <table class="lux-table">
      <thead><tr><th>#</th><th>Customer</th><th>Service</th><th>Staff</th><th>Date</th><th>Time</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @forelse($appointments as $a)
        <tr>
          <td style="color:var(--text-3)">{{ $a->id }}</td>
          <td style="color:var(--text);font-weight:400">{{ $a->customer?->name ?? 'Walk-in' }}</td>
          <td>{{ $a->service?->name }}</td>
          <td>{{ $a->staff?->user?->name }}</td>
          <td>{{ \Carbon\Carbon::parse($a->appointment_date)->format('d M Y') }}</td>
          <td style="font-family:var(--ff-display);color:var(--gold)">{{ \Carbon\Carbon::parse($a->start_time)->format('h:i A') }}</td>
          <td>
            <span class="lux-badge {{ match($a->status){ 'completed'=>'lb-green','cancelled'=>'lb-red','confirmed'=>'lb-gold',default=>'lb-amber' } }}">
              {{ ucfirst($a->status) }}
            </span>
          </td>
          <td>
            <div style="display:flex;gap:.3rem">
              @if($a->status !== 'completed' && $a->status !== 'cancelled')
                <form method="POST" action="{{ route('owner.appointments.status', $a->id) }}" style="display:inline">
                  @csrf @method('POST')
                  <input type="hidden" name="status" value="completed">
                  <button type="submit" class="aa-btn success" title="Mark Completed"><i class="bi bi-check-lg"></i></button>
                </form>
                <form method="POST" action="{{ route('owner.appointments.status', $a->id) }}" style="display:inline">
                  @csrf @method('POST')
                  <input type="hidden" name="status" value="cancelled">
                  <button type="submit" class="aa-btn danger" title="Cancel" onclick="return confirm('Cancel karein?')"><i class="bi bi-x-lg"></i></button>
                </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-3)"><i class="bi bi-calendar-x" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>Koi appointment nahi mili</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{-- Pagination --}}
  <div style="padding:.8rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
    <div style="font-size:.72rem;color:var(--text-3)">Showing {{ $appointments->firstItem() }}–{{ $appointments->lastItem() }} of {{ $appointments->total() }}</div>
    <div style="display:flex;gap:.3rem">
      @if(!$appointments->onFirstPage())
        <a href="{{ $appointments->previousPageUrl() }}" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-2);text-decoration:none;font-size:.8rem"><i class="bi bi-chevron-left"></i></a>
      @endif
      @foreach($appointments->getUrlRange(max(1,$appointments->currentPage()-1),min($appointments->lastPage(),$appointments->currentPage()+1)) as $pg=>$url)
        <a href="{{ $url }}" style="width:30px;height:30px;border-radius:6px;border:1px solid {{ $pg==$appointments->currentPage() ? 'var(--gold)' : 'var(--border)' }};background:{{ $pg==$appointments->currentPage() ? 'var(--gold)' : 'transparent' }};color:{{ $pg==$appointments->currentPage() ? '#1a1400' : 'var(--text-2)' }};display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.78rem">{{ $pg }}</a>
      @endforeach
      @if($appointments->hasMorePages())
        <a href="{{ $appointments->nextPageUrl() }}" style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-2);text-decoration:none;font-size:.8rem"><i class="bi bi-chevron-right"></i></a>
      @endif
    </div>
  </div>
</div>

{{-- Book Modal --}}
<div class="modal-overlay" id="bookModal" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
      <div style="font-family:var(--ff-display);font-size:1.3rem;color:var(--text)">New Booking</div>
      <button onclick="document.getElementById('bookModal').style.display='none'" style="background:none;border:none;color:var(--text-3);font-size:1.2rem;cursor:pointer"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="{{ route('owner.appointments.store') }}">
      @csrf
      <div class="row g-3">
        <div class="col-12">
          <div class="fl-group">
            <label>Customer *</label>
            <select name="customer_id" required>
              <option value="">Select Customer</option>
              @foreach(\App\Models\User::where('tenant_id',auth()->user()->tenant_id)->role('customer')->get() as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-6">
          <div class="fl-group">
            <label>Service *</label>
            <select name="service_id" required>
              <option value="">Select Service</option>
              @foreach(\App\Models\Service::where('tenant_id',auth()->user()->tenant_id)->where('is_active',true)->get() as $s)
                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->duration_minutes }}min)</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-6">
          <div class="fl-group">
            <label>Staff *</label>
            <select name="staff_id" required>
              <option value="">Select Staff</option>
              @foreach(\App\Models\Staff::with('user')->where('tenant_id',auth()->user()->tenant_id)->where('is_available',true)->get() as $s)
                <option value="{{ $s->id }}">{{ $s->user?->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-6">
          <div class="fl-group">
            <label>Date *</label>
            <input type="date" name="appointment_date" min="{{ date('Y-m-d') }}" required />
          </div>
        </div>
        <div class="col-6">
          <div class="fl-group">
            <label>Time *</label>
            <input type="time" name="start_time" required />
          </div>
        </div>
        <div class="col-12">
          <div class="fl-group">
            <label>Notes</label>
            <input type="text" name="notes" placeholder="Optional notes…" />
          </div>
        </div>
      </div>
      <div style="display:flex;gap:.8rem;margin-top:1rem">
        <button type="button" onclick="document.getElementById('bookModal').style.display='none'" class="btn-ghost-sm" style="flex:1;justify-content:center">Cancel</button>
        <button type="submit" class="btn-gold-sm" style="flex:2;justify-content:center"><i class="bi bi-calendar-check"></i> Book Appointment</button>
      </div>
    </form>
  </div>
</div>
@endsection
