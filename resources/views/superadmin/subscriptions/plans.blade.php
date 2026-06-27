@extends('layouts.superadmin')

@section('title', 'Subscription Architecture Tiers')
@section('page-title', 'SaaS Pricing Configuration Matrix')
@section('breadcrumb', 'Platform / Subscription Models')

@section('topbar-actions')
<a href="{{ route('superadmin.subscriptions.index') }}" class="btn-lux-ghost btn-sm border-0">
    <i class="bi bi-layers-half"></i> View Active Contracts
</a>
<button type="button" class="btn-lux-gold btn-sm" onclick="LuxModal.open('addPlanModal')">
    <i class="bi bi-plus-lg"></i> Provision New Tier Model
</button>
@endsection

@section('content')

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    @php
    $sc = [
    ['label' => 'Total Configured Plans', 'val' => $stats['total_plans'] ?? 0, 'color' => 'var(--gold)', 'bg' => 'var(--gold-dim)', 'icon' => 'bi-grid-1x2'],
    ['label' => 'Active Billing Matrix', 'val' => $stats['active_plans'] ?? 0, 'color' => 'var(--emerald)', 'bg' => 'var(--emerald-dim)', 'icon' => 'bi-check-circle-fill'],
    ['label' => 'Active Account Lifecycles', 'val' => $stats['total_subscriptions'] ?? 0,'color' => 'var(--purple)', 'bg' => 'var(--purple-dim)', 'icon' => 'bi-layers'],
    ['label' => 'Gross Accumulated MRR', 'val' => '₹' . number_format($stats['monthly_revenue'] ?? 0), 'color' => 'var(--amber)', 'bg' => 'var(--amber-dim)', 'icon' => 'bi-currency-rupee'],
    ];
    @endphp
    @foreach($sc as $i => $card)
    <div class="col-6 col-xl-3 fade-up s{{ $i + 1 }}">
        {{-- FIX: h-100 to make cards equal height, padding-right to avoid icon overlap --}}
        <div class="card-lux kpi-pad h-100" style="border-top:2px solid {{ $card['color'] }}; display: flex; flex-direction: column; justify-content: space-between;">
            <div class="kpi-icon-abs" style="background:{{ $card['bg'] }};color:{{ $card['color'] }};"><i class="bi {{ $card['icon'] }}"></i></div>
            <div class="kpi-label" style="padding-right: 48px;">{{ $card['label'] }}</div>
            <div class="kpi-value" style="color:{{ $card['color'] }}; font-size: 1.8rem; margin-bottom: 0;">{{ $card['val'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Section header --}}
<div class="d-flex align-items-center justify-content-between mb-4 fade-up s2">
    <div>
        <div style="font-family: var(--ff-display); font-size: 1.4rem; color: var(--text);">Active Product Pricing Blueprints</div>
        <div class="faint" style="font-size: var(--text-xs); margin-top: 2px;">Configure cloud tenants capability constraints.</div>
    </div>
</div>

{{-- Plan Cards Grid --}}
<div class="row g-4 fade-up s3">
    @forelse($plans ?? [] as $plan)
    @php
    $accentColor = match($plan->slug) {
    'premium' => 'var(--amber)',
    'basic', 'pro' => 'var(--gold)',
    default => 'var(--border-2)',
    };
    $badgeBg = match($plan->slug) {
    'premium' => 'var(--amber-dim)',
    'basic', 'pro' => 'var(--gold-dim)',
    default => 'rgba(255,255,255,0.05)',
    };
    $badgeColor = match($plan->slug) {
    'premium' => 'var(--amber)',
    'basic', 'pro' => 'var(--gold)',
    default => 'var(--text-3)',
    };
    @endphp
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card-lux p-0 h-100 d-flex flex-column" style="border-top:2px solid {{ $accentColor }};overflow:hidden;">

            {{-- FIX: flex-grow-1 ensures bottom button stays at the bottom even if content is less --}}
            <div style="padding:var(--space-5); flex-grow: 1;">

                {{-- Plan badge + status --}}
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <span class="plan-badge" style="background:{{ $badgeBg }};color:{{ $badgeColor }};font-size:0.65rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;">
                        {{ $plan->name }}
                    </span>
                    <span class="status-badge {{ $plan->is_active ? 'badge-active' : 'badge-inactive' }}">
                        @if($plan->is_active) <span class="live-dot" style="width:5px; height:5px;"></span> @endif
                        {{ $plan->is_active ? 'Live Profile' : 'Deallocated' }}
                    </span>
                </div>

                {{-- Pricing (FIX: baseline alignment so text doesn't look weird) --}}
                <div class="mb-4">
                    <div class="d-flex align-items-baseline gap-2">
                        <div style="font-size:2rem;font-weight:500;font-family:var(--ff-display);color:var(--text);line-height:1;">
                            ₹{{ number_format($plan->price_monthly, 0) }}
                        </div>
                        <div class="faint" style="font-size:var(--text-xs);font-weight:500;">/mo billing cycle</div>
                    </div>

                    @if($plan->price_yearly > 0)
                    <div style="font-size:var(--text-xs);color:var(--text-3);margin-top:8px;display:flex;align-items:center;gap:.5rem;">
                        <span>₹{{ number_format($plan->price_yearly, 0) }}/annum</span>
                        @if($plan->yearly_saving > 0)
                        <span style="color:var(--emerald);font-weight:700;background:var(--emerald-dim);padding:2px 6px;border-radius:4px;font-size:10px; letter-spacing: 0.05em;">
                            SAVE ₹{{ number_format($plan->yearly_saving, 0) }}
                        </span>
                        @endif
                    </div>
                    @endif
                </div>

                @if($plan->description)
                <p class="muted" style="font-size:var(--text-xs);line-height:1.6;margin-bottom:var(--space-4);padding-bottom:var(--space-4);border-bottom:1px solid var(--border);">
                    {{ $plan->description }}
                </p>
                @endif

                {{-- Feature list --}}
                @php
                $features = [
                ['txt' => "Up to {$plan->max_staff} active artist staff seats", 'on' => true],
                ['txt' => "Up to {$plan->max_services} treatment catalog menus", 'on' => true],
                ['txt' => number_format($plan->max_appointments_per_month) . " bookings/month", 'on' => true],
                ['txt' => "Inventory management module", 'on' => (bool)$plan->inventory_enabled],
                ['txt' => "Revenue analytics & telemetry dashboard", 'on' => (bool)$plan->analytics_enabled],
                ['txt' => "Stylist commission splitting algorithm", 'on' => (bool)$plan->commission_enabled],
                ];
                @endphp
                <div style="display:flex;flex-direction:column;gap:.8rem;">
                    @foreach($features as $feat)
                    <div style="display:flex;align-items:flex-start;gap:.6rem;font-size:var(--text-xs);">
                        <span style="flex-shrink:0;display:flex;align-items:center;justify-content:center;width:16px;height:16px;border-radius:50%;margin-top:1px;
                            background:{{ $feat['on'] ? 'var(--gold-dim)' : 'rgba(255,255,255,0.04)' }};
                            color:{{ $feat['on'] ? 'var(--gold)' : 'var(--text-3)' }};">
                            <i class="bi {{ $feat['on'] ? 'bi-check' : 'bi-dash' }}" style="font-size:12px;font-weight:800;"></i>
                        </span>
                        <span style="line-height:1.4;color:{{ $feat['on'] ? 'var(--text-2)' : 'var(--text-3)' }};
                            {{ $feat['on'] ? '' : 'text-decoration:line-through;opacity:.4;' }}">
                            {{ $feat['txt'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Card footer action --}}
            <div class="mt-auto" style="border-top:1px solid var(--border);padding:var(--space-3) var(--space-5);background:rgba(255,255,255,0.02);">
                <button type="button" class="btn-lux-ghost btn-sm w-100" onclick="openEditPlan({{ json_encode([
        'id'                         => $plan->id,
        'name'                        => $plan->name,
        'description'                 => $plan->description,
        'price_monthly'               => $plan->price_monthly,
        'price_yearly'                => $plan->price_yearly,
        'max_staff'                   => $plan->max_staff,
        'max_services'                => $plan->max_services,
        'max_appointments_per_month'  => $plan->max_appointments_per_month,
        'is_active'                   => $plan->is_active,
        'inventory_enabled'           => $plan->inventory_enabled,
        'analytics_enabled'           => $plan->analytics_enabled,
        'commission_enabled'          => $plan->commission_enabled,
    ]) }})">
                    <i class="bi bi-pencil-square"></i> Modify Tier Constraints
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card-lux" style="text-align:center;padding:5rem 2rem;border-style:dashed;">
            <i class="bi bi-grid-1x2 faint" style="font-size:2.5rem;display:block;margin-bottom:1rem;"></i>
            <div style="font-family: var(--ff-display); font-size: 1.4rem; color: var(--text);">No active pricing architectures</div>
            <p class="muted" style="font-size:var(--text-sm);margin-top:.5rem;max-width:380px;margin-inline:auto;">
                Initialize plan configurations to map incoming subscription monetization pipelines.
            </p>
            <button type="button" class="btn-lux-gold btn-sm mt-4" onclick="LuxModal.open('addPlanModal')">
                <i class="bi bi-plus-lg"></i> Provision First Tier
            </button>
        </div>
    </div>
    @endforelse
