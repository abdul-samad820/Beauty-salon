@extends('layouts.owner')

@section('title', 'Billing & Plans')

@section('content')
<div class="mb-4 fade-up s1">
    <div>
        <h1 class="serif" style="font-size: 1.8rem; color: var(--text);">Billing & Plans</h1>
        <p style="font-size: 0.85rem; color: var(--text-3);">Choose a plan that fits your salon's needs</p>
    </div>
</div>

{{-- Current Subscription Status --}}
@if($currentSubscription)
<div class="card-lux mb-5 fade-up s1" style="border: 1px solid rgba(201, 169, 110, 0.4); background: rgba(201, 169, 110, 0.05); padding: 1.2rem 1.5rem;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; background: rgba(201, 169, 110, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-patch-check-fill" style="color: var(--gold); font-size: 1.5rem;"></i>
            </div>
            <div>
                <div style="font-weight: 600; color: var(--text); font-size: 1.1rem;">
                    Current Plan: <span style="color: var(--gold);">{{ $currentSubscription->plan->name }}</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-3); margin-top: 0.2rem;">
                    {{ ucfirst($currentSubscription->billing_cycle) }} Cycle · Expires on
                    <strong style="color: var(--text-2);">{{ $currentSubscription->expires_at->format('d M Y') }}</strong>
                    <span style="color: var(--gold); margin-left: 0.3rem;">({{ $currentSubscription->daysLeft() }} days left)</span>
                </div>
            </div>
        </div>
        <span class="status-badge badge-active" style="padding: 0.4rem 1rem; font-size: 0.8rem;">
            <span class="live-dot" style="margin-right: 0.3rem;"></span> Active
        </span>
    </div>
</div>
@endif

{{-- Billing Cycle Toggle --}}
<div class="d-flex align-items-center justify-content-center gap-3 mb-5 fade-up s2">
    <span id="lbl-monthly" style="font-weight: 600; color: var(--gold); transition: color 0.3s;">Monthly</span>

    <div class="form-check form-switch mb-0 lux-switch-wrapper">
        <input class="form-check-input lux-switch" type="checkbox" id="billingToggle" role="switch" style="width: 3.5rem; height: 1.8rem; cursor: pointer;">
    </div>

    <span id="lbl-yearly" style="font-weight: 400; color: var(--text-3); transition: color 0.3s; display: flex; align-items: center; gap: 0.5rem;">
        Yearly
        <span style="background: var(--emerald-dim); color: var(--emerald); font-size: 0.65rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 20px;">Save up to 20%</span>
    </span>
</div>

