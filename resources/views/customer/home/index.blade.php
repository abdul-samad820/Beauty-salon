@extends('customer.layouts.app')
@section('title', 'Book Appointment')

@push('styles')
<style>
  .hero-strip{background:linear-gradient(135deg,rgba(201,169,110,.07),transparent 60%);border:1px solid rgba(201,169,110,.12);border-radius:16px;padding:1.6rem 2rem;margin-bottom:1.8rem;position:relative;overflow:hidden;}
  .hero-strip::before{content:'';position:absolute;font-family:var(--ff-display);font-size:7rem;font-weight:300;color:rgba(201,169,110,.04);right:-1rem;top:50%;transform:translateY(-50%);white-space:nowrap;pointer-events:none;}
  .hero-greet{font-family:var(--ff-display);font-size:clamp(1.4rem,4vw,2rem);font-weight:300;color:var(--text);margin-bottom:.4rem;}
  .hero-greet em{font-style:italic;color:var(--gold);}
  .cat-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.4rem;}
  .cat-tab{padding:.38rem .9rem;border-radius:20px;font-size:.7rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;background:rgba(255,255,255,.04);border:1px solid var(--border-2);color:var(--text-3);cursor:pointer;transition:all .25s;}
  .cat-tab.active,.cat-tab:hover{background:var(--gold-dim);border-color:rgba(201,169,110,.25);color:var(--gold);}
  .svc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;margin-bottom:2rem;}
  .svc-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1.4rem;cursor:pointer;transition:all .3s;position:relative;overflow:hidden;}
  .svc-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.07),transparent);}
  .svc-card:hover,.svc-card.selected{border-color:rgba(201,169,110,.35);background:rgba(201,169,110,.04);box-shadow:0 8px 30px rgba(0,0,0,.4);}
  .svc-card.selected{border-color:var(--gold);background:var(--gold-dim);}
  .svc-card.selected::after{content:'\F26E';font-family:'bootstrap-icons';position:absolute;top:.8rem;right:.9rem;color:var(--gold);font-size:.9rem;}
  .svc-cat-pill{font-size:.58rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;padding:.18rem .55rem;border-radius:20px;display:inline-flex;margin-bottom:.7rem;}
  .svc-name{font-size:.92rem;font-weight:500;color:var(--text);margin-bottom:.4rem;}
  .svc-desc{font-size:.72rem;color:var(--text-3);line-height:1.5;margin-bottom:.9rem;}
  .svc-meta{display:flex;align-items:center;justify-content:space-between;}
  .svc-price{font-family:var(--ff-display);font-size:1.3rem;color:var(--gold);}
  .svc-dur{font-size:.68rem;color:var(--text-3);}
  .booking-panel{background:var(--bg-card);border:1px solid var(--border-2);border-radius:16px;padding:1.8rem;position:sticky;top:5rem;}
  .booking-panel-title{font-family:var(--ff-display);font-size:1.3rem;color:var(--text);margin-bottom:1.5rem;}
  .selected-svc-info{background:var(--gold-dim);border:1px solid rgba(201,169,110,.2);border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.2rem;display:none;}
  .selected-svc-info.show{display:block;}
  .slots-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem;margin-top:.6rem;}
  .slot-btn{padding:.45rem .3rem;border-radius:7px;border:1px solid var(--border-2);background:transparent;color:var(--text-2);font-family:var(--ff-body);font-size:.7rem;text-align:center;cursor:pointer;transition:all .2s;}
  .slot-btn:hover:not(:disabled){border-color:var(--gold);color:var(--gold);background:var(--gold-dim);}
  .slot-btn.selected{background:var(--gold);color:#1a1400;border-color:var(--gold);font-weight:600;}
  .slot-btn:disabled{opacity:.3;cursor:not-allowed;}
  .staff-selector{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem;}
  .staff-chip{padding:.35rem .8rem;border-radius:20px;border:1px solid var(--border-2);background:transparent;color:var(--text-2);font-size:.72rem;cursor:pointer;transition:all .2s;}
  .staff-chip:hover{border-color:var(--gold);color:var(--gold);}
  .staff-chip.selected{background:var(--gold-dim);border-color:var(--gold);color:var(--gold);}
  .today-bar{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.15);border-radius:10px;padding:.9rem 1.1rem;margin-bottom:1.4rem;}
  .today-item{display:flex;align-items:center;gap:.8rem;padding:.5rem 0;border-bottom:1px solid rgba(255,255,255,.03);}
  .today-item:last-child{border-bottom:none;}
  .today-time{font-family:var(--ff-display);font-size:.95rem;color:var(--gold);min-width:50px;}
  .spinner{display:none;text-align:center;padding:1rem;color:var(--text-3);font-size:.78rem;}
  .spinner.show{display:block;}
  @media(max-width:768px){.booking-panel{position:static;margin-top:1.5rem;}}
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="hero-strip fade-up s1">
  <div class="hero-greet">
    Namaste, <em>{{ explode(' ', auth()->user()->name)[0] }}</em> ✦
  </div>
  <div style="font-size:.78rem;color:var(--text-3);">
    {{ $tenant->name }} · {{ now()->format('l, d M Y') }} · Appointment book karein
  </div>