</div>

{{-- Add Plan Modal --}}
<x-cards.modal id="addPlanModal" title="Provision New Pricing Blueprint">
    <form method="POST" action="{{ route('superadmin.plans.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <x-forms.input name="name" label="Plan Branding Name *" placeholder="e.g. Scaling Enterprise Pro" :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.input name="slug" label="Unique Slug Identifier *" placeholder="e.g. enterprise-pro" :required="true" />
            </div>
        </div>

        <div class="mt-3">
            <x-forms.textarea name="description" label="Marketing Description" placeholder="Outline specific market targeting metrics…" :rows="2" />
        </div>

        <div class="row g-3 mt-0">
            <div class="col-12 col-md-6">
                <x-forms.input name="price_monthly" label="Monthly Charge (₹) *" type="number" step="0.01" min="0" placeholder="2999" :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.input name="price_yearly" label="Annual Charge (₹) *" type="number" step="0.01" min="0" placeholder="29990" :required="true" />
            </div>
        </div>

        <div class="row g-3 mt-0">
            <div class="col-4">
                <x-forms.input name="max_staff" label="Staff Seats *" type="number" min="1" value="5" :required="true" />
            </div>
            <div class="col-4">
                <x-forms.input name="max_services" label="Service Menus *" type="number" min="1" value="20" :required="true" />
            </div>
            <div class="col-4">
                <x-forms.input name="max_appointments_per_month" label="Bookings/Mo *" type="number" min="1" value="500" :required="true" />
            </div>
        </div>

        <div class="mt-4">
            <label class="lux-label mb-2">Module Access Layers</label>
            <div style="display:flex;flex-wrap:wrap;gap:1rem;padding:1rem;border:1px solid var(--border-2);border-radius:var(--r-md);background:var(--bg-input);">
                @foreach(['inventory_enabled' => 'Inventory Module', 'analytics_enabled' => 'Analytics Dashboard', 'commission_enabled' => 'Commission Algorithm'] as $key => $lbl)
                <label style="display:inline-flex;align-items:center;gap:.5rem;font-size:var(--text-xs);font-weight:500;color:var(--text);cursor:pointer;">
                    <input type="checkbox" name="{{ $key }}" value="1" style="accent-color: var(--gold); width:16px; height:16px;"> {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div style="margin-top:1.5rem;display:flex;align-items:center;justify-content:flex-end;gap:.75rem;border-top:1px solid var(--border);padding-top:1.2rem;">
            <button type="button" onclick="LuxModal.close('addPlanModal')" class="btn-lux-ghost btn-sm border-0">Cancel</button>
            <button type="submit" class="btn-lux-gold btn-sm">
                <i class="bi bi-plus-lg"></i> Provision Tier
            </button>
        </div>
    </form>
</x-cards.modal>

{{-- Edit Plan Modal --}}
<x-cards.modal id="editPlanModal" title="Modify Tier Framework Parameters">
    <form method="POST" id="editPlanForm">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <x-forms.input name="name" id="ep_name" label="Plan Name *" :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <label class="lux-label">Lifecycle State</label>
                {{-- FIX: Apply color-scheme dark to native select for correct rendering --}}
                <div style="position: relative;">
                    <select name="is_active" id="ep_active" class="lux-input" style="cursor: pointer; padding-right: 2.5rem; color-scheme: dark; background-color: var(--bg-input); color: var(--text);">
                        <option value="1" style="background: var(--bg-card); color: var(--text);">Active State Node</option>
                        <option value="0" style="background: var(--bg-card); color: var(--text);">Inactive / Deallocated</option>
                    </select>
                    <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <x-forms.textarea name="description" id="ep_desc" label="Description" :rows="2" />
        </div>

        <div class="row g-3 mt-0">
            <div class="col-12 col-md-6">
                <x-forms.input name="price_monthly" id="ep_monthly" label="Monthly Charge (₹) *" type="number" step="0.01" min="0" :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.input name="price_yearly" id="ep_yearly" label="Annual Charge (₹) *" type="number" step="0.01" min="0" :required="true" />
            </div>
        </div>

        <div class="row g-3 mt-0">
            <div class="col-4">
                <x-forms.input name="max_staff" id="ep_staff" label="Staff Seats *" type="number" min="1" :required="true" />
            </div>
            <div class="col-4">
                <x-forms.input name="max_services" id="ep_services" label="Service Menus *" type="number" min="1" :required="true" />
            </div>
            <div class="col-4">
                <x-forms.input name="max_appointments_per_month" id="ep_appts" label="Bookings/Mo *" type="number" min="1" :required="true" />
            </div>
        </div>

        <div class="mt-4">
            <label class="lux-label mb-2">Module Access Layers</label>
            <div style="display:flex;flex-wrap:wrap;gap:1rem;padding:1rem;border:1px solid var(--border-2);border-radius:var(--r-md);background:var(--bg-input);">
                @foreach(['inventory_enabled' => 'Inventory Module', 'analytics_enabled' => 'Analytics Dashboard', 'commission_enabled' => 'Commission Algorithm'] as $key => $lbl)
                <label style="display:inline-flex;align-items:center;gap:.5rem;font-size:var(--text-xs);font-weight:500;color:var(--text);cursor:pointer;">
                    <input type="checkbox" name="{{ $key }}" id="ep_{{ $key }}" value="1" style="accent-color: var(--gold); width:16px; height:16px;"> {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div style="margin-top:1.5rem;display:flex;align-items:center;justify-content:flex-end;gap:.75rem;border-top:1px solid var(--border);padding-top:1.2rem;">
            <button type="button" onclick="LuxModal.close('editPlanModal')" class="btn-lux-ghost btn-sm border-0">Cancel</button>
            <button type="submit" class="btn-lux-gold btn-sm">
                <i class="bi bi-check-lg"></i> Save Changes
            </button>
        </div>
    </form>
</x-cards.modal>

@endsection

@push('scripts')
<script>
    function openEditPlan(plan) {
        if (!plan) return;
        document.getElementById('editPlanForm').action = '{{ url("superadmin/plans") }}/' + plan.id;

        const ef = document.getElementById('editPlanForm');
        ef.querySelector('[name="name"]').value = plan.name;
        ef.querySelector('[name="description"]').value = plan.description || '';
        ef.querySelector('[name="price_monthly"]').value = plan.price_monthly;
        ef.querySelector('[name="price_yearly"]').value = plan.price_yearly;
        ef.querySelector('[name="max_staff"]').value = plan.max_staff;
        ef.querySelector('[name="max_services"]').value = plan.max_services;
        ef.querySelector('[name="max_appointments_per_month"]').value = plan.max_appointments_per_month;
        document.getElementById('ep_active').value = plan.is_active ? '1' : '0';

        document.getElementById('ep_inventory_enabled').checked = parseInt(plan.inventory_enabled) === 1;
        document.getElementById('ep_analytics_enabled').checked = parseInt(plan.analytics_enabled) === 1;
        document.getElementById('ep_commission_enabled').checked = parseInt(plan.commission_enabled) === 1;

        LuxModal.open('editPlanModal');
    }

</script>
@endpush
