@extends('owner.layouts.app')
@section('title','Staff')
@section('page-title','Staff Management')
@section('topbar-actions')
  <button class="btn-gold-sm" onclick="document.getElementById('addModal').style.display='flex'">
    <i class="bi bi-plus-lg"></i> Add Staff
  </button>
@endsection

@push('styles')
<style>
  .staff-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:all var(--transition);position:relative}
  .staff-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent)}
  .staff-card:hover{border-color:rgba(201,169,110,.2);box-shadow:0 12px 50px rgba(0,0,0,.5);transform:translateY(-3px)}
  .staff-av-lg{width:70px;height:70px;border-radius:50%;background:var(--gold-grad);display:flex;align-items:center;justify-content:center;font-family:var(--ff-display);font-size:1.6rem;font-weight:400;color:#1a1400;margin:0 auto 1rem;box-shadow:0 4px 20px var(--gold-glow-2)}
  .staff-avail{display:inline-flex;align-items:center;gap:.35rem;font-size:.6rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;padding:.2rem .6rem;border-radius:20px}
  .spec-tag{display:inline-flex;align-items:center;font-size:.6rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:.18rem .5rem;border-radius:20px;background:var(--bg-card-2);border:1px solid var(--border-2);color:var(--text-2);margin:0 .2rem .25rem 0}
  .comm-badge{display:inline-flex;align-items:center;gap:.3rem;background:var(--gold-dim);color:var(--gold);border:1px solid rgba(201,169,110,.2);font-size:.62rem;font-weight:700;letter-spacing:.1em;padding:.22rem .6rem;border-radius:20px}
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);z-index:500;align-items:center;justify-content:center;padding:1rem}
  .modal-box{background:var(--bg-card);border:1px solid var(--border-2);border-radius:16px;padding:2rem;width:100%;max-width:500px;max-height:90vh;overflow-y:auto}
  .fl-group{margin-bottom:1.1rem}
  .fl-group label{display:block;font-size:.65rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-3);margin-bottom:.4rem}
  .fl-group input,.fl-group select{width:100%;background:var(--bg-input);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.82rem;padding:.7rem 1rem;outline:none;transition:border-color .25s}
  .fl-group input:focus,.fl-group select:focus{border-color:var(--gold)}
</style>
@endpush

@section('content')
<div class="row g-3 mb-3">
  <div class="col-4 fade-up s1"><div class="card-lux kpi-pad gold-border"><div class="kpi-label">Total Staff</div><div class="kpi-value" style="color:var(--gold)">{{ $stats['total'] }}</div></div></div>
  <div class="col-4 fade-up s2"><div class="card-lux kpi-pad" style="border-top:2px solid var(--emerald)"><div class="kpi-label">Available</div><div class="kpi-value" style="color:var(--emerald)">{{ $stats['available'] }}</div></div></div>
  <div class="col-4 fade-up s3"><div class="card-lux kpi-pad" style="border-top:2px solid var(--purple)"><div class="kpi-label">Avg Commission</div><div class="kpi-value" style="color:var(--purple)">{{ round($stats['avg_commission']) }}%</div></div></div>
</div>