{{-- Plans Grid --}}
<div class="row g-4 justify-content-center fade-up s3">
    @foreach($plans as $plan)
    @php
    $isCurrent = $currentSubscription && $currentSubscription->plan_id === $plan->id;
    $isPremium = $plan->slug === 'premium';
    @endphp
    <div class="col-md-4">
        <div class="card-lux h-100 position-relative" style="padding: 2rem 1.5rem; display: flex; flex-direction: column; {{ $isPremium ? 'border: 1px solid var(--gold); background: linear-gradient(180deg, rgba(201,169,110,0.05) 0%, rgba(0,0,0,0) 100%);' : '' }}">

            @if($isPremium)
            <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--gold); color: var(--bg-body); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; padding: 0.3rem 1rem; border-radius: 20px; white-space: nowrap;">
                MOST POPULAR
            </div>
            @endif

            {{-- Plan Name --}}
            <div class="mb-4 text-center">
                <h3 class="serif" style="font-size: 1.5rem; color: {{ $isPremium ? 'var(--gold)' : 'var(--text)' }}; margin-bottom: 0.3rem;">
                    {{ $plan->name }}
                </h3>
                @if($plan->description)
                <p style="font-size: 0.75rem; color: var(--text-3); margin: 0;">{{ $plan->description }}</p>
                @endif
            </div>

            {{-- Price --}}
            <div class="mb-4 text-center" style="min-height: 70px;">
                <div class="price-monthly">
                    <span class="serif" style="font-size: 2.5rem; font-weight: 400; color: var(--text);">
                        ₹{{ number_format($plan->price_monthly, 0) }}
                    </span>
                    <span style="color: var(--text-3); font-size: 0.85rem;">/mo</span>
                </div>
                <div class="price-yearly" style="display: none;">
                    <span class="serif" style="font-size: 2.5rem; font-weight: 400; color: var(--text);">
                        ₹{{ number_format($plan->price_yearly, 0) }}
                    </span>
                    <span style="color: var(--text-3); font-size: 0.85rem;">/yr</span>
                    @if($plan->yearly_saving > 0)
                    <div style="font-size: 0.75rem; color: var(--emerald); margin-top: 0.3rem; font-weight: 500;">
                        Save ₹{{ number_format($plan->yearly_saving, 0) }} vs monthly
                    </div>
                    @endif
                </div>
            </div>

            <hr style="border-color: rgba(255,255,255,0.05); margin-bottom: 1.5rem;">

            {{-- Features --}}
            <ul style="list-style: none; padding: 0; margin: 0 0 2rem; font-size: 0.8rem; flex-grow: 1;">
                <li class="d-flex align-items-center gap-3 mb-3">
                    <div style="color: var(--emerald);"><i class="bi bi-check-circle-fill"></i></div>
                    <span style="color: var(--text-2);">Up to <strong style="color: var(--text);">{{ $plan->max_staff }}</strong> staff members</span>
                </li>
                <li class="d-flex align-items-center gap-3 mb-3">
                    <div style="color: var(--emerald);"><i class="bi bi-check-circle-fill"></i></div>
                    <span style="color: var(--text-2);">Up to <strong style="color: var(--text);">{{ $plan->max_services }}</strong> services</span>
                </li>
                <li class="d-flex align-items-center gap-3 mb-3">
                    <div style="color: var(--emerald);"><i class="bi bi-check-circle-fill"></i></div>
                    <span style="color: var(--text-2);"><strong style="color: var(--text);">{{ $plan->max_appointments_per_month }}</strong> appointments/month</span>
                </li>

                <li class="d-flex align-items-center gap-3 mb-3">
                    @if($plan->inventory_enabled)
                    <div style="color: var(--emerald);"><i class="bi bi-check-circle-fill"></i></div>
                    <span style="color: var(--text-2);">Inventory management</span>
                    @else
                    <div style="color: var(--text-3); opacity: 0.5;"><i class="bi bi-x-circle"></i></div>
                    <span style="color: var(--text-3); text-decoration: line-through; opacity: 0.5;">Inventory management</span>
                    @endif
                </li>
                <li class="d-flex align-items-center gap-3 mb-3">
                    @if($plan->analytics_enabled)
                    <div style="color: var(--emerald);"><i class="bi bi-check-circle-fill"></i></div>
                    <span style="color: var(--text-2);">Advanced analytics</span>
                    @else
                    <div style="color: var(--text-3); opacity: 0.5;"><i class="bi bi-x-circle"></i></div>
                    <span style="color: var(--text-3); text-decoration: line-through; opacity: 0.5;">Advanced analytics</span>
                    @endif
                </li>
                <li class="d-flex align-items-center gap-3">
                    @if($plan->commission_enabled)
                    <div style="color: var(--emerald);"><i class="bi bi-check-circle-fill"></i></div>
                    <span style="color: var(--text-2);">Staff commissions</span>
                    @else
                    <div style="color: var(--text-3); opacity: 0.5;"><i class="bi bi-x-circle"></i></div>
                    <span style="color: var(--text-3); text-decoration: line-through; opacity: 0.5;">Staff commissions</span>
                    @endif
                </li>
            </ul>

            {{-- CTA Button --}}
            @if($isCurrent)
            <button disabled class="btn-lux-ghost" style="width: 100%; justify-content: center; cursor: not-allowed; opacity: 0.7;">
                Current Plan
            </button>
            @else
            <button class="btn-pay-now {{ $isPremium ? 'btn-lux-gold' : 'btn-lux-ghost' }}" data-plan-id="{{ $plan->id }}" data-plan-name="{{ $plan->name }}" data-price-monthly="{{ $plan->price_monthly }}" data-price-yearly="{{ $plan->price_yearly }}" style="width: 100%; justify-content: center;">
                <span class="btn-label-monthly">Select {{ $plan->name }}</span>
                <span class="btn-label-yearly" style="display: none;">Select {{ $plan->name }}</span>
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div class="text-center mt-5 fade-up s4">
    <p style="font-size: 0.75rem; color: var(--text-3); display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.02); padding: 0.5rem 1rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
        <i class="bi bi-shield-check" style="color: var(--emerald); font-size: 1rem;"></i>
        Payments secured by Razorpay · 256-bit SSL encryption
    </p>
