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
<section class="mb-4 fade-up s2" style="border-radius:14px;overflow:hidden;border:1px solid rgba(52,211,153,0.2);background:linear-gradient(135deg,rgba(52,211,153,0.06) 0%,rgba(52,211,153,0.02) 100%);">
    <div style="padding:0.75rem 1.25rem;border-bottom:1px solid rgba(52,211,153,0.12);display:flex;align-items:center;justify-content:space-between;background:rgba(52,211,153,0.07);">
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <span class="live-dot" style="background:var(--emerald);width:7px;height:7px;border-radius:50%;display:inline-block;box-shadow:0 0 6px var(--emerald);"></span>
            <i class="bi bi-calendar-check-fill" style="color:var(--emerald);font-size:0.75rem;"></i>
            <span style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:var(--emerald);">Today's Active Bookings</span>
        </div>
        <span style="font-size:0.6rem;color:var(--emerald);background:rgba(52,211,153,0.12);padding:0.2rem 0.55rem;border-radius:20px;font-weight:600;">
            {{ $todayBookings->count() }} {{ $todayBookings->count() === 1 ? 'appointment' : 'appointments' }}
        </span>
    </div>
    <div style="padding:0.5rem 0;">
        @foreach($todayBookings as $tb)
        @php
        $statusColors = [
        'pending' => ['bg' => 'rgba(251,191,36,0.12)', 'text' => '#fbbf24', 'label' => 'Pending'],
        'confirmed' => ['bg' => 'rgba(52,211,153,0.12)', 'text' => '#34d399', 'label' => 'Confirmed'],
        'checked_in' => ['bg' => 'rgba(96,165,250,0.12)', 'text' => '#60a5fa', 'label' => 'Checked In'],
        'completed' => ['bg' => 'rgba(167,243,208,0.12)', 'text' => '#6ee7b7', 'label' => 'Completed'],
        'cancelled' => ['bg' => 'rgba(248,113,113,0.12)', 'text' => '#f87171', 'label' => 'Cancelled'],
        'no_show' => ['bg' => 'rgba(156,163,175,0.12)', 'text' => '#9ca3af', 'label' => 'No Show'],
        ];
        $sc = $statusColors[$tb->status] ?? ['bg' => 'rgba(255,255,255,0.07)', 'text' => 'var(--text-3)', 'label' => ucfirst($tb->status)];
        @endphp
        <div style="display:flex;align-items:center;gap:1rem;padding:0.75rem 1.25rem;border-bottom:1px solid rgba(255,255,255,0.04);transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-width:52px;background:rgba(201,169,110,0.1);border:1px solid rgba(201,169,110,0.2);border-radius:8px;padding:0.35rem 0.4rem;">
                <span style="font-family:monospace;font-size:0.8rem;font-weight:700;color:var(--gold);line-height:1;">{{ \Carbon\Carbon::parse($tb->start_time)->format('h:i') }}</span>
                <span style="font-size:0.55rem;color:var(--gold);opacity:0.7;letter-spacing:0.05em;">{{ \Carbon\Carbon::parse($tb->start_time)->format('A') }}</span>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:0.82rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $tb->service?->name ?? '—' }}</div>
                <div style="font-size:0.68rem;color:var(--text-3);margin-top:0.15rem;display:flex;align-items:center;gap:0.3rem;">
                    <i class="bi bi-person-fill" style="font-size:0.6rem;"></i>
                    {{ $tb->staff?->user?->name ?? 'Any Professional' }}
                </div>
            </div>
            <span style="font-size:0.6rem;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;padding:0.25rem 0.6rem;border-radius:20px;white-space:nowrap;background:{{ $sc['bg'] }};color:{{ $sc['text'] }};">{{ $sc['label'] }}</span>
        </div>
        @endforeach
    </div>
</section>
@endif

