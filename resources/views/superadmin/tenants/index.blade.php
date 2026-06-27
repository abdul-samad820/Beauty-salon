@extends('layouts.superadmin')

@section('title', 'Platform Core Tenancy')
@section('page-title', 'Global Tenants Register')
@section('breadcrumb', 'Platform / Tenants Registry')

@section('topbar-actions')
<button class="btn-lux-gold btn-sm" onclick="LuxModalManager.openCreationRegistry()">
    <i class="bi bi-plus-lg" aria-hidden="true"></i> Provision New Tenant Node
</button>
@endsection

@push('styles')
<style>
    /* Premium Scrollbar */
    .lux-scroller::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .lux-scroller::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
        border-radius: 10px;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(201, 169, 110, 0.3);
        border-radius: 10px;
        transition: background 0.3s;
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
        box-shadow: 0 1px 0 var(--border);
        /* Ek halki line table header ke neeche */
    }

</style>
@endpush
@section('content')


{{-- KPI Stats Matrix --}}
<div class="row g-3 mb-4 fade-up">
    @php
    $statItems = [
    ['label' => 'Total Subscribed Nodes', 'value' => $stats['total'] ?? 0],
    ['label' => 'Active Core Workspaces', 'value' => $stats['active'] ?? 0],
    ['label' => 'Trial Evaluation Phases', 'value' => $stats['trial'] ?? 0],
    ['label' => 'Suspended Deficit States', 'value' => $stats['suspended'] ?? 0],
    ];
    @endphp
    @foreach($statItems as $indexKey => $dataMetric)
    <div class="col-6 col-lg-3 fade-up s{{ $indexKey + 1 }}">
        <div class="card-lux kpi-pad glow-hover h-100 d-flex flex-column justify-content-center @if($indexKey === 0) gold-border @endif">
            <div class="kpi-label">{{ $dataMetric['label'] }}</div>
            <div class="kpi-value mb-0">{{ $dataMetric['value'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card-lux mb-4 fade-up s2" style="padding: 1rem;">
    <form method="GET" action="{{ route('superadmin.tenants.index') }}" id="tenantFilterForm" class="row g-3 align-items-center" role="search">
        <div class="col-12 col-md-5 position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y faint" style="left: 1.2rem; font-size: 0.85rem;"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search tenant node, workspace owner..." class="lux-input" style="padding-left: 2.4rem;" aria-label="Search records" />
        </div>

        <div class="col-6 col-md-2">
            <div style="position: relative;">
                {{-- FIX: Added color-scheme: dark and option backgrounds --}}
                <select name="status" class="lux-input" aria-label="Filter status" style="padding-right: 2rem; color-scheme: dark; background: var(--bg-input); color: var(--text);">
                    <option value="all" style="background: var(--bg-card); color: var(--text);" {{ request('status','all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'active' ? 'selected' : '' }}>Active Node</option>
                    <option value="inactive" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive State</option>
                    <option value="suspended" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="trial" style="background: var(--bg-card); color: var(--text);" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial Phase</option>
                </select>
                <div style="position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                    <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div style="position: relative;">
                {{-- FIX: Added color-scheme: dark and option backgrounds --}}
                <select name="plan" class="lux-input" aria-label="Filter plans" style="padding-right: 2rem; color-scheme: dark; background: var(--bg-input); color: var(--text);">
                    <option value="all" style="background: var(--bg-card); color: var(--text);" {{ request('plan','all') === 'all' ? 'selected' : '' }}>All Tiers</option>
                    <option value="free" style="background: var(--bg-card); color: var(--text);" {{ request('plan') === 'free' ? 'selected' : '' }}>Free</option>
                    <option value="basic" style="background: var(--bg-card); color: var(--text);" {{ request('plan') === 'basic' ? 'selected' : '' }}>Basic</option>
                    <option value="premium" style="background: var(--bg-card); color: var(--text);" {{ request('plan') === 'premium' ? 'selected' : '' }}>Premium</option>
                </select>
                <div style="position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                    <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end">
            <button type="submit" class="btn-lux-ghost btn-sm">Apply Filters</button>
            <a href="{{ route('superadmin.tenants.index') }}" class="btn-lux-ghost btn-sm faint border-0">Clear</a>
        </div>
    </form>
</div>

{{-- Data Table --}}
<div class="card-lux fade-up s3">
    <div class="lux-table-wrapper lux-scroller" style="max-height: 450px; overflow-y: auto; overflow-x: auto;">
        <table class="lux-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Node ID</th>
                    <th>Target Domain Scope</th>
                    <th>Account Owner</th>
                    <th>Contact Log</th>
                    <th>Tier Plan</th>
                    <th>Lifecycle State</th>
                    <th>Provision Date</th>
                    <th class="text-end">Control</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $t)
                <tr>
                    <td class="faint" style="font-family: monospace;">#{{ $t->id }}</td>
                    <td>
                        <div style="font-weight: 500; color: var(--text);">{{ $t->name }}</div>
                        <div class="faint" style="font-size: var(--text-xs); font-family: monospace; margin-top: 2px;">{{ $t->subdomain }}.{{ config('app.domain','example.com') }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 400;">{{ $t->owner?->name ?? 'Detached Identity' }}</div>
                    </td>
                    <td>
                        <div>{{ $t->email }}</div>
                        <div class="faint" style="font-size: var(--text-xs); margin-top: 2px;">{{ $t->phone }}</div>
                    </td>
                    <td>
                        <span class="plan-badge plan-{{ strtolower($t->plan) }}">{{ $t->plan }}</span>
                    </td>
                    <td>
                        <span class="status-badge badge-{{ strtolower($t->status) }}">
                            @if($t->status === 'active') <span class="live-dot"></span> @endif
                            {{ ucfirst($t->status) }}
                        </span>
                    </td>
                    <td class="faint" style="font-size: var(--text-sm);">
                        {{ $t->created_at->format('d M Y') }}
                    </td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('superadmin.tenants.show', $t->id) }}" class="btn-icon-action" title="View Metrics">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button type="button" class="btn-icon-action" title="Modify Context" onclick="LuxModalManager.openEditRegistry({{ $t->id }}, '{{ addslashes($t->name) }}', '{{ $t->status }}', '{{ $t->plan }}', '{{ addslashes($t->subdomain) }}', '{{ addslashes($t->phone ?? '') }}', '{{ addslashes($t->address ?? '') }}')">
                                <i class="bi bi-pencil"></i>

                                @if($t->status !== 'suspended')
                                <form method="POST" action="{{ route('superadmin.tenants.status', $t->id) }}" class="d-inline">
                                    @method('PATCH')
                                    @csrf
                                    <input type="hidden" name="status" value="suspended">
                                    <button type="submit" class="btn-icon-action" title="Suspend System" style="color: var(--rose);">
                                        <i class="bi bi-slash-circle"></i>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('superadmin.tenants.status', $t->id) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn-icon-action" title="Revoke Suspension" style="color: var(--emerald);">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 4rem 2rem;">
                        <i class="bi bi-buildings faint d-block mb-3" style="font-size: 2rem;"></i>
                        <h4 class="faint" style="font-size: var(--text-sm);">No multi-tenant system slices generated.</h4>
                        <p class="muted" style="font-size: var(--text-xs);">Click top operations boundary to allocate cloud partitions.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
    <div class="lux-pagination-wrapper border-top" style="border-color: var(--border) !important; padding: 1rem 1.5rem;">
        <x-tables.pagination :paginator="$tenants" />
    </div>
    @endif
</div>

{{-- Creation / Edit Modal --}}
<x-cards.modal id="addTenantModal" title="Tenant Environment Details">
    <form method="POST" id="tenantForm" action="{{ route('superadmin.tenants.store') }}">
        @csrf
        <span id="tenantMethodField"></span>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <x-forms.input name="business_name" label="Salon / Parlour Name *" :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.input name="subdomain" label="Subdomain *" :required="true" placeholder="e.g. meesu-cosmetic" />
            </div>
            <div class="col-12 col-md-6 data-creation-field">
                <x-forms.input name="owner_name" label="Owner Name *" :required="true" />
            </div>
            <div class="col-12 col-md-6 data-creation-field">
                <x-forms.input name="owner_email" label="Owner Email *" type="email" :required="true" placeholder="owner@example.com" />
            </div>
            <div class="col-12 col-md-6 data-creation-field">
                <x-forms.input name="owner_password" label="Password *" type="password" :required="true" />
            </div>
            <div class="col-12 col-md-6 data-creation-field">
                <x-forms.input name="owner_password_confirmation" label="Confirm Password *" type="password" :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.input name="phone" label="Phone Number" placeholder="9634361073" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.select name="plan" label="Subscription Plan *" :options="['free' => 'Free', 'basic' => 'Basic', 'premium' => 'Premium']" selected="free" />
            </div>
            <div class="col-12">
                <x-forms.textarea name="address" label="Physical Address" />
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: var(--border) !important;">
            <button type="button" onclick="LuxModal.close('addTenantModal')" class="btn-lux-ghost btn-sm border-0">Abrupt Operations</button>
            <button type="submit" class="btn-lux-gold btn-sm">
                <i class="bi bi-check-lg" aria-hidden="true"></i> Commit Partition
            </button>
        </div>
    </form>
</x-cards.modal>

@endsection

@push('scripts')
<script>
    const LuxModalManager = {
        STORE_URL: "{{ route('superadmin.tenants.store') }}",

        openCreationRegistry: function() {
            const modalTitle = document.querySelector('#addTenantModal .lux-modal-title');
            if (modalTitle) modalTitle.textContent = 'Provision New Tenant Partition';

            const formEl = document.getElementById('tenantForm');
            if (formEl) {
                formEl.reset();
                formEl.action = this.STORE_URL;
            }

            const methodPort = document.getElementById('tenantMethodField');
            if (methodPort) methodPort.innerHTML = '';

            document.querySelectorAll('.data-creation-field').forEach(nodeBlock => {
                nodeBlock.style.display = 'block';
                const targetInput = nodeBlock.querySelector('input');
                if (targetInput) targetInput.setAttribute('required', 'true');
            });

            LuxModal.open('addTenantModal');
        },

        openEditRegistry: function(nodeId, nodeName, nodeStatus, nodePlan, nodeSubdomain, nodePhone, nodeAddress) {
            const modalTitle = document.querySelector('#addTenantModal .lux-modal-title');
            if (modalTitle) modalTitle.textContent = 'Modify Cloud Deployment Matrix';

            const formEl = document.getElementById('tenantForm');
            if (formEl) formEl.action = `/superadmin/tenants/${nodeId}`;

            const methodPort = document.getElementById('tenantMethodField');
            if (methodPort) methodPort.innerHTML = '<input type="hidden" name="_method" value="PUT">';

            const q = (name) => formEl.querySelector(`[name="${name}"]`);
            if (q('business_name')) q('business_name').value = nodeName;
            if (q('subdomain')) q('subdomain').value = nodeSubdomain;
            if (q('phone')) q('phone').value = nodePhone;
            if (q('plan')) q('plan').value = nodePlan;


            const addrEl = formEl.querySelector('[name="address"]');
            if (addrEl) addrEl.value = nodeAddress;


            document.querySelectorAll('.data-creation-field').forEach(nodeBlock => {
                nodeBlock.style.display = 'none';
                const targetInput = nodeBlock.querySelector('input');
                if (targetInput) targetInput.removeAttribute('required');
            });

            LuxModal.open('addTenantModal');
        }
    };

</script>
@endpush