</div>

{{-- Today's bookings mini strip --}}
@if($todayBookings->count() > 0)
<div class="today-bar fade-up s2">
  <div style="font-size:.62rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--emerald);margin-bottom:.5rem;">
    <i class="bi bi-calendar-check-fill"></i> Aaj ki Bookings
  </div>
  @foreach($todayBookings as $tb)
  <div class="today-item">
    <div class="today-time">{{ \Carbon\Carbon::parse($tb->start_time)->format('h:i') }}</div>
    <div>
      <div style="font-size:.82rem;color:var(--text);">{{ $tb->service?->name }}</div>
      <div style="font-size:.68rem;color:var(--text-3);">{{ $tb->staff?->user?->name }}</div>
    </div>
    <span class="c-badge {{ match($tb->status){ 'confirmed'=>'cb-gold','completed'=>'cb-green','cancelled'=>'cb-red',default=>'cb-amber' } }}" style="margin-left:auto">{{ ucfirst($tb->status) }}</span>
  </div>
  @endforeach
</div>
@endif

<div class="row g-4">
  {{-- Services column --}}
  <div class="col-lg-7 fade-up s2">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;">
      <div>
        <div style="font-family:var(--ff-display);font-size:1.2rem;color:var(--text);">Our Services</div>
        <div style="font-size:.68rem;color:var(--text-3);">Service choose karein aur slot book karein</div>
      </div>
    </div>

    {{-- Category tabs --}}
    <div class="cat-tabs">
      <button class="cat-tab active" onclick="filterCat(this,'all')">All</button>
      @foreach($services->keys() as $cat)
        <button class="cat-tab" onclick="filterCat(this,'{{ $cat }}')">{{ ucfirst($cat) }}</button>
      @endforeach
    </div>

    {{-- Service cards --}}
    <div class="svc-grid" id="svcGrid">
      @foreach($services as $cat => $catServices)
        @foreach($catServices as $svc)
        @php
          $catColor = match($svc->category) { 'hair'=>'var(--gold)','skin'=>'#a78bfa','nail'=>'var(--teal-light)','bridal'=>'var(--rose)','massage'=>'var(--emerald)',default=>'var(--text-2)' };
          $catBg    = match($svc->category) { 'hair'=>'var(--gold-dim)','skin'=>'rgba(167,139,250,.12)','nail'=>'var(--teal-dim)','bridal'=>'var(--rose-dim)','massage'=>'var(--emerald-dim)',default=>'rgba(255,255,255,.05)' };
        @endphp
        <div class="svc-card"
             data-cat="{{ $svc->category }}"
             data-id="{{ $svc->id }}"
             data-name="{{ $svc->name }}"
             data-price="{{ $svc->price }}"
             data-dur="{{ $svc->duration_minutes }}"
             onclick="selectService(this)">
          <span class="svc-cat-pill" style="background:{{ $catBg }};color:{{ $catColor }}">{{ ucfirst($svc->category) }}</span>
          <div class="svc-name">{{ $svc->name }}</div>
          @if($svc->description)
            <div class="svc-desc">{{ Str::limit($svc->description, 70) }}</div>
          @endif
          <div class="svc-meta">
            <div class="svc-price">₹{{ number_format($svc->price) }}</div>
            <div class="svc-dur"><i class="bi bi-clock"></i> {{ $svc->duration_minutes }} min</div>
          </div>
        </div>
        @endforeach
      @endforeach
    </div>
  </div>

  {{-- Booking panel --}}
  <div class="col-lg-5 fade-up s3">
    <div class="booking-panel">
      <div class="booking-panel-title">Book Appointment</div>

      {{-- Selected service info --}}
      <div class="selected-svc-info" id="svcInfo">
        <div style="font-size:.62rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);margin-bottom:.3rem;">Selected Service</div>
        <div id="infoName" style="font-size:.9rem;font-weight:500;color:var(--text);">—</div>
        <div style="display:flex;gap:1rem;margin-top:.3rem;">
          <span id="infoPrice" style="font-family:var(--ff-display);color:var(--gold);font-size:1.1rem;"></span>
          <span id="infoDur"   style="font-size:.7rem;color:var(--text-3);align-self:flex-end;"></span>
        </div>
      </div>

      <form method="POST" action="{{ route('customer.book', request()->route('subdomain')) }}" id="bookForm">
        @csrf
        <input type="hidden" name="service_id"       id="f_svc_id" />
        <input type="hidden" name="staff_id"         id="f_staff_id" />
        <input type="hidden" name="appointment_date" id="f_date_val" />
        <input type="hidden" name="start_time"       id="f_time_val" />

        {{-- Date picker --}}
        <div class="cfl">
          <label>Date *</label>
          <input type="date" id="datePicker" min="{{ date('Y-m-d') }}" onchange="onDateChange(this.value)" />
        </div>

        {{-- Staff chips --}}
        <div class="cfl">
          <label>Staff (optional)</label>
          <div class="staff-selector" id="staffChips">
            <button type="button" class="staff-chip selected" data-id="" onclick="selectStaff(this,'')">Anyone</button>
            @foreach($staff as $s)
              <button type="button" class="staff-chip" data-id="{{ $s->id }}" onclick="selectStaff(this,'{{ $s->id }}')">
                {{ $s->user?->name }}
              </button>
            @endforeach
          </div>
        </div>

        {{-- Slots --}}
        <div class="cfl" id="slotsSection" style="display:none;">
          <label>Available Slots *</label>
          <div class="spinner show" id="slotSpinner"><i class="bi bi-arrow-repeat"></i> Loading slots…</div>
          <div id="slotsContainer"></div>
        </div>

        {{-- Notes --}}
        <div class="cfl" id="notesSection" style="display:none;">
          <label>Notes (optional)</label>
          <input type="text" name="notes" placeholder="Koi special request?" />
        </div>

        <button type="submit" class="btn-cust-gold w-100 justify-content-center" id="bookBtn" disabled style="opacity:.5">
          <i class="bi bi-calendar-check-fill"></i> Confirm Booking
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let selectedSvcId  = null;
let selectedStaffId = '';
let selectedDate   = null;
let selectedSlot   = null;

function filterCat(btn, cat) {
  document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.svc-card').forEach(c => {
    c.style.display = (cat === 'all' || c.dataset.cat === cat) ? 'block' : 'none';
  });
}