<div class="row g-4">
    <div class="col-12 col-lg-7 fade-up s2">
        <h3 class="serif" style="font-size:1.1rem;color:var(--text);margin-bottom:1rem;">Our Treatment Menu</h3>
        <div style="display:flex;gap:0.5rem;overflow-x:auto;padding-bottom:1rem;margin-bottom:1rem;" role="tablist">
            <button class="cat-tab active" role="tab" onclick="Booking.filterCategory(this,'all')">All Treatments</button>
            @foreach($services->keys() as $cat)
            <button class="cat-tab" role="tab" onclick="Booking.filterCategory(this,'{{ $cat }}')">{{ ucfirst($cat) }}</button>
            @endforeach
        </div>
        <div class="row g-3" id="svcGrid">
            @foreach($services as $cat => $catServices)
            @foreach($catServices as $svc)
            <div class="col-12 col-sm-6 service-card-wrapper" data-cat="{{ $svc->category }}">
                <article class="card-lux group" style="height:100%;cursor:pointer;transition:all 0.3s;padding:1.25rem;display:flex;flex-direction:column;" data-id="{{ $svc->id }}" data-name="{{ $svc->name }}" data-price="{{ $svc->price }}" data-dur="{{ $svc->duration_minutes }}" onclick="Booking.selectService(this, document.getElementById('bookingPanel').dataset.subdomain)">
                    <div style="display:flex;justify-content:space-between;margin-bottom:1rem;">
                        <span class="plan-badge" style="background:var(--bg-input);color:var(--text-3);font-size:0.6rem;">{{ $svc->category }}</span>
                    </div>
                    <h4 style="font-size:0.85rem;font-weight:600;color:var(--text);margin-bottom:0.5rem;">{{ $svc->name }}</h4>
                    <p style="font-size:0.75rem;color:var(--text-3);line-height:1.5;margin-bottom:1rem;">{{ $svc->description }}</p>
                    <div style="margin-top:auto;display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--border);padding-top:1rem;">
                        <span style="font-size:1.1rem;font-weight:600;color:var(--gold);">₹{{ number_format($svc->price, 0) }}</span>
                        <span style="font-size:0.75rem;color:var(--text-3);"><i class="bi bi-clock faint"></i> {{ $svc->duration_minutes }} min</span>
                    </div>
                </article>
            </div>
            @endforeach
            @endforeach
        </div>
    </div>

    <div class="col-12 col-lg-5 fade-up s3">
        <section class="card-lux" id="bookingPanel" data-subdomain="{{ $subdomain }}" style="position:sticky;top:1.5rem;padding:0;overflow:hidden;">

            {{-- Panel Header --}}
            <div style="padding:1.5rem 1.5rem 1.25rem;border-bottom:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.3rem;">
                    <span style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:var(--gold-dim);color:var(--gold);font-size:0.85rem;">
                        <i class="bi bi-calendar2-week"></i>
                    </span>
                    <h3 class="serif" style="font-size:1.15rem;color:var(--text);margin:0;">Configure Appointment</h3>
                </div>
                <p style="font-size:0.7rem;color:var(--text-3);margin:0 0 0 2.3rem;">Complete the steps below to reserve your slot</p>
            </div>

            {{-- Progress --}}
            <div style="padding:1.1rem 1.5rem;border-bottom:1px solid var(--border);background:var(--bg-input);">
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:0.6rem;">
                    <span id="progress-label" style="font-size:0.7rem;font-weight:600;color:var(--text-2);letter-spacing:0.02em;">Step 1 of 4 — Select a Service</span>
                    <span id="progress-percent" style="font-size:0.7rem;font-weight:700;color:var(--gold);">25%</span>
                </div>
                <div style="background:rgba(255,255,255,0.06);border-radius:20px;height:5px;overflow:hidden;">
                    <div id="booking-progress-bar" style="width:25%;height:100%;background:linear-gradient(90deg, var(--gold) 0%, #e8c97a 100%);border-radius:20px;transition:width .4s cubic-bezier(0.16,1,0.3,1);"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:0.55rem;">
                    @foreach(['Service','Date & Time','Payment','Confirm'] as $i => $stepName)
                    <span style="font-size:0.58rem;color:var(--text-3);letter-spacing:0.03em;{{ $i === 0 ? '' : '' }}">{{ $stepName }}</span>
                    @endforeach
                </div>
            </div>

            <div style="padding:1.5rem;">

                {{-- Selected Treatment Card --}}
                <div id="svcInfo" class="hidden" style="margin-bottom:1.4rem;background:linear-gradient(135deg, var(--gold-dim) 0%, rgba(201,169,110,0.03) 100%);border:1px solid rgba(201,169,110,0.25);border-radius:var(--r-md);padding:1rem 1.1rem;display:flex;align-items:center;gap:0.9rem;">
                    <span style="flex-shrink:0;display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;background:rgba(201,169,110,0.15);color:var(--gold);font-size:1rem;">
                        <i class="bi bi-stars"></i>
                    </span>
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--gold);margin-bottom:0.15rem;">Selected Treatment</div>
                        <div id="infoName" style="font-size:0.88rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                        <div style="display:flex;gap:0.9rem;margin-top:0.3rem;">
                            <span id="infoPrice" style="font-size:0.95rem;font-weight:700;color:var(--text);"></span>
                            <span id="infoDur" style="font-size:0.75rem;color:var(--text-3);display:flex;align-items:center;gap:0.25rem;"></span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('customer.book', $subdomain) }}" id="bookForm" style="display:flex;flex-direction:column;gap:1.4rem;">
                    @csrf
                    <input type="hidden" name="service_id" id="f_svc_id" />
                    <input type="hidden" name="staff_id" id="f_staff_id" />
                    <input type="hidden" name="start_time" id="f_time_val" />

                    {{-- Date --}}
                    <div>
                        <label class="lux-label" for="datePicker" style="display:flex;align-items:center;gap:0.4rem;">
                            <i class="bi bi-calendar3" style="color:var(--gold);font-size:0.8rem;"></i> Preferred Date *
                        </label>
                        <input type="date" id="datePicker" name="appointment_date" min="{{ $tenantTodayDate }}" class="lux-input" onchange="Booking.onDateChange(this.value, document.getElementById('bookingPanel').dataset.subdomain)" required />
                    </div>

                    {{-- Professional --}}
                    <div>
                        <label class="lux-label" style="display:flex;align-items:center;gap:0.4rem;">
                            <i class="bi bi-person-badge" style="color:var(--gold);font-size:0.8rem;"></i> Select Professional
                        </label>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.5rem;">
                            <button type="button" class="staff-chip selected" data-id="" onclick="Booking.selectStaff(this,'',document.getElementById('bookingPanel').dataset.subdomain)">
                                <i class="bi bi-shuffle" style="font-size:0.7rem;margin-right:0.3rem;"></i>Anyone (Auto)
                            </button>
                            @foreach($staff as $s)
                            <button type="button" class="staff-chip" data-id="{{ $s->id }}" onclick="Booking.selectStaff(this,'{{ $s->id }}',document.getElementById('bookingPanel').dataset.subdomain)">{{ $s->user?->name }}</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Time Slots --}}
                    <div id="slotsSection" class="hidden">
                        <label class="lux-label" style="display:flex;align-items:center;gap:0.4rem;">
                            <i class="bi bi-clock-history" style="color:var(--gold);font-size:0.8rem;"></i> Available Time Slots *
                        </label>
                        <div id="slotSpinner" style="display:none;padding:1.25rem;text-align:center;color:var(--text-3);font-size:0.75rem;background:var(--bg-input);border-radius:var(--r-sm);margin-top:0.5rem;">
                            <i class="bi bi-arrow-repeat spin"></i> Searching for availability...
                        </div>
                        <div id="slotsContainer" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(85px,1fr));gap:0.5rem;margin-top:0.6rem;"></div>
                    </div>

                    {{-- Payment --}}
                    <div>
                        <label class="lux-label" style="display:flex;align-items:center;gap:0.4rem;">
                            <i class="bi bi-wallet2" style="color:var(--gold);font-size:0.8rem;"></i> Payment Method *
                        </label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;margin-top:0.5rem;">
                            <button type="button" class="pay-chip pay-chip-block selected" id="pay_cash" data-payment="cash" onclick="selectPayment('cash')">
                                <i class="bi bi-cash-coin"></i>
                                <span>Cash</span>
                            </button>
                            <button type="button" class="pay-chip pay-chip-block" id="pay_razorpay" data-payment="razorpay" onclick="selectPayment('razorpay')">
                                <i class="bi bi-credit-card-2-front"></i>
                                <span>Online Pay</span>
                            </button>
                        </div>
                        <input type="hidden" name="payment_method" id="f_payment" value="cash" />
                    </div>

                    {{-- Notes --}}
                    <div id="notesSection" style="display:none;">
                        <label class="lux-label" for="notes" style="display:flex;align-items:center;gap:0.4rem;">
                            <i class="bi bi-chat-left-text" style="color:var(--gold);font-size:0.8rem;"></i> Special Requests / Comments
                        </label>
                        <textarea name="notes" id="notes" class="lux-input" rows="2" placeholder="e.g. Sensitive skin treatments..." style="resize:vertical;"></textarea>
                    </div>

                    <button type="submit" id="bookBtn" class="btn-lux-gold" style="width:100%;display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.85rem;font-size:0.85rem;opacity:0.5;cursor:not-allowed;" disabled>
                        <i class="bi bi-calendar-check-fill"></i> Confirm Appointment
                    </button>
                </form>
            </div>
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
        cursor: pointer;
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
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 500;
        transition: all 0.2s;
        cursor: pointer;
        user-select: none;
        display: inline-flex;
        align-items: center;
    }

    .staff-chip:hover {
        border-color: var(--gold);
        color: var(--gold);
        background: var(--gold-dim);
    }

    .staff-chip.selected {
        border-color: var(--gold);
        color: var(--gold);
        background: var(--gold-dim);
        font-weight: 600;
    }

    .pay-chip {
        border: 1px solid var(--border);
        background: var(--bg-input);
        color: var(--text-2);
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-size: 0.72rem;
        transition: all 0.2s;
        cursor: pointer;
        user-select: none;
    }

    .pay-chip:hover {
        border-color: var(--gold);
        color: var(--gold);
        background: var(--gold-dim);
    }

    .pay-chip.selected {
        border-color: var(--gold);
        color: var(--gold);
        background: var(--gold-dim);
    }

    /* Redesigned block-style payment chips */
    .pay-chip-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.85rem 0.5rem;
        border-radius: var(--r-sm);
        font-weight: 600;
    }

    .pay-chip-block i {
        font-size: 1.05rem;
    }

    .pay-chip-block.selected {
        box-shadow: 0 0 0 1px var(--gold) inset;
    }

    /* Slot buttons — refined */
    .slot-btn {
        border: 1px solid var(--border);
        background: var(--bg-input);
        color: var(--text-2);
        padding: 0.55rem 0.4rem;
        border-radius: var(--r-sm);
        font-size: 0.72rem;
        font-weight: 500;
        transition: 0.2s;
        cursor: pointer;
        text-align: center;
    }

    .slot-btn:hover:not(:disabled) {
        border-color: var(--gold);
        color: var(--gold);
        background: var(--gold-dim);
    }

    .slot-btn.selected {
        border-color: var(--gold);
        background: var(--gold-dim);
        color: var(--gold);
        font-weight: 700;
        box-shadow: 0 0 0 1px var(--gold) inset;
    }

    .slot-btn.disabled,
    .slot-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
        text-decoration: line-through;
    }

    #bookBtn:not(:disabled) {
        box-shadow: 0 6px 20px rgba(201, 169, 110, 0.25);
    }

    #bookBtn:not(:disabled):hover {
        transform: translateY(-1px);
    }

