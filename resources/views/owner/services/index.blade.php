@extends('owner.layouts.app')
@section('title','Services')
@section('page-title','Services')
@section('topbar-actions')
  <button class="btn-gold-sm" onclick="document.getElementById('addModal').style.display='flex'">
    <i class="bi bi-plus-lg"></i> Add Service
  </button>
@endsection

@push('styles')
<style>
  .service-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:all var(--transition);position:relative}
  .service-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent)}
  .service-card:hover{border-color:rgba(201,169,110,.25);box-shadow:0 12px 50px rgba(0,0,0,.5);transform:translateY(-4px)}
  .svc-cat-badge{display:inline-flex;align-items:center;font-size:.6rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;padding:.2rem .65rem;border-radius:20px;margin-bottom:.8rem}
  .svc-price{font-family:var(--ff-display);font-size:1.5rem;font-weight:400;color:var(--gold)}
  .svc-dur{font-size:.72rem;color:var(--text-3);margin-top:.3rem}
  .svc-actions{display:flex;gap:.4rem;margin-top:1rem}
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);z-index:500;align-items:center;justify-content:center;padding:1rem}
  .modal-box{background:var(--bg-card);border:1px solid var(--border-2);border-radius:16px;padding:2rem;width:100%;max-width:500px;max-height:90vh;overflow-y:auto}
  .fl-group{margin-bottom:1.1rem}
  .fl-group label{display:block;font-size:.65rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-3);margin-bottom:.4rem}
  .fl-group input,.fl-group select,.fl-group textarea{width:100%;background:var(--bg-input);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.82rem;padding:.7rem 1rem;outline:none;transition:border-color .25s}
  .fl-group input:focus,.fl-group select:focus,.fl-group textarea:focus{border-color:var(--gold)}
  .fl-group select option{background:var(--bg-card)}
  .cat-colors{hair:'var(--gold)',skin:'var(--purple)',nail:'var(--teal-light)',bridal:'var(--rose)',massage:'var(--emerald)',other:'var(--text-2)'}
</style>
@endpush

@section('content')
<div class="row g-3 mb-3">
  <div class="col-4 fade-up s1"><div class="card-lux kpi-pad gold-border"><div class="kpi-label">Total</div><div class="kpi-value" style="color:var(--gold)">{{ $stats['total'] }}</div></div></div>
  <div class="col-4 fade-up s2"><div class="card-lux kpi-pad" style="border-top:2px solid var(--emerald)"><div class="kpi-label">Active</div><div class="kpi-value" style="color:var(--emerald)">{{ $stats['active'] }}</div></div></div>
  <div class="col-4 fade-up s3"><div class="card-lux kpi-pad" style="border-top:2px solid var(--text-3)"><div class="kpi-label">Inactive</div><div class="kpi-value" style="color:var(--text-3)">{{ $stats['inactive'] }}</div></div></div>
</div>

{{-- Filter strip --}}
<form method="GET" action="{{ route('owner.services.index') }}" style="display:flex;flex-wrap:wrap;gap:.6rem;margin-bottom:1.4rem" class="fade-up s2">
  <input type="text" name="search" value="{{ request('search') }}" placeholder="Search services…" style="background:var(--bg-input);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.78rem;padding:.42rem .9rem;outline:none;min-width:180px" />
  <select name="category" style="background:var(--bg-input);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.78rem;padding:.42rem .9rem;outline:none" onchange="this.form.submit()">
    <option value="">All Categories</option>
    @foreach(['hair'=>'Hair','skin'=>'Skin','nail'=>'Nail','bridal'=>'Bridal','massage'=>'Massage','other'=>'Other'] as $v=>$l)
      <option value="{{ $v }}" {{ request('category')===$v?'selected':'' }}>{{ $l }}</option>
    @endforeach
  </select>
  <button type="submit" class="btn-gold-sm"><i class="bi bi-search"></i></button>
  <a href="{{ route('owner.services.index') }}" class="btn-ghost-sm"><i class="bi bi-x"></i></a>
</form>

