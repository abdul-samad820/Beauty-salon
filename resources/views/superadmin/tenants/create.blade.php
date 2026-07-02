@extends('layouts.superadmin')

@section('title', 'Create New Tenant')
@section('breadcrumb')
<a href="{{ route('superadmin.tenants.index') }}" style="color:var(--text-3);text-decoration:none;">Tenants</a>
<i class="bi bi-chevron-right" style="font-size:0.55rem;margin:0 0.4rem;"></i>
<span style="color:var(--text-2);">Create New Tenant</span>
@endsection
@section('page-title', 'Onboard New Parlour')

@section('topbar-actions')
<a href="{{ route('superadmin.tenants.index') }}" class="btn-lux-ghost btn-sm border-0">
    <i class="bi bi-x-lg"></i> Cancel
</a>
@endsection

@push('styles')
<style>
    .stepper-wrap {
        display: flex;
        align-items: flex-start;
        gap: 0;
        margin-bottom: 2rem;
        position: relative;
    }

    .stepper-wrap::before {
        content: '';
        position: absolute;
        top: 18px;
        left: 0;
        right: 0;
        height: 1px;
        background: var(--border-2);
        z-index: 0;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
        position: relative;
        z-index: 1;
    }

    .step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1.5px solid var(--border-2);
        background: var(--bg-card);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--text-3);
        transition: all 0.4s;
    }

    .step.active .step-circle {
        border-color: var(--gold);
        color: var(--gold);
        background: var(--gold-dim);
        box-shadow: 0 0 20px var(--gold-glow);
    }

    .step.done .step-circle {
        border-color: var(--emerald);
        background: var(--emerald-dim);
        color: var(--emerald);
    }

    .step-label {
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: var(--text-3);
        text-align: center;
        transition: color 0.4s;
        white-space: nowrap;
    }

    .step.active .step-label {
        color: var(--gold);
    }

    .step.done .step-label {
        color: var(--emerald);
    }

    .form-panel {
        display: none;
        animation: panelIn 0.5s ease both;
    }

    .form-panel.active {
        display: block;
    }

    @keyframes panelIn {
        from {
            opacity: 0;
            transform: translateX(20px)
        }

        to {
            opacity: 1;
            transform: none
        }
    }

    .form-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);
    }

    .form-card-title {
        font-family: var(--ff-display);
        font-size: 1.2rem;
        font-weight: 400;
        color: var(--text);
        margin-bottom: 0.3rem;
    }

    .form-card-sub {
        font-size: 0.75rem;
        color: var(--text-3);
        margin-bottom: 1.8rem;
    }

    /* Floating labels */
    .fl-group {
        position: relative;
        margin-bottom: 1.4rem;
    }

    .fl-group label {
        position: absolute;
        top: 50%;
        left: 1rem;
        transform: translateY(-50%);
        font-size: 0.82rem;
        color: var(--text-3);
        pointer-events: none;
        transition: all 0.25s;
        background: transparent;
        padding: 0 0.2rem;
        letter-spacing: 0.04em;
    }

    .fl-group.has-icon label {
        left: 2.8rem;
    }

    .fl-group input,
    .fl-group select,
    .fl-group textarea {
        width: 100%;
        background: var(--bg-input);
        border: 1px solid var(--border-2);
        border-radius: 10px;
        color: var(--text);
        font-family: var(--ff-body);
        font-size: 0.85rem;
        font-weight: 300;
        padding: 0.9rem 1rem;
        outline: none;
        transition: border-color 0.3s, background 0.3s, box-shadow 0.3s;
        appearance: none;
    }

    .fl-group.has-icon input,
    .fl-group.has-icon select {
        padding-left: 2.8rem;
    }

    .fl-group textarea {
        padding-top: 1.2rem;
        resize: vertical;
        min-height: 100px;
    }

    .fl-group input:focus,
    .fl-group select:focus,
    .fl-group textarea:focus {
        border-color: var(--gold);
        background: rgba(201, 169, 110, 0.04);
        box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.08);
    }

    .fl-group input:focus+label,
    .fl-group input:not(:placeholder-shown)+label,
    .fl-group select:focus+label,
    .fl-group textarea:focus+label,
    .fl-group textarea:not(:placeholder-shown)+label {
        top: 0;
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        color: var(--gold);
        background: var(--bg-card);
        padding: 0 0.4rem;
    }

    .fl-group input::placeholder,
    .fl-group textarea::placeholder {
        color: transparent;
    }

    .fl-group select option {
        background: var(--bg-card);
        color: var(--text);
    }

    .fl-input-icon {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-3);
        font-size: 0.9rem;
        pointer-events: none;
    }

    .fl-group textarea~.fl-input-icon {
        top: 1.2rem;
        transform: none;
    }

    .fl-group.error input,
    .fl-group.error select {
        border-color: var(--rose);
    }

    .fl-group.error label {
        color: var(--rose);
    }

    .field-hint {
        font-size: 0.68rem;
        color: var(--text-3);
        margin-top: 0.3rem;
    }

    /* Plan cards */
    .plan-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .plan-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1.5px solid var(--border-2);
        border-radius: 12px;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.35s;
        position: relative;
        overflow: hidden;
    }

    .plan-card:hover {
        border-color: var(--gold);
        transform: translateY(-2px);
    }

    .plan-card.selected {
        border-color: var(--gold);
        background: var(--gold-dim);
        box-shadow: 0 8px 32px rgba(201, 169, 110, 0.2);
    }

    .plan-check {
        position: absolute;
        top: 0.8rem;
        right: 0.8rem;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 1.5px solid var(--border-2);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        font-size: 0.7rem;
        color: transparent;
    }

    .plan-card.selected .plan-check {
        background: var(--gold);
        border-color: var(--gold);
        color: #1a1400;
    }

    .plan-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .plan-name {
        font-family: var(--ff-display);
        font-size: 1.3rem;
        font-weight: 400;
        color: var(--text);
        margin-bottom: 0.2rem;
    }

    .plan-price {
        font-family: var(--ff-display);
        font-size: 1.6rem;
        font-weight: 400;
        margin-bottom: 0.8rem;
    }

    .plan-price small {
        font-family: var(--ff-body);
        font-size: 0.7rem;
        color: var(--text-3);
    }

    .plan-features {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .plan-features li {
        font-size: 0.75rem;
        color: var(--text-2);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .plan-features li i {
        color: var(--emerald);
        font-size: 0.75rem;
    }

    /* Review block */
    .review-block {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 1.2rem;
        margin-bottom: 1rem;
    }

    .review-label {
        font-size: 0.6rem;
        font-weight: 600;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--text-3);
        margin-bottom: 0.8rem;
    }

    .review-row {
        display: flex;
        justify-content: space-between;
        padding: 0.4rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        font-size: 0.82rem;
    }

    .review-row:last-child {
        border-bottom: none;
    }

    .review-key {
        color: var(--text-3);
    }

    .review-val {
        color: var(--text);
        font-weight: 400;
        text-align: right;
    }

    .form-progress {
        height: 3px;
        background: var(--border-2);
        border-radius: 2px;
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .form-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--teal), var(--gold));
        border-radius: 2px;
        transition: width 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    }

    @media(max-width:576px) {
        .plan-grid {
            grid-template-columns: 1fr;
        }

        .stepper-wrap {
            gap: 0.5rem;
        }
    }