</style>

@endsection

@push('scripts')
<script>
    function selectPayment(method) {
        document.getElementById('f_payment').value = method;
        document.querySelectorAll('.pay-chip').forEach(el => el.classList.remove('selected'));
        const map = {
            cash: 'pay_cash'
            , razorpay: 'pay_razorpay'
        };
        const target = document.getElementById(map[method]);
        if (target) target.classList.add('selected');
        if (window.Booking) window.Booking.refreshSubmitState();
        updateProgress();
    }

    function updateProgress() {
        const steps = [{
                check: () => document.getElementById('f_svc_id').value !== ''
                , label: 'Step 2 of 5 — Select Date'
                , pct: 20
            }
            , {
                check: () => document.getElementById('datePicker').value !== ''
                , label: 'Step 3 of 5 — Select Professional'
                , pct: 40
            }
            , {
                check: () => document.querySelector('.staff-chip.selected') !== null
                , label: 'Step 4 of 5 — Choose Time Slot'
                , pct: 60
            }
            , {
                check: () => document.getElementById('f_time_val').value !== ''
                , label: 'Step 5 of 5 — Choose Payment'
                , pct: 80
            }
            , {
                check: () => document.querySelector('.pay-chip.selected') !== null
                , label: 'Ready to Confirm'
                , pct: 100
            }
        ];

        let pct = 0
            , label = 'Step 1 of 5 — Select a Service';

        for (const step of steps) {
            if (step.check()) {
                pct = step.pct;
                label = step.label;
            } else {
                break;
            }
        }

        document.getElementById('booking-progress-bar').style.width = pct + '%';
        document.getElementById('progress-label').textContent = label;
        document.getElementById('progress-percent').textContent = pct + '%';
    }

    ['f_svc_id', 'datePicker', 'f_staff_id', 'f_time_val', 'f_payment'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new MutationObserver(updateProgress).observe(el, {
            attributes: true
            , childList: true
            , subtree: true
        });
    });

    document.getElementById('datePicker') ? .addEventListener('change', updateProgress);
    document.getElementById('f_payment') ? .addEventListener('change', updateProgress);

    document.addEventListener('click', function(e) {
        if (e.target.closest('.staff-chip, .pay-chip, .slot-btn, #svcGrid article')) {
            setTimeout(updateProgress, 0);
        }
    });

    updateProgress();

</script>
@endpush
