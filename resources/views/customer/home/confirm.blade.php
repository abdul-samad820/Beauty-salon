@extends('layouts.customer')

@section('title', 'Appointment Confirmed')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 70vh; padding: var(--space-4) 0;">
    <div class="card-lux p-5 text-center fade-up" style="max-width: 540px; width: 100%; position: relative; background: var(--bg-card-2);">

        {{-- Success / Payment Pending Icon --}}
        @if($appointment->payment_method === 'razorpay' && $appointment->payment_status !== 'paid')
        <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(201,168,76,.1); color: var(--gold); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4) auto; font-size: var(--text-3xl); box-shadow: 0 0 24px rgba(201,168,76,.15);">
            <i class="bi bi-credit-card"></i>
        </div>
        <h1 class="serif gold-text mb-2" style="font-size: var(--text-2xl); font-weight: 400;">Payment Pending</h1>
        <p style="font-size: var(--text-base); color: var(--text-2); max-width: 400px; margin: 0 auto var(--space-5) auto;">
            Your slot is reserved. Complete payment to confirm your appointment.
        </p>
        @else
        <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--emerald-dim); color: var(--emerald); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4) auto; font-size: var(--text-3xl); box-shadow: 0 0 24px var(--emerald-dim);">
            <i class="bi bi-check2-circle"></i>
        </div>
        <h1 class="serif gold-text mb-2" style="font-size: var(--text-2xl); font-weight: 400;">Appointment Confirmed!</h1>
        <p style="font-size: var(--text-base); color: var(--text-2); max-width: 400px; margin: 0 auto var(--space-5) auto;">
            Thank you for choosing LUMIÈRE. Your reservation has been successfully verified and added to our scheduling system.
        </p>
        @endif

        {{-- Flash Messages --}}
        @if(session('error'))
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
            {{ session('error') }}
        </div>
        @endif

        {{-- Appointment Details --}}
        <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--r-md); padding: var(--space-4); text-align: left; margin-bottom: var(--space-5);">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom: var(--space-2); margin-bottom: var(--space-3); font-size: var(--text-sm);">
                <span style="color: var(--text-3); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Booking Reference</span>
                <span style="font-family: monospace; color: var(--text); font-weight: bold;">#LMR-{{ str_pad($appointment->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>

            <div style="display: flex; align-items: flex-start; gap: var(--space-3); margin-bottom: var(--space-3);">
                <div style="width: 36px; height: 36px; border-radius: var(--r-sm); background: var(--gold-dim); color: var(--gold); display: flex; align-items: center; justify-content: center; font-size: var(--text-md); flex-shrink: 0;">
                    <i class="bi bi-scissors"></i>
                </div>
                <div>
                    <h4 style="font-size: var(--text-sm); font-weight: 600; color: var(--text); margin: 0;">{{ $appointment->service->name }}</h4>
                    <p style="font-size: var(--text-xs); color: var(--text-3); margin-top: 0.1rem;">Duration: {{ $appointment->service->duration_minutes }} Minutes</p>
                </div>
                <div style="margin-left: auto; font-family: var(--ff-display); font-size: var(--text-lg); color: var(--gold);">
                    ₹{{ number_format($appointment->amount, 2) }}
                </div>
            </div>

            <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.03); margin: var(--space-3) 0;">

            <div style="display: flex; flex-direction: column; gap: var(--space-2); font-size: var(--text-sm);">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-3);"><i class="bi bi-calendar3" style="margin-right: 6px;"></i> Date</span>
                    <span style="color: var(--text-2); font-weight: 500;">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, d M Y') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-3);"><i class="bi bi-clock" style="margin-right: 6px;"></i> Time</span>
                    <span style="color: var(--text-2); font-weight: 500; font-family: monospace;">{{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-3);"><i class="bi bi-person-badge" style="margin-right: 6px;"></i> Professional</span>
                    <span style="color: var(--text-2); font-weight: 500;">{{ $appointment->staff->user->name ?? 'Any Available Stylist' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-3);"><i class="bi bi-wallet2" style="margin-right: 6px;"></i> Payment</span>
                    <span style="color: var(--text-2); font-weight: 500;">
                        {{ strtoupper($appointment->payment_method) }}
                        @if($appointment->payment_method === 'razorpay')
                        —
                        @if($appointment->payment_status === 'paid')
                        @if($appointment->status === 'pending')
                        <span style="color: var(--gold);">Paid ✓ — Awaiting Salon Confirmation</span>
                        @else
                        <span style="color: var(--emerald);">Paid ✓</span>
                        @endif
                        @elseif($appointment->payment_status === 'failed')
                        <span style="color: #f87171;">Failed</span>
                        @else
                        <span style="color: var(--gold);">Pending</span>
                        @endif
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div style="display: flex; flex-direction: column; gap: var(--space-2);">

            {{-- Razorpay Pay Now Button --}}
            @if($appointment->payment_method === 'razorpay' && $appointment->payment_status !== 'paid')
            <button id="btn-pay-now" style="width:100%;padding:.85rem;border-radius:8px;border:none;background:var(--gold);color:#000;font-weight:700;font-size:1rem;cursor:pointer;">
                <i class="bi bi-credit-card me-2"></i> Pay ₹{{ number_format($appointment->amount, 2) }} Now
            </button>
            @endif

            <a href="{{ route('customer.appointments', $subdomain) }}" class="btn-lux-gold" style="width: 100%;">
                <i class="bi bi-calendar2-event"></i> View My Appointments
            </a>
            <a href="{{ route('customer.home', $subdomain) }}" class="btn-lux-ghost" style="width: 100%;">
                <i class="bi bi-arrow-left"></i> Return to Services
            </a>
        </div>

        <p style="font-size: var(--text-xs); color: var(--text-3); margin-top: var(--space-4); font-style: italic;">
            A confirmation email has been sent to your registered address.
        </p>
    </div>
</div>
@endsection

@if($appointment->payment_method === 'razorpay' && $appointment->payment_status !== 'paid')
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('btn-pay-now').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Initializing...';

        try {
            const res = await fetch('{{ route("customer.payment.create-order", [$subdomain, $appointment->id]) }}', {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                , }
                , credentials: 'same-origin'
            , });

            if (res.status === 401) {
                alert('Session expired. Please login again.');
                window.location.href = '/{{ $subdomain }}/login';
                return;
            }

            if (!res.ok) {
                const text = await res.text();
                console.error('Server response:', text);
                alert('Could not initiate payment. Status: ' + res.status);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-credit-card me-2"></i> Pay ₹{{ number_format($appointment->amount, 2) }} Now';
                return;
            }

            const data = await res.json();

            if (!data.success) {
                alert('Could not initiate payment. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-credit-card me-2"></i> Pay ₹{{ number_format($appointment->amount, 2) }} Now';
                return;
            }

            const options = {
                key: data.key_id
                , amount: data.amount
                , currency: data.currency
                , name: data.tenant_name
                , description: data.service_name
                , order_id: data.order_id
                , prefill: {
                    name: data.customer_name
                    , email: data.customer_email
                , }
                , theme: {
                    color: '#C9A84C'
                }
                , handler: function(response) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("customer.payment.verify", [$subdomain, $appointment->id]) }}';

                    const fields = {
                        _token: '{{ csrf_token() }}'
                        , razorpay_order_id: response.razorpay_order_id
                        , razorpay_payment_id: response.razorpay_payment_id
                        , razorpay_signature: response.razorpay_signature
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
                        btn.innerHTML = '<i class="bi bi-credit-card me-2"></i> Pay ₹{{ number_format($appointment->amount, 2) }} Now';
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();

        } catch (err) {
            console.error(err);
            alert('Something went wrong. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-credit-card me-2"></i> Pay ₹{{ number_format($appointment->amount, 2) }} Now';
        }
    });

</script>
@endpush
@endif
