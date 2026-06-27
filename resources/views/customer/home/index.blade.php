@extends('layouts.customer')

@section('title', 'Book Premium Appointment')

@section('content')

<section class="card-lux p-4 mb-4 fade-up s1" aria-labelledby="customer-greeting">
    @php $customerName = auth('customer')->user()?->name ?? 'Guest Client'; @endphp
    <h2 class="serif gold-text" style="font-size: 1.4rem; font-weight: 400;" id="customer-greeting">
        Welcome, <span style="color: var(--text);">{{ explode(' ', $customerName)[0] }}</span> ✦
    </h2>
    <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem;">
        {{ $tenant->name }} Portal · Explore our curation and secure your appointment slot instantly.
    </p>
</section>

@if($todayBookings->count() > 0)
<section class="card-lux mb-4 fade-up s2" style="border-left: 3px solid var(--emerald); background: var(--emerald-dim);">
    <p style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--emerald); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <span class="live-dot" style="background: var(--emerald);"></span>
        <i class="bi bi-calendar-check-fill"></i> Today's Active Bookings
    </p>
    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
        @foreach($todayBookings as $tb)
        <div style="display: flex; align-items: center; gap: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <time style="font-family: monospace; font-size: 0.75rem; font-weight: 600; color: var(--gold); min-width: 60px;">
                {{ \Carbon\Carbon::parse($tb->start_time)->format('h:i A') }}
            </time>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--text);">{{ $tb->service?->name }}</div>
                <div style="font-size: 0.7rem; color: var(--text-3);">Professional: {{ $tb->staff?->user?->name }}</div>
            </div>
            <span class="status-badge {{ $tb->status === 'completed' ? 'badge-active' : 'badge-trial' }}" style="font-size: 0.6rem;">{{ ucfirst($tb->status) }}</span>
        </div>
        @endforeach
    </div>
</section>
@endif

<div class="row g-4">
    <div class="col-12 col-lg-7 fade-up s2">
        <h3 class="serif" style="font-size: 1.1rem; color: var(--text); margin-bottom: 1rem;">Our Treatment Menu</h3>

        <div style="display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 1rem; margin-bottom: 1rem;" role="tablist">
            <button class="cat-tab active" role="tab" onclick="Booking.filterCategory(this,'all')">All Treatments</button>
            @foreach($services->keys() as $cat)
            <button class="cat-tab" role="tab" onclick="Booking.filterCategory(this,'{{ $cat }}')">{{ ucfirst($cat) }}</button>
            @endforeach
        </div>

        <div class="row g-3" id="svcGrid">
            @foreach($services as $cat => $catServices)
            @foreach($catServices as $svc)
            <div class="col-12 col-sm-6 service-card-wrapper" data-cat="{{ $svc->category }}">
                <article class="card-lux group" style="height: 100%; cursor: pointer; transition: all 0.3s; padding: 1.25rem; display: flex; flex-direction: column;" data-id="{{ $svc->id }}" data-name="{{ $svc->name }}" data-price="{{ $svc->price }}" data-dur="{{ $svc->duration_minutes }}" onclick="Booking.selectService(this, document.getElementById('bookingPanel').dataset.subdomain)">

                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span class="plan-badge" style="background: var(--bg-input); color: var(--text-3); font-size: 0.6rem;">{{ $svc->category }}</span>
                    </div>
                    <h4 style="font-size: 0.85rem; font-weight: 600; color: var(--text); margin-bottom: 0.5rem;">{{ $svc->name }}</h4>
                    <p style="font-size: 0.75rem; color: var(--text-3); line-height: 1.5; margin-bottom: 1rem;">{{ $svc->description }}</p>

                    <div style="margin-top: auto; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 1rem;">
                        <span style="font-size: 1.1rem; font-weight: 600; color: var(--gold);">₹{{ number_format($svc->price, 0) }}</span>
                        <span style="font-size: 0.75rem; color: var(--text-3);"><i class="bi bi-clock faint"></i> {{ $svc->duration_minutes }} min</span>
                    </div>
                </article>
            </div>
            @endforeach
            @endforeach
        </div>
    </div>

    <div class="col-12 col-lg-5 fade-up s3">
        <section class="card-lux p-4" id="bookingPanel" data-subdomain="{{ $subdomain }}" style="position: sticky; top: 1.5rem;">

            <div style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1rem;">
                <h3 class="serif" style="font-size: 1.1rem; color: var(--text); margin-bottom: 0;">Configure Appointment</h3>
            </div>

            <div id="svcInfo" class="hidden" style="margin-bottom: 1.5rem; background: var(--gold-dim); border: 1px solid rgba(201,169,110,0.2); border-radius: var(--r-md); padding: 1rem;">
                <div style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; color: var(--gold); margin-bottom: 0.2rem;">Selected Treatment</div>
                <div id="infoName" style="font-size: 0.85rem; font-weight: 600; color: var(--text);"></div>
                <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                    <span id="infoPrice" style="font-size: 0.9rem; font-weight: 600; color: var(--text);"></span>
                    <span id="infoDur" style="font-size: 0.75rem; color: var(--text-3);"></span>
                </div>
            </div>
            {{-- Booking Progress Bar --}}
            <div style="margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;">
                    <span id="progress-label" style="font-size:.7rem;color:var(--text-3);">Step 1 of 4 — Select a Service</span>
                    <span id="progress-percent" style="font-size:.7rem;color:var(--gold);">25%</span>
                </div>
                <div style="background:var(--bg-input);border-radius:20px;height:4px;overflow:hidden;">
                    <div id="booking-progress-bar" style="width:25%;height:100%;background:var(--gold);border-radius:20px;transition:width .4s ease;"></div>
                </div>
            </div>

            <form method="POST" action="{{ route('customer.book', $subdomain) }}" id="bookForm" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                <input type="hidden" name="service_id" id="f_svc_id" />
                <input type="hidden" name="staff_id" id="f_staff_id" />
                <div>
                    <label class="lux-label" for="datePicker">Preferred Date *</label>
                    <input type="date" id="datePicker" name="appointment_date" min="{{ date('Y-m-d') }}" class="lux-input" onchange="Booking.onDateChange(this.value, document.getElementById('bookingPanel').dataset.subdomain)" required />
                </div>
                <input type="hidden" name="start_time" id="f_time_val" />

                <div>
                    <label class="lux-label">Select Professional</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <button type="button" class="staff-chip selected" data-id="" onclick="Booking.selectStaff(this,'',document.getElementById('bookingPanel').dataset.subdomain)">Anyone (Auto)</button>
                        @foreach($staff as $s)
                        <button type="button" class="staff-chip" data-id="{{ $s->id }}" onclick="Booking.selectStaff(this,'{{ $s->id }}',document.getElementById('bookingPanel').dataset.subdomain)">{{ $s->user?->name }}</button>
                        @endforeach
                    </div>
                </div>

                <div id="slotsSection" class="hidden">
                    <label class="lux-label">Available Time Slots *</label>
                    <div id="slotSpinner" style="display: none; padding: 1rem; text-align: center; color: var(--text-3); font-size: 0.75rem;">
                        <i class="bi bi-arrow-repeat spin"></i> Searching for availability...
                    </div>
                    <div id="slotsContainer" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-top: 0.5rem;"></div>
                </div>

                <div>
                    <label class="lux-label">Payment Method *</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="staff-chip selected" id="pay_cash" onclick="selectPayment('cash')">
                            <i class="bi bi-cash"></i> Cash
                        </button>
                        <button type="button" class="staff-chip" id="pay_upi" onclick="selectPayment('upi')">
                            <i class="bi bi-qr-code"></i> UPI
                        </button>
                        <button type="button" class="staff-chip" id="pay_razorpay" onclick="selectPayment('razorpay')">
                            <i class="bi bi-credit-card"></i> Online Pay
                        </button>
                    </div>
                    <input type="hidden" name="payment_method" id="f_payment" value="cash" />
                </div>

                <div id="notesSection" style="display: none;">
                    <label class="lux-label" for="notes">Special Requests/Comments</label>
                    <textarea name="notes" id="notes" class="lux-input" rows="2" placeholder="e.g. Sensitive skin treatments..."></textarea>
                </div>

                <button type="submit" id="bookBtn" class="btn-lux-gold" style="width: 100%; margin-top: 0.5rem; opacity: 0.5; cursor: not-allowed;" disabled>
                    <i class="bi bi-calendar-check-fill"></i> Confirm Appointment
                </button>
            </form>
        </section>
    </div>
