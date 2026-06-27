@extends('layouts.superadmin')

@section('title', 'Subscriptions')
@section('page-title', 'Subscriptions')
@section('page-sub', 'Tenant subscription management')
@push('styles')
<style>
    /* Premium Scroller */
    .lux-scroller::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(201, 169, 110, 0.3);
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb:hover {
        background: var(--gold);
    }

    /* Sticky Header */
    .lux-table thead th {
        position: sticky;
        top: 0;
        background: var(--bg-card);
        z-index: 10;
    }

</style>
@endpush
@section('content')

{{-- FIXED KPI Row (Icon Top, Text Bottom) --}}
<div class="row g-3 mb-4 fade-up">
    @php
    $cards = [
    ['label'=>'Total Subscriptions','val'=>$stats['total'], 'color'=>'var(--gold)', 'bg'=>'var(--gold-dim)', 'icon'=>'bi-layers-fill'],
    ['label'=>'Active', 'val'=>$stats['active'], 'color'=>'var(--emerald)','bg'=>'var(--emerald-dim)','icon'=>'bi-check-circle-fill'],
    ['label'=>'On Trial', 'val'=>$stats['trial'], 'color'=>'var(--amber)', 'bg'=>'var(--amber-dim)', 'icon'=>'bi-clock'],
    ['label'=>'Expired', 'val'=>$stats['expired'], 'color'=>'var(--rose)', 'bg'=>'var(--rose-dim)', 'icon'=>'bi-x-circle'],
    ['label'=>'Expiring (7 days)', 'val'=>$stats['expiring_soon'],'color'=>'var(--amber)','bg'=>'var(--amber-dim)', 'icon'=>'bi-exclamation-triangle'],
    ['label'=>'Total Revenue', 'val'=>'₹'.number_format($stats['revenue'],0,'.',','),'color'=>'var(--gold)','bg'=>'var(--gold-dim)','icon'=>'bi-currency-rupee'],
    ];
    @endphp
    @foreach($cards as $i => $c)
    <div class="col-xl-2 col-md-4 col-6 fade-up s{{ $i + 1 }}">
        <div class="card-lux p-3" style="height:100%; display: flex; flex-direction: column; justify-content: space-between;">

            {{-- Icon Section (Top) --}}
            <div style="width: 42px; height: 42px; border-radius: 12px; background: {{ $c['bg'] }}; color: {{ $c['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 1.2rem; border: 1px solid rgba(255,255,255,0.02);">
                <i class="bi {{ $c['icon'] }}"></i>
            </div>

            {{-- Details Section (Bottom) --}}
            <div>
                <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; line-height: 1.2;">
                    {{ $c['label'] }}
                </div>
                <div style="font-family: var(--ff-display); font-size: 1.6rem; color: {{ $c['color'] }}; font-weight: 600; line-height: 1; margin-bottom: 0;">
                    {{ $c['val'] }}
                </div>
            </div>

        </div>
    </div>
    @endforeach
</div>