</div>
@endsection

@push('styles')
<style>
    /* Custom Toggle Switch for Dark Theme */
    .lux-switch-wrapper .form-check-input:checked {
        background-color: var(--gold);
        border-color: var(--gold);
    }

    .lux-switch-wrapper .form-check-input:focus {
        box-shadow: 0 0 0 0.25rem rgba(201, 169, 110, 0.25);
    }

    .lux-switch-wrapper .form-check-input {
        background-color: var(--bg-input);
        border-color: var(--border);
    }

</style>
@endpush

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    const billingToggle = document.getElementById('billingToggle');
    const lblMonthly = document.getElementById('lbl-monthly');
    const lblYearly = document.getElementById('lbl-yearly');

    // ── Billing cycle toggle logic ────────────────────────────────
    billingToggle.addEventListener('change', function() {
        const isYearly = this.checked;

        // Toggle Prices
        document.querySelectorAll('.price-monthly').forEach(el => el.style.display = isYearly ? 'none' : 'block');
        document.querySelectorAll('.price-yearly').forEach(el => el.style.display = isYearly ? 'block' : 'none');

        // Toggle Button Texts
        document.querySelectorAll('.btn-label-monthly').forEach(el => el.style.display = isYearly ? 'none' : 'inline');
        document.querySelectorAll('.btn-label-yearly').forEach(el => el.style.display = isYearly ? 'inline' : 'none');

        // Toggle Label Colors
        lblMonthly.style.color = isYearly ? 'var(--text-3)' : 'var(--gold)';
        lblMonthly.style.fontWeight = isYearly ? '400' : '600';

        lblYearly.style.color = isYearly ? 'var(--gold)' : 'var(--text-3)';
        lblYearly.style.fontWeight = isYearly ? '600' : '400';
    });

    // ── Pay Now buttons (Razorpay) ────────────────────────────────
    document.querySelectorAll('.btn-pay-now').forEach(btn => {
        btn.addEventListener('click', async function() {
            const planId = this.dataset.planId;
            const planName = this.dataset.planName;
            const billingCycle = billingToggle.checked ? 'yearly' : 'monthly';

            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

            try {
                const res = await fetch('{{ route("owner.razorpay.create-order") }}', {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    , }
                    , body: JSON.stringify({
                        plan_id: planId
                        , billing_cycle: billingCycle
                    })
                , });

                const data = await res.json();

                if (!data.success) {
                    alert('Could not initiate payment. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    return;
                }
                if (data.free_plan) {
                    window.location.href = data.redirect;
                    return;
                }

                // ── Open Razorpay Checkout ───────────────────────
                const options = {
                    key: data.key_id
                    , amount: data.amount
                    , currency: data.currency
                    , name: 'LUMIÈRE'
                    , description: `${data.plan_name} — ${billingCycle}`
                    , order_id: data.order_id
                    , prefill: {
                        name: data.tenant_name
                        , email: data.tenant_email
                    , }
                    , theme: {
                        color: '#C9A96E'
                    }, // LUMIÈRE Gold
                    handler: function(response) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("owner.razorpay.verify") }}';

                        const fields = {
                            _token: '{{ csrf_token() }}'
                            , razorpay_order_id: response.razorpay_order_id
                            , razorpay_payment_id: response.razorpay_payment_id
                            , razorpay_signature: response.razorpay_signature
                            , plan_id: planId
                            , billing_cycle: billingCycle
                        , };

                        Object.entries(fields).forEach(([name, value]) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = value;
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    }
                    , modal: {
                        ondismiss: function() {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();

            } catch (err) {
                console.error(err);
                alert('Something went wrong. Please try again.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });

</script>
@endpush
