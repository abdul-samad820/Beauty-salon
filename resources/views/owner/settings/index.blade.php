@extends('owner.layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-sub', 'Apne parlour ki settings manage karein')

@push('styles')
<style>
  .settings-card { background:var(--bg-card); border:1px solid var(--border); border-radius:14px; padding:1.8rem; margin-bottom:1.2rem; position:relative; overflow:hidden; }
  .settings-card::before { content:''; position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent); }
  .settings-card-title { font-family:var(--ff-display); font-size:1.1rem; font-weight:400; color:var(--text); margin-bottom:.3rem; }
  .settings-card-sub   { font-size:.72rem; color:var(--text-3); margin-bottom:1.5rem; }
  .fl-group { margin-bottom:1.2rem; }
  .fl-group label { display:block; font-size:.65rem; font-weight:600; letter-spacing:.15em; text-transform:uppercase; color:var(--text-3); margin-bottom:.4rem; }
  .fl-group input, .fl-group select, .fl-group textarea {
    width:100%; background:var(--bg-input); border:1px solid var(--border-2); border-radius:10px;
    color:var(--text); font-family:var(--ff-body); font-size:.85rem; font-weight:300;
    padding:.8rem 1rem; outline:none; transition:border-color .3s, box-shadow .3s;
  }
  .fl-group input:focus, .fl-group select:focus, .fl-group textarea:focus {
    border-color:var(--gold); background:rgba(201,169,110,.04); box-shadow:0 0 0 3px rgba(201,169,110,.08);
  }
  .fl-group select option { background:var(--bg-card); }
  .fl-group textarea { resize:vertical; min-height:80px; }
  .info-row { display:flex; justify-content:space-between; padding:.55rem 0; border-bottom:1px solid rgba(255,255,255,.03); font-size:.82rem; }
  .info-row:last-child { border-bottom:none; }
  .info-key { color:var(--text-3); }
  .info-val { color:var(--text); font-family:monospace; font-size:.78rem; }
  .day-row { display:flex; align-items:center; gap:1rem; padding:.6rem 0; border-bottom:1px solid rgba(255,255,255,.03); }
  .day-row:last-child { border-bottom:none; }
  .day-label { font-size:.8rem; color:var(--text-2); width:90px; flex-shrink:0; }
  .day-toggle { position:relative; width:36px; height:20px; flex-shrink:0; }
  .day-toggle input { opacity:0; width:0; height:0; }
  .day-toggle-slider { position:absolute; inset:0; background:rgba(255,255,255,.08); border-radius:20px; cursor:pointer; transition:.3s; }
  .day-toggle input:checked + .day-toggle-slider { background:var(--gold); }
  .day-toggle-slider::before { content:''; position:absolute; height:14px; width:14px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
  .day-toggle input:checked + .day-toggle-slider::before { transform:translateX(16px); }
  .time-inputs { display:flex; align-items:center; gap:.5rem; flex:1; }
  .time-inputs input[type=time] { width:110px; padding:.4rem .7rem; font-size:.78rem; border:1px solid var(--border-2); border-radius:8px; background:var(--bg-input); color:var(--text); outline:none; }
  .time-inputs input[type=time]:focus { border-color:var(--gold); }
  .time-sep { color:var(--text-3); font-size:.75rem; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('owner.settings.update') }}">
  @csrf @method('PUT')

  <div class="row g-3">
    <div class="col-lg-8">

      {{-- Business Info --}}
      <div class="settings-card fade-up s1">
        <div class="settings-card-title">Business Information</div>
        <div class="settings-card-sub">Apne parlour ki basic details update karein</div>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="fl-group">
              <label>Salon / Parlour Name *</label>
              <input type="text" name="business_name" value="{{ old('business_name', $tenant->name) }}" required />
            </div>
          </div>
          <div class="col-md-6">
            <div class="fl-group">
              <label>Phone Number *</label>
              <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" required />
            </div>
          </div>
          <div class="col-12">
            <div class="fl-group">
              <label>Business Address</label>
              <textarea name="address">{{ old('address', $tenant->address) }}</textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- Working Hours --}}
      <div class="settings-card fade-up s2">
        <div class="settings-card-title">Working Hours</div>
        <div class="settings-card-sub">Kaunse din aur kab se kab tak parlour open rahega</div>
        @php
          $workingHours = $tenant->settings['working_hours'] ?? [];
          $days = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];
        @endphp
        @foreach($days as $key => $label)
          @php
            $hours    = $workingHours[$key] ?? null;
            $isOpen   = !empty($hours);
            $parts    = $isOpen ? explode('-', $hours) : ['09:00','20:00'];
            $startT   = $parts[0] ?? '09:00';
            $endT     = $parts[1] ?? '20:00';
          @endphp
          <div class="day-row" id="row_{{ $key }}">
            <div class="day-label">{{ $label }}</div>
            <label class="day-toggle">
              <input type="checkbox" name="open_{{ $key }}" value="1"
                {{ $isOpen ? 'checked' : '' }}
                onchange="toggleDay('{{ $key }}',this.checked)" />
              <span class="day-toggle-slider"></span>
            </label>
            <div class="time-inputs" id="times_{{ $key }}" style="{{ !$isOpen ? 'opacity:.35;pointer-events:none' : '' }}">
              <input type="time" name="open_{{ $key }}_start" value="{{ $startT }}" />
              <span class="time-sep">to</span>
              <input type="time" name="open_{{ $key }}_end"   value="{{ $endT }}" />
            </div>
            @if(!$isOpen)
              <span style="font-size:.7rem;color:var(--text-3);margin-left:.5rem">Closed</span>
            @endif
          </div>
        @endforeach
      </div>

    </div>

    <div class="col-lg-4">

      {{-- Account Info (read-only) --}}
      <div class="settings-card fade-up s1">
        <div class="settings-card-title">Account Details</div>
        <div class="settings-card-sub">Ye sirf superadmin change kar sakta hai</div>
        <div class="info-row"><span class="info-key">Subdomain</span><span class="info-val">{{ $tenant->subdomain }}.lumiere.app</span></div>
        <div class="info-row"><span class="info-key">Plan</span><span class="info-val" style="color:var(--gold)">{{ ucfirst($tenant->plan) }}</span></div>
        <div class="info-row"><span class="info-key">Status</span>
          <span class="lux-badge {{ $tenant->status === 'active' ? 'lb-green' : 'lb-red' }}">{{ ucfirst($tenant->status) }}</span>
        </div>
        <div class="info-row"><span class="info-key">Joined</span><span class="info-val">{{ $tenant->created_at->format('d M Y') }}</span></div>
        @if($tenant->trial_ends_at)
          <div class="info-row"><span class="info-key">Trial Ends</span><span class="info-val" style="color:var(--amber)">{{ $tenant->trial_ends_at->format('d M Y') }}</span></div>
        @endif
      </div>

      {{-- Owner Profile --}}
      <div class="settings-card fade-up s2">
        <div class="settings-card-title">Your Profile</div>
        <div class="settings-card-sub">Login account info</div>
        <div class="info-row"><span class="info-key">Name</span><span class="info-val">{{ auth()->user()->name }}</span></div>
        <div class="info-row"><span class="info-key">Email</span><span class="info-val">{{ auth()->user()->email }}</span></div>
        <div class="info-row"><span class="info-key">Role</span><span class="lux-badge lb-gold">Owner</span></div>
      </div>

    </div>
  </div>

  <div style="margin-top:.5rem;display:flex;justify-content:flex-end;gap:.8rem;" class="fade-up s3">
    <a href="{{ route('owner.dashboard') }}" class="btn-ghost-sm">
      <i class="bi bi-x-lg"></i> Cancel
    </a>
    <button type="submit" class="btn-gold-sm" style="padding:.6rem 1.5rem;">
      <i class="bi bi-check-lg"></i> Save Settings
    </button>
  </div>
</form>
@endsection

@push('scripts')
<script>
function toggleDay(key, isOpen) {
  const timesEl = document.getElementById('times_' + key);
  timesEl.style.opacity      = isOpen ? '1' : '.35';
  timesEl.style.pointerEvents = isOpen ? 'auto' : 'none';
}
</script>
@endpush