{{-- Actions & Filters (Upgraded to Dark Theme) --}}
<div class="card-lux p-3 mb-4 fade-up s2">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;justify-content:space-between;">

        <div style="display:flex;gap:.75rem;">
            <a href="{{ route('superadmin.plans.index') }}" class="btn-lux-ghost btn-sm border-0">
                <i class="bi bi-grid"></i> Manage Plans
            </a>
            <button class="btn-lux-gold btn-sm" onclick="LuxModal.open('addSubModal')">
                <i class="bi bi-plus-lg"></i> Assign Subscription
            </button>
        </div>

        {{-- Filters --}}
        <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">

            {{-- Status Select --}}
            <div style="position: relative;">
                <select name="status" class="lux-input" style="width:140px; padding-right: 2rem; font-size: 0.8rem; color-scheme: dark; background: var(--bg-input); color: var(--text); cursor: pointer; -webkit-appearance: none; appearance: none;" onchange="this.form.submit()">
                    <option value="all" style="background: var(--bg-card); color: var(--text);" {{ request('status','all')==='all'?'selected':'' }}>All Status</option>
                    @foreach(['active','trial','expired','cancelled'] as $st)
                    <option value="{{ $st }}" style="background: var(--bg-card); color: var(--text);" {{ request('status')===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <div style="position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                    <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
                </div>
            </div>

            {{-- Plan Select --}}
            <div style="position: relative;">
                <select name="plan_id" class="lux-input" style="width:140px; padding-right: 2rem; font-size: 0.8rem; color-scheme: dark; background: var(--bg-input); color: var(--text); cursor: pointer; -webkit-appearance: none; appearance: none;" onchange="this.form.submit()">
                    <option value="" style="background: var(--bg-card); color: var(--text);">All Plans</option>
                    @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" style="background: var(--bg-card); color: var(--text);" {{ request('plan_id')==$plan->id?'selected':'' }}>{{ $plan->name }}</option>
                    @endforeach
                </select>
                <div style="position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                    <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
                </div>
            </div>

            {{-- Search --}}
            <div style="position: relative;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y" style="left: 0.8rem; font-size: 0.75rem; color: var(--text-3);"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search parlour..." class="lux-input" style="width:180px; padding-left: 2rem; font-size: 0.8rem;">
            </div>

            <button type="submit" class="btn-lux-ghost btn-sm" style="padding: 0.4rem 0.8rem;"><i class="bi bi-funnel"></i></button>
            <a href="{{ route('superadmin.subscriptions.index') }}" class="btn-lux-ghost btn-sm faint border-0" style="padding: 0.4rem 0.8rem;">Clear</a>
        </form>
    </div>
</div>

{{-- Subscriptions Table --}}
<div class="card-lux fade-up s3">
    <div class="lux-table-wrapper lux-scroller" style="max-height: 500px; overflow-y: auto;">
        <table class="lux-table">
            <thead>
                <tr>
                    <th>Parlour</th>
                    <th>Plan</th>
                    <th class="d-none d-xl-table-cell">Billing Cycle</th>
                    <th>Status</th>
                    <th class="d-none d-xl-table-cell">Start Date</th>
                    <th>Expiry Date</th>
                    <th>Days Remaining</th>
                    <th>Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                @php $daysLeft = $sub->daysLeft(); @endphp

                <tr class="{{ ($sub->status === 'active' && $daysLeft <= 7) ? 'row-expiring' : '' }}" style="transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                    <td>
                        <div style="font-weight:500;color:var(--text);">{{ $sub->tenant?->name ?? '—' }}</div>
                        <div style="font-size:var(--text-xs);color:var(--text-3);font-family:monospace;">{{ $sub->tenant?->subdomain }}.lumiere.app</div>
                    </td>
                    <td><span class="plan-badge plan-{{ $sub->plan?->slug ?? 'free' }}">{{ $sub->plan?->name ?? '—' }}</span></td>
                    <td class="d-none d-xl-table-cell" style="text-transform:capitalize;">{{ $sub->billing_cycle }}</td>
                    <td>
                        <span class="status-badge {{ $sub->status === 'active' ? 'badge-active' : ($sub->status === 'trial' ? 'badge-trial' : 'badge-suspended') }}">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </td>
                    <td class="d-none d-xl-table-cell faint" style="font-size:var(--text-sm);">{{ $sub->starts_at?->format('d M Y') }}</td>
                    <td style="font-size:var(--text-sm);color:{{ $daysLeft <= 7 ? 'var(--rose)' : 'var(--text-2)' }};">
                        {{ $sub->expires_at?->format('d M Y') }}
                    </td>
                    <td>
                        @if($sub->status === 'active')
                        <span class="status-badge {{ $daysLeft <= 7 ? 'badge-suspended' : ($daysLeft <= 30 ? 'badge-trial' : 'badge-active') }}">
                            {{ $daysLeft }}d
                        </span>
                        @else
                        <span class="faint">—</span>
                        @endif
                    </td>
                    <td style="color:var(--gold);font-weight:500; font-family: var(--ff-display);">₹{{ number_format($sub->amount, 0) }}</td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <form method="POST" action="{{ route('superadmin.subscriptions.renew', $sub) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="billing_cycle" value="{{ $sub->billing_cycle }}">
                                <button type="submit" class="btn-icon-action" style="color: var(--emerald);" title="Renew Subscription">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </form>
                            @if($sub->status !== 'cancelled')
                            <form method="POST" action="{{ route('superadmin.subscriptions.cancel', $sub) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this subscription?');">
                                @csrf
                                <button type="submit" class="btn-icon-action" style="color: var(--rose);" title="Cancel Subscription">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:4rem 2rem;">
                        <i class="bi bi-credit-card faint d-block mb-3" style="font-size: 2rem;"></i>
                        <h4 class="faint" style="font-size: var(--text-sm);">No Subscriptions Found</h4>
                        <p class="muted" style="font-size: var(--text-xs);">No subscription records match your criteria.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($subscriptions->hasPages())
    <div class="lux-pagination-wrapper border-top" style="border-color: rgba(255,255,255,0.05) !important; padding: 1rem 1.5rem;">
        {{ $subscriptions->links() }}
    </div>
    @endif
</div>

{{-- Add Subscription Modal --}}
<x-cards.modal id="addSubModal" title="Assign Subscription">
    <form method="POST" action="{{ route('superadmin.subscriptions.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="lux-label">Parlour (Tenant) *</label>
                <div style="position: relative;">
                    <select name="tenant_id" class="lux-input w-100" style="padding-right: 2rem; color-scheme: dark; background: var(--bg-input); color: var(--text); cursor: pointer; -webkit-appearance: none; appearance: none;" required>
                        <option value="" style="background: var(--bg-card); color: var(--text-3);">Select Parlour…</option>
                        @foreach($tenants as $t)
                        <option value="{{ $t->id }}" style="background: var(--bg-card); color: var(--text);">{{ $t->name }} ({{ $t->subdomain }})</option>
                        @endforeach
                    </select>
                    <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label class="lux-label">Plan *</label>
                <div style="position: relative;">
                    <select name="plan_id" id="planSelect" class="lux-input w-100" style="padding-right: 2rem; color-scheme: dark; background: var(--bg-input); color: var(--text); cursor: pointer; -webkit-appearance: none; appearance: none;" required>
                        <option value="" style="background: var(--bg-card); color: var(--text-3);">Select Plan…</option>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" data-monthly="{{ $plan->price_monthly }}" data-yearly="{{ $plan->price_yearly }}" style="background: var(--bg-card); color: var(--text);">
                            {{ $plan->name }}
                        </option>
                        @endforeach
                    </select>
                    <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-0">
            <div class="col-6">
                <label class="lux-label">Billing Cycle *</label>
                <div style="position: relative;">
                    <select name="billing_cycle" id="billingCycle" class="lux-input w-100" style="padding-right: 2rem; color-scheme: dark; background: var(--bg-input); color: var(--text); cursor: pointer; -webkit-appearance: none; appearance: none;" required>
                        <option value="monthly" style="background: var(--bg-card); color: var(--text);">Monthly</option>
                        <option value="yearly" style="background: var(--bg-card); color: var(--text);">Yearly</option>
                    </select>
                    <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </div>
                </div>
                <div id="planPricePreview" style="margin-top:.75rem; color:var(--gold); font-weight:600; font-size:.85rem; font-family: var(--ff-mono);">
                    Select a plan
                </div>
            </div>
            <div class="col-6">
                <label class="lux-label">Start Date *</label>
                <input type="date" name="starts_at" value="{{ now()->format('Y-m-d') }}" class="lux-input w-100" style="color-scheme: dark;" required>
            </div>
        </div>

        <div class="row g-3 mt-0">
            <div class="col-6">
                <label class="lux-label">Payment Method</label>
                <div style="position: relative;">
                    <select name="payment_method" class="lux-input w-100" style="padding-right: 2rem; color-scheme: dark; background: var(--bg-input); color: var(--text); cursor: pointer; -webkit-appearance: none; appearance: none;">
                        <option value="manual" style="background: var(--bg-card); color: var(--text);">Manual / Cash</option>
                        <option value="upi" style="background: var(--bg-card); color: var(--text);">UPI</option>
                        <option value="bank_transfer" style="background: var(--bg-card); color: var(--text);">Bank Transfer</option>
                        <option value="razorpay" style="background: var(--bg-card); color: var(--text);">Razorpay</option>
                    </select>
                    <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <label class="lux-label">Transaction ID</label>
                <input type="text" name="transaction_id" class="lux-input w-100" placeholder="Optional Ref ID">
            </div>
        </div>

        <div class="mt-3">
            <label class="lux-label">Notes</label>
            <textarea name="notes" class="lux-input w-100" rows="2" placeholder="Optional notes…"></textarea>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1.2rem;">
            <button type="button" class="btn-lux-ghost btn-sm border-0" onclick="LuxModal.close('addSubModal')">Cancel</button>
            <button type="submit" class="btn-lux-gold btn-sm"><i class="bi bi-check-lg me-1"></i> Assign</button>
        </div>
    </form>
</x-cards.modal>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const planSelect = document.getElementById('planSelect');
        const billingCycle = document.getElementById('billingCycle');
        const preview = document.getElementById('planPricePreview');

        function updatePrice() {
            if (!planSelect || !billingCycle || !preview) return;

            const selected = planSelect.options[planSelect.selectedIndex];

            if (!selected.value) {
                preview.innerHTML = 'Select a plan';
                return;
            }

            const monthly = selected.dataset.monthly;
            const yearly = selected.dataset.yearly;

            if (billingCycle.value === 'yearly') {
                preview.innerHTML = 'Price: ₹' + parseInt(yearly).toLocaleString() + ' / year';
            } else {
                preview.innerHTML = 'Price: ₹' + parseInt(monthly).toLocaleString() + ' / month';
            }
        }

        planSelect.addEventListener('change', updatePrice);
        billingCycle.addEventListener('change', updatePrice);
    });

</script>
@endpush