</div>

<style>
    .cat-tab {
        border: 1px solid var(--border);
        background: transparent;
        color: var(--text-2);
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        transition: 0.3s;
    }

    .cat-tab.active {
        background: var(--gold);
        color: var(--bg-card);
        border-color: var(--gold);
    }

    .staff-chip {
        border: 1px solid var(--border);
        background: var(--bg-input);
        color: var(--text-2);
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.7rem;
        transition: 0.3s;
    }

    .staff-chip.selected {
        border-color: var(--gold);
        color: var(--gold);
    }

    .svc-card.selected {
        border-color: var(--gold);
        outline: 1px solid var(--gold);
    }

</style>
@endsection
@push('scripts')
<script>
    function selectPayment(method) {
        document.getElementById('f_payment').value = method;
        document.getElementById('pay_cash').classList.toggle('selected', method === 'cash');
        document.getElementById('pay_upi').classList.toggle('selected', method === 'upi');
        document.getElementById('pay_razorpay').classList.toggle('selected', method === 'razorpay');
    }
    // Progress bar logic
    function updateProgress() {
        const steps = [{
                check: () => document.getElementById('f_svc_id').value !== ''
                , label: 'Step 2 of 4 — Select Date & Time'
                , pct: 50
            }
            , {
                check: () => document.getElementById('f_time_val').value !== ''
                , label: 'Step 3 of 4 — Choose Payment'
                , pct: 75
            }
            , {
                check: () => document.getElementById('f_payment').value !== ''
                , label: 'Step 4 of 4 — Ready to Confirm'
                , pct: 100
            }
        , ];

        let pct = 25;
        let label = 'Step 1 of 4 — Select a Service';

        for (const step of steps) {
            if (step.check()) {
                pct = step.pct;
                label = step.label;
            } else break;
        }

        document.getElementById('booking-progress-bar').style.width = pct + '%';
        document.getElementById('progress-label').textContent = label;
        document.getElementById('progress-percent').textContent = pct + '%';
    }

    // Observe changes
    ['f_svc_id', 'f_time_val', 'f_payment'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new MutationObserver(updateProgress).observe(el, {
            attributes: true
            , childList: true
            , subtree: true
        });
    });

    document.getElementById('f_payment') ? .addEventListener('change', updateProgress);
    setInterval(updateProgress, 500);

</script>
@endpush