{{-- Service cards grid --}}
<div class="row g-3 fade-up s3">
  @forelse($services as $svc)
  <div class="col-sm-6 col-lg-4">
    <div class="service-card">
      <div style="padding:1.4rem">
        @php
          $catColor = match($svc->category) { 'hair'=>'var(--gold)','skin'=>'var(--purple)','nail'=>'var(--teal-light)','bridal'=>'var(--rose)','massage'=>'var(--emerald)',default=>'var(--text-2)' };
          $catBg    = match($svc->category) { 'hair'=>'var(--gold-dim)','skin'=>'var(--purple-dim)','nail'=>'var(--teal-dim)','bridal'=>'var(--rose-dim)','massage'=>'var(--emerald-dim)',default=>'rgba(255,255,255,.05)' };
        @endphp
        <span class="svc-cat-badge" style="background:{{ $catBg }};color:{{ $catColor }}">{{ ucfirst($svc->category) }}</span>
        @if(!$svc->is_active)
          <span class="lux-badge lb-muted" style="float:right">Inactive</span>
        @endif
        <div style="font-size:.95rem;font-weight:500;color:var(--text);margin-bottom:.4rem">{{ $svc->name }}</div>
        @if($svc->description)
          <div style="font-size:.72rem;color:var(--text-3);margin-bottom:.8rem;line-height:1.5">{{ Str::limit($svc->description, 80) }}</div>
        @endif
        <div style="display:flex;align-items:flex-end;justify-content:space-between">
          <div>
            <div class="svc-price">₹{{ number_format($svc->price) }}</div>
            <div class="svc-dur"><i class="bi bi-clock"></i> {{ $svc->duration_minutes }} min</div>
          </div>
        </div>
        <div class="svc-actions">
          <button class="btn-ghost-sm" onclick="openEdit({{ $svc->id }},'{{ addslashes($svc->name) }}','{{ $svc->category }}',{{ $svc->duration_minutes }},{{ $svc->price }},'{{ addslashes($svc->description ?? '') }}',{{ $svc->is_active ? 1 : 0 }})">
            <i class="bi bi-pencil"></i> Edit
          </button>
          <form method="POST" action="{{ route('owner.services.destroy', $svc->id) }}" style="display:inline" onsubmit="return confirm('Deactivate karein?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-ghost-sm" style="border-color:var(--rose);color:var(--rose)"><i class="bi bi-trash3"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12" style="text-align:center;padding:3rem;color:var(--text-3)">
    <i class="bi bi-scissors" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
    Koi service nahi mili. Pehli service add karein!
  </div>
  @endforelse
</div>

{{-- Add Modal --}}
<div class="modal-overlay" id="addModal" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
      <div style="font-family:var(--ff-display);font-size:1.3rem;color:var(--text)" id="modalTitle">Add New Service</div>
      <button onclick="closeModal()" style="background:none;border:none;color:var(--text-3);font-size:1.2rem;cursor:pointer"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" id="svcForm" action="{{ route('owner.services.store') }}">
      @csrf
      <span id="methodField"></span>
      <div class="fl-group"><label>Service Name *</label><input type="text" name="name" id="f_name" required /></div>
      <div class="row g-2">
        <div class="col-6"><div class="fl-group"><label>Category *</label><select name="category" id="f_cat" required><option value="">Select</option>@foreach(['hair'=>'Hair','skin'=>'Skin','nail'=>'Nail','bridal'=>'Bridal','massage'=>'Massage','other'=>'Other'] as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div></div>
        <div class="col-6"><div class="fl-group"><label>Duration (min) *</label><input type="number" name="duration_minutes" id="f_dur" min="15" step="15" required /></div></div>
        <div class="col-12"><div class="fl-group"><label>Price (₹) *</label><input type="number" name="price" id="f_price" min="0" step="50" required /></div></div>
        <div class="col-12"><div class="fl-group"><label>Description</label><textarea name="description" id="f_desc" rows="2" style="resize:none"></textarea></div></div>
      </div>
      <div style="display:flex;gap:.8rem;margin-top:.8rem">
        <button type="button" onclick="closeModal()" class="btn-ghost-sm" style="flex:1;justify-content:center">Cancel</button>
        <button type="submit" class="btn-gold-sm" style="flex:2;justify-content:center"><i class="bi bi-check-lg"></i> Save Service</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, name, cat, dur, price, desc, active) {
  document.getElementById('modalTitle').textContent = 'Edit Service';
  document.getElementById('svcForm').action = `/owner/services/${id}`;
  document.getElementById('methodField').innerHTML = `<input type="hidden" name="_method" value="PUT">`;
  document.getElementById('f_name').value  = name;
  document.getElementById('f_cat').value   = cat;
  document.getElementById('f_dur').value   = dur;
  document.getElementById('f_price').value = price;
  document.getElementById('f_desc').value  = desc;
  document.getElementById('addModal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('addModal').style.display = 'none';
  document.getElementById('svcForm').action = "{{ route('owner.services.store') }}";
  document.getElementById('methodField').innerHTML = '';
  document.getElementById('modalTitle').textContent = 'Add New Service';
  document.getElementById('svcForm').reset();
}
</script>
@endpush