function selectService(el) {
  document.querySelectorAll('.svc-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  selectedSvcId = el.dataset.id;
  document.getElementById('f_svc_id').value = selectedSvcId;

  // Show info
  document.getElementById('svcInfo').classList.add('show');
  document.getElementById('infoName').textContent  = el.dataset.name;
  document.getElementById('infoPrice').textContent = '₹' + Number(el.dataset.price).toLocaleString('en-IN');
  document.getElementById('infoDur').textContent   = el.dataset.dur + ' min';

  if (selectedDate) fetchSlots();
}

function selectStaff(btn, id) {
  document.querySelectorAll('.staff-chip').forEach(c => c.classList.remove('selected'));
  btn.classList.add('selected');
  selectedStaffId = id;
  document.getElementById('f_staff_id').value = id;
  if (selectedDate && selectedSvcId) fetchSlots();
}

function onDateChange(val) {
  selectedDate = val;
  document.getElementById('f_date_val').value = val;
  if (selectedSvcId) fetchSlots();
}

function fetchSlots() {
  document.getElementById('slotsSection').style.display = 'block';
  document.getElementById('slotSpinner').classList.add('show');
  document.getElementById('slotsContainer').innerHTML = '';
  document.getElementById('bookBtn').disabled = true;
  document.getElementById('bookBtn').style.opacity = '.5';
  selectedSlot = null;
  document.getElementById('f_time_val').value = '';

  const params = new URLSearchParams({ date: selectedDate, service_id: selectedSvcId });
  if (selectedStaffId) params.append('staff_id', selectedStaffId);

  fetch(`{{ route('customer.slots', request()->route('subdomain')) }}?${params}` {
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('slotSpinner').classList.remove('show');
    let html = '';
    data.data?.forEach(staff => {
      if (data.data.length > 1) {
        html += `<div style="font-size:.65rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-3);margin:.8rem 0 .4rem;">${staff.staff_name}</div>`;
      }
      html += '<div class="slots-grid">';
      staff.slots.forEach(slot => {
        if (slot.available) {
          html += `<button type="button" class="slot-btn" data-staff="${staff.staff_id}" data-time="${slot.start}" onclick="selectSlot(this,'${staff.staff_id}','${slot.start}')">${slot.display}</button>`;
        } else {
          html += `<button type="button" class="slot-btn" disabled>${slot.display}</button>`;
        }
      });
      html += '</div>';
    });
    if (!html) html = '<div style="text-align:center;color:var(--text-3);font-size:.78rem;padding:1rem;">Koi slot available nahi is date pe</div>';
    document.getElementById('slotsContainer').innerHTML = html;
    document.getElementById('notesSection').style.display = 'block';
  })
  .catch(() => {
    document.getElementById('slotSpinner').classList.remove('show');
    document.getElementById('slotsContainer').innerHTML = '<div style="color:var(--rose);font-size:.78rem;">Slots load nahi hue. Refresh karo.</div>';
  });
}

function selectSlot(btn, staffId, time) {
  document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  selectedSlot = time;
  document.getElementById('f_time_val').value  = time;
  document.getElementById('f_staff_id').value  = staffId;
  document.getElementById('f_date_val').value  = selectedDate;
  document.getElementById('bookBtn').disabled  = false;
  document.getElementById('bookBtn').style.opacity = '1';
}

document.getElementById('bookForm')?.addEventListener('submit', function(e) {
  if (!selectedSvcId || !selectedDate || !selectedSlot) {
    e.preventDefault();
    alert('Service, date aur slot — teeno choose karo!');
  }
});
</script>
@endpush