<div class="row g-3 fade-up s2">
  @forelse($staff as $s)
  <div class="col-sm-6 col-lg-4">
    <div class="staff-card p-4" style="text-align:center">
      <div class="staff-av-lg">{{ strtoupper(substr($s->user?->name ?? 'S', 0, 2)) }}</div>
      <div style="font-size:.95rem;font-weight:500;color:var(--text);margin-bottom:.2rem">{{ $s->user?->name }}</div>
      <div style="font-size:.68rem;color:var(--text-3);letter-spacing:.12em;text-transform:uppercase;margin-bottom:.6rem">{{ $s->user?->email }}</div>
      <span class="staff-avail {{ $s->is_available ? 'lb-green' : 'lb-red' }} lux-badge">
        <i class="bi bi-circle-fill" style="font-size:.35rem"></i>
        {{ $s->is_available ? 'Available' : 'Unavailable' }}
      </span>
      @if($s->specializations)
        <div style="margin-top:.8rem">
          @foreach($s->specializations as $sp)
            <span class="spec-tag">{{ $sp }}</span>
          @endforeach
        </div>
      @endif
      <div style="margin-top:.8rem">
        <span class="comm-badge"><i class="bi bi-percent"></i> {{ $s->commission_percent }}% Commission</span>
      </div>
      <div style="display:flex;gap:.4rem;margin-top:1rem;justify-content:center">
        <button class="btn-ghost-sm" onclick="openEdit({{ $s->id }},'{{ addslashes($s->user?->name) }}','{{ $s->user?->phone }}',{{ $s->commission_percent }},'{{ implode(',',$s->specializations??[]) }}',{{ $s->is_available?1:0 }})">
          <i class="bi bi-pencil"></i> Edit
        </button>
        <form method="POST" action="{{ route('owner.staff.destroy', $s->id) }}" style="display:inline" onsubmit="return confirm('Deactivate karein?')">
          @csrf @method('DELETE')
          <button type="submit" class="btn-ghost-sm" style="border-color:var(--rose);color:var(--rose)"><i class="bi bi-trash3"></i></button>
        </form>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12" style="text-align:center;padding:3rem;color:var(--text-3)">
    <i class="bi bi-people" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
    Koi staff nahi. Pehla staff member add karein!
  </div>
  @endforelse
</div>

{{-- Add/Edit Modal --}}
<div class="modal-overlay" id="addModal" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
      <div style="font-family:var(--ff-display);font-size:1.3rem;color:var(--text)" id="modalTitle">Add Staff Member</div>
      <button onclick="closeModal()" style="background:none;border:none;color:var(--text-3);font-size:1.2rem;cursor:pointer"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" id="staffForm" action="{{ route('owner.staff.store') }}">
      @csrf
      <span id="methodField"></span>
      <div id="addOnlyFields">
        <div class="fl-group"><label>Email *</label><input type="email" name="email" id="f_email" required /></div>
        <div class="row g-2">
          <div class="col-6"><div class="fl-group"><label>Password *</label><input type="password" name="password" id="f_pass" /></div></div>
          <div class="col-6"><div class="fl-group"><label>Confirm Password *</label><input type="password" name="password_confirmation" /></div></div>
        </div>
      </div>
      <div class="fl-group"><label>Full Name *</label><input type="text" name="name" id="f_name" required /></div>
      <div class="fl-group"><label>Phone *</label><input type="text" name="phone" id="f_phone" required /></div>
      <div class="fl-group"><label>Commission % *</label><input type="number" name="commission_percent" id="f_comm" min="0" max="100" step="0.5" required /></div>
      <div class="fl-group"><label>Specializations (comma-separated)</label><input type="text" name="specializations" id="f_spec" placeholder="Hair, Bridal, Nail Art" /></div>
      <div style="display:flex;gap:.8rem;margin-top:.8rem">
        <button type="button" onclick="closeModal()" class="btn-ghost-sm" style="flex:1;justify-content:center">Cancel</button>
        <button type="submit" class="btn-gold-sm" style="flex:2;justify-content:center"><i class="bi bi-check-lg"></i> Save</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, name, phone, comm, spec, avail) {
  document.getElementById('modalTitle').textContent = 'Edit Staff';
  document.getElementById('staffForm').action = `/owner/staff/${id}`;
  document.getElementById('methodField').innerHTML = `<input type="hidden" name="_method" value="PUT">`;
  document.getElementById('addOnlyFields').style.display = 'none';
  document.getElementById('f_email').removeAttribute('required');
  document.getElementById('f_pass').removeAttribute('required');
  document.getElementById('f_name').value  = name;
  document.getElementById('f_phone').value = phone;
  document.getElementById('f_comm').value  = comm;
  document.getElementById('f_spec').value  = spec;
  document.getElementById('addModal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('addModal').style.display = 'none';
  document.getElementById('staffForm').action = "{{ route('owner.staff.store') }}";
  document.getElementById('methodField').innerHTML = '';
  document.getElementById('modalTitle').textContent = 'Add Staff Member';
  document.getElementById('addOnlyFields').style.display = 'block';
  document.getElementById('f_email').setAttribute('required','');
  document.getElementById('f_pass').setAttribute('required','');
  document.getElementById('staffForm').reset();
}
</script>
@endpush