</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-11">

        {{-- Progress bar --}}
        <div class="form-progress fade-in-up">
            <div class="form-progress-fill" id="progressFill" style="width:25%;"></div>
        </div>

        {{-- Stepper --}}
        <div class="stepper-wrap fade-in-up stagger-1">
            <div class="step active" id="step-dot-1">
                <div class="step-circle" id="sc1">1</div>
                <div class="step-label">Business Info</div>
            </div>
            <div class="step" id="step-dot-2">
                <div class="step-circle" id="sc2">2</div>
                <div class="step-label">Choose Plan</div>
            </div>
            <div class="step" id="step-dot-3">
                <div class="step-circle" id="sc3">3</div>
                <div class="step-label">Settings</div>
            </div>
            <div class="step" id="step-dot-4">
                <div class="step-circle" id="sc4">4</div>
                <div class="step-label">Review & Launch</div>
            </div>
        </div>

        <form method="POST" action="{{ route('superadmin.tenants.store') }}" id="mainForm">
            @csrf

            {{-- ══ STEP 1: BUSINESS INFO ══ --}}
            <div class="form-panel active" id="panel-1">
                <div class="form-card fade-in-up stagger-2">
                    <div class="form-card-title">Parlour Business Information</div>
                    <div class="form-card-sub">Please provide the core details for the new salon tenant account.</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-buildings fl-input-icon"></i>
                                <input type="text" name="business_name" id="salonName" placeholder="x" value="{{ old('business_name') }}" required />
                                <label>Salon / Parlour Name *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-link-45deg fl-input-icon"></i>
                                <input type="text" name="subdomain" id="slugInput" placeholder="x" value="{{ old('subdomain') }}" required />
                                <label>Subdomain Slug *</label>
                            </div>
                            <div class="field-hint">Preview: <span id="slugPreview" style="color:var(--gold);">__.lumiere.app</span></div>
                        </div>
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-person-fill fl-input-icon"></i>
                                <input type="text" name="owner_name" placeholder="x" value="{{ old('owner_name') }}" required />
                                <label>Owner Full Name *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-envelope-fill fl-input-icon"></i>
                                <input type="email" name="owner_email" placeholder="x" value="{{ old('owner_email') }}" required />
                                <label>Owner Email Address *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-lock-fill fl-input-icon"></i>
                                <input type="password" name="owner_password" id="ownerPass" placeholder="x" required minlength="8" />
                                <label>Owner Password *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-lock-fill fl-input-icon"></i>
                                <input type="password" name="owner_password_confirmation" placeholder="x" required />
                                <label>Confirm Password *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-telephone-fill fl-input-icon"></i>
                                <input type="text" name="phone" placeholder="x" value="{{ old('phone') }}" required />
                                <label>Phone Number *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-geo-alt-fill fl-input-icon"></i>
                                <textarea name="address" placeholder="x">{{ old('address') }}</textarea>
                                <label>Business Address</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;">
                    <button type="button" class="btn-lux-gold" onclick="goTo(2)">Next: Choose Plan <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            {{-- ══ STEP 2: PLAN ══ --}}
            <div class="form-panel" id="panel-2">
                <div class="form-card fade-in-up stagger-1">
                    <div class="form-card-title">Select Subscription Plan</div>
                    <div class="form-card-sub">Choose a plan for your tenant</div>
                    <input type="hidden" name="plan" id="planInput" value="{{ old('plan', 'basic') }}" />
                    <div class="plan-grid">
                        {{-- Free --}}
                        <div class="plan-card {{ old('plan', 'basic') === 'free' ? 'selected' : '' }}" onclick="selectPlan(this,'free')">
                            <div class="plan-check"><i class="bi bi-check"></i></div>
                            <div class="plan-icon" style="background:var(--teal-dim);color:var(--teal-light);"><i class="bi bi-stars"></i></div>
                            <div class="plan-name">Free</div>
                            <div class="plan-price" style="color:var(--teal-light);">₹0 <small>/ 14 days trial</small></div>
                            <ul class="plan-features">
                                <li><i class="bi bi-check-circle-fill"></i> 1 Staff Member</li>
                                <li><i class="bi bi-check-circle-fill"></i> 10 Services</li>
                                <li><i class="bi bi-check-circle-fill"></i> Basic Reporting</li>
                            </ul>
                        </div>

                        <div class="plan-card {{ old('plan', 'basic') === 'basic' ? 'selected' : '' }}" onclick="selectPlan(this,'basic')">

                            <div class="plan-check"><i class="bi bi-check"></i></div>
                            <div class="plan-icon" style="background:var(--purple-dim);color:#a78bfa;"><i class="bi bi-gem"></i></div>
                            <div class="plan-name">Pro</div>
                            <div class="plan-price" style="color:#a78bfa;">₹999 <small>/ month</small></div>

                            <ul class="plan-features">
                                <li><i class="bi bi-check-circle-fill"></i> Unlimited Staff</li>
                                <li><i class="bi bi-check-circle-fill"></i> Unlimited Services</li>
                                <li><i class="bi bi-check-circle-fill"></i> Analytics Dashboard</li>
                                <li><i class="bi bi-check-circle-fill"></i> Email Reminders</li>
                            </ul>
                        </div>
                        {{-- Enterprise --}}
                        <div class="plan-card {{ old('plan') === 'premium' ? 'selected' : '' }}" onclick="selectPlan(this,'premium')">

                            <div class="plan-check"><i class="bi bi-check"></i></div>
                            <div class="plan-icon" style="background:var(--gold-dim);color:var(--gold);"><i class="bi bi-crown-fill"></i></div>
                            <div class="plan-name">Premium</div>
                            <div class="plan-price" style="color:var(--gold);">₹2,499 <small>/ month</small></div>

                            <ul class="plan-features">
                                <li><i class="bi bi-check-circle-fill"></i> Everything in Basic</li>
                                <li><i class="bi bi-check-circle-fill"></i> WhatsApp Integration</li>
                                <li><i class="bi bi-check-circle-fill"></i> Priority Support</li>
                                <li><i class="bi bi-check-circle-fill"></i> Advanced Analytics</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <button type="button" class="btn-lux-ghost" onclick="goTo(1)"><i class="bi bi-arrow-left"></i> Back</button>
                    <button type="button" class="btn-lux-gold" onclick="goTo(3)">Next: Settings <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            {{-- ══ STEP 3: SETTINGS ══ --}}
            <div class="form-panel" id="panel-3">
                <div class="form-card fade-in-up stagger-1">
                    <div class="form-card-title">Default Settings</div>
                    <div class="form-card-sub">These settings can be updated by the owner later</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-clock fl-input-icon"></i>
                                <input type="text" name="working_start" placeholder="x" value="09:00" />
                                <label>Opening Time</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fl-group has-icon">
                                <i class="bi bi-clock fl-input-icon"></i>
                                <input type="text" name="working_end" placeholder="x" value="20:00" />
                                <label>Closing Time</label>
                            </div>
                        </div>
                    </div>
                    <div style="font-size:0.8rem;color:var(--text-3);padding:0.5rem 0;">
                        <i class="bi bi-info-circle"></i> Default timezone: Asia/Kolkata (IST)
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <button type="button" class="btn-lux-ghost" onclick="goTo(2)"><i class="bi bi-arrow-left"></i> Back</button>
                    <button type="button" class="btn-lux-gold" onclick="goTo(4)">Review & Launch <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            {{-- ══ STEP 4: REVIEW ══ --}}
            <div class="form-panel" id="panel-4">
                <div class="form-card fade-in-up stagger-1">
                    <div class="form-card-title">Review & Launch</div>
                    <div class="form-card-sub">Please confirm the details and create the tenant</div>

                    <div class="review-block">
                        <div class="review-label">Business Details</div>
                        <div class="review-row"><span class="review-key">Salon Name</span><span class="review-val" id="rev-name" style="color:var(--gold);">—</span></div>
                        <div class="review-row"><span class="review-key">Subdomain</span><span class="review-val"><span id="rev-subdomain" style="font-family:monospace;color:var(--teal-light);">—</span></span></div>
                        <div class="review-row"><span class="review-key">Owner</span><span class="review-val" id="rev-owner">—</span></div>
                        <div class="review-row"><span class="review-key">Email</span><span class="review-val" id="rev-email">—</span></div>
                        <div class="review-row"><span class="review-key">Phone</span><span class="review-val" id="rev-phone">—</span></div>
                    </div>

                    <div class="review-block">
                        <div class="review-label">Subscription</div>
                        <div class="review-row"><span class="review-key">Plan</span><span class="review-val" id="rev-plan">—</span></div>
                    </div>

                    <div style="background:var(--emerald-dim);border:1px solid rgba(16,185,129,0.2);border-radius:8px;padding:1rem;margin-top:0.5rem;">
                        <div style="font-size:0.78rem;color:var(--emerald);font-weight:500;">
                            <i class="bi bi-check-circle-fill"></i> Login credentials for the owner will be generated automatically.
                        </div>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <button type="button" class="btn-lux-ghost" onclick="goTo(3)"><i class="bi bi-arrow-left"></i> Back</button>
                    <button type="submit" class="btn-lux-gold" style="padding:0.7rem 2rem;font-size:0.8rem;">
                        <i class="bi bi-rocket-takeoff-fill"></i> Create Tenant
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 4;
    const progressPcts = {
        1: 25
        , 2: 50
        , 3: 75
        , 4: 100
    };

    function goTo(n) {

        if (n > currentStep) {
            let isValid = true;
            const currentPanel = document.getElementById('panel-' + currentStep);

            const requiredInputs = currentPanel.querySelectorAll('input[required], select[required], textarea[required]');

            requiredInputs.forEach(input => {
                if (!input.checkValidity()) {
                    input.reportValidity(); 
                    isValid = false;
                }
            });

            if (!isValid) return;
        }

        // Hide current, show next
        document.getElementById('panel-' + currentStep).classList.remove('active');
        document.getElementById('step-dot-' + currentStep).classList.remove('active');
        if (n > currentStep) document.getElementById('step-dot-' + currentStep).classList.add('done');

        currentStep = n;
        document.getElementById('panel-' + n).classList.add('active');
        document.getElementById('step-dot-' + n).classList.add('active');
        document.getElementById('progressFill').style.width = progressPcts[n] + '%';

        window.scrollTo({
            top: 0
            , behavior: 'smooth'
        });

        if (n === 4) buildReview();
    }

    function selectPlan(el, plan) {
        document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('planInput').value = plan;
    }

    function buildReview() {
        const form = document.getElementById('mainForm');
        document.getElementById('rev-name').textContent = form.querySelector('[name=business_name]').value || '—';
        const slug = form.querySelector('[name=subdomain]').value;
        document.getElementById('rev-subdomain').textContent = slug ? slug + '.lumiere.app' : '—';
        document.getElementById('rev-owner').textContent = form.querySelector('[name=owner_name]').value || '—';
        document.getElementById('rev-email').textContent = form.querySelector('[name=owner_email]').value || '—';
        document.getElementById('rev-phone').textContent = form.querySelector('[name=phone]').value || '—';
        const plan = document.getElementById('planInput').value;
        document.getElementById('rev-plan').innerHTML = `<span class="plan-badge plan-${plan}">${plan.charAt(0).toUpperCase()+plan.slice(1)}</span>`;
    }

    // Slug auto-generate from name
    document.getElementById('salonName')?.addEventListener('input', function() {
        const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        const slugInput = document.getElementById('slugInput');
        if (slugInput && !slugInput.dataset.manual) {
            slugInput.value = slug;
            document.getElementById('slugPreview').textContent = slug ? slug + '.lumiere.app' : '__.lumiere.app';
        }
    });
    document.getElementById('slugInput')?.addEventListener('input', function() {
        this.dataset.manual = '1';
        const val = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
        this.value = val;
        document.getElementById('slugPreview').textContent = val ? val + '.lumiere.app' : '__.lumiere.app';
    });

</script>
@endpush
