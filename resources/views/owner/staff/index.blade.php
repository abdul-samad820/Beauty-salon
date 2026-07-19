@extends('layouts.owner')

@section('title', 'Staff Management')
@section('page-title', 'Staff Matrix')
@section('breadcrumb', 'Manage / Staff')

@section('topbar-actions')
<button class="btn-lux-gold btn-sm" onclick="openAddStaffModal()">
    <i class="bi bi-plus-lg" aria-hidden="true"></i> Add Staff Member
</button>
@endsection

@section('content')

<div class="mb-4 fade-up s1">
    <x-cards.stat-row :stats="[
        ['label' => 'Total Registered Staff',    'value' => $stats['total'],                    'color' => 'var(--gold)'],
        ['label' => 'Available Artists',         'value' => $stats['available'],                'color' => 'var(--emerald)'],
        ['label' => 'Average Commission Tier',   'value' => round($stats['avg_commission']).'%','color' => 'var(--purple)'],
    ]" />
</div>

<div class="row g-3 fade-up s2">
    @forelse($staff as $s)
    <div class="col-12 col-md-6 col-xl-4">
        <article class="card-lux p-4 h-100 d-flex flex-column" style="position: relative;">

            <div style="display: flex; align-items: flex-start; gap: 1rem;">
                @if($s->user?->profile_photo)
               <img src="{{ cloudinary()->image($s->user->profile_photo)->toUrl() }}" alt="{{ $s->user->name }}" style="width: 48px; height: 48px; flex-shrink: 0; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-2);" />
                @else
                <div style="width: 48px; height: 48px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-2); font-size: 1rem; font-weight: 600; letter-spacing: 1px; color: var(--text-2);" aria-hidden="true">
                    {{ strtoupper(substr($s->user?->name ?? 'S', 0, 2)) }}
                </div>
                @endif

                <div style="flex: 1; min-width: 0;">
                    <h4 class="serif truncate" style="font-size: 1.1rem; font-weight: 500; color: var(--text); margin-bottom: 0;">
                        {{ $s->user?->name }}
                    </h4>
                    <p class="truncate" style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem; margin-bottom: 0;">
                        {{ $s->user?->email }}
                    </p>

                    <div style="margin-top: 0.5rem;">
                        <span class="status-badge {{ $s->is_available ? 'badge-active' : 'badge-suspended' }}" style="font-size: 0.6rem; padding: 0.2rem 0.5rem;">
                            @if($s->is_available) <span class="live-dot" style="margin-right: 0.2rem;"></span> @endif
                            {{ $s->is_available ? 'Available' : 'On Leave' }}
                        </span>
                    </div>
                </div>
            </div>

            @if($s->specializations)
            <div style="margin-top: 1.25rem; display: flex; flex-wrap: wrap; gap: 0.4rem; min-height: 24px;">
                @foreach($s->specializations as $sp)
                <span style="display: inline-flex; align-items: center; border-radius: var(--r-md); background: rgba(255,255,255,0.03); border: 1px solid var(--border); padding: 0.2rem 0.6rem; font-size: 0.65rem; font-weight: 500; color: var(--text-2);">
                    {{ $sp }}
                </span>
                @endforeach
            </div>
            @endif

            <div style="margin-top: auto; padding-top: 1.25rem;">
                <div style="border-radius: var(--r-md); border: 1px solid rgba(255,255,255,0.03); background: rgba(255,255,255,0.02); padding: 0.75rem 1rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500; color: var(--text-3);">Payout Structure</span>
                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-weight: 600; color: var(--text);">
                            <i class="bi bi-percent" style="color: var(--purple);"></i>
                            {{ $s->commission_percent }}% flat (fallback)
                        </span>
                    </div>

                    {{-- Tier list --}}
                    @if($s->commissionTiers->isNotEmpty())
                    <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.3rem;">
                        @foreach($s->commissionTiers as $tier)
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; background: rgba(255,255,255,0.02); border-radius: 6px; padding: 0.3rem 0.5rem;">
                            <span style="color: var(--text-3);">
                                ₹{{ number_format($tier->min_revenue, 0) }}
                                – {{ $tier->max_revenue ? '₹' . number_format($tier->max_revenue, 0) : '∞' }}
                            </span>
                            <span style="color: var(--gold); font-weight: 600;">{{ $tier->commission_percent }}%</span>
                            <form method="POST" action="{{ route('owner.staff.tiers.destroy', $tier->id) }}" class="d-inline" onsubmit="return confirm('Remove this tier?');">
                                @csrf @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: var(--rose); cursor: pointer; font-size: 0.75rem; padding: 0;" title="Remove tier">
                                    <i class="bi bi-x"></i>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p style="font-size: 0.7rem; color: var(--text-3); margin: 0.4rem 0 0; font-style: italic;">No tiers set — flat rate applies.</p>
                    @endif

                    {{-- Add tier mini form --}}
                    <details style="margin-top: 0.6rem;">
                        <summary style="font-size: 0.72rem; color: var(--teal-light); cursor: pointer; list-style: none; display: flex; align-items: center; gap: 0.3rem;">
                            <i class="bi bi-plus-circle"></i> Add revenue tier
                        </summary>
                        <form method="POST" action="{{ route('owner.staff.tiers.store', $s->id) }}" style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.4rem;">
                            @csrf
                            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                <input type="number" name="min_revenue" class="tier-input" placeholder="Min ₹" min="0" step="1000" style="flex: 1 1 100px; min-width: 100px; font-size: 0.72rem; padding: 0.3rem 0.5rem; border-radius: 4px; background: var(--bg-input); border: 1px solid var(--border); color: var(--text);" required />
                                <input type="number" name="max_revenue" class="tier-input" placeholder="Max ₹ (∞)" min="0" step="1000" style="flex: 1 1 100px; min-width: 100px; font-size: 0.72rem; padding: 0.3rem 0.5rem; border-radius: 4px; background: var(--bg-input); border: 1px solid var(--border); color: var(--text);" />
                            </div>
                            <div style="display: flex; gap: 0.4rem; align-items: center;">
                                <input type="number" name="commission_percent" class="tier-input" placeholder="Rate %" min="0" max="50" step="0.5" style="flex: 1; font-size: 0.72rem; padding: 0.3rem 0.5rem; border-radius: 4px; background: var(--bg-input); border: 1px solid var(--border); color: var(--text);" required />
                                <button type="submit" style="padding: 0.3rem 0.7rem; border-radius: 4px; background: var(--gold); color: #1a1400; font-size: 0.72rem; font-weight: 600; border: none; cursor: pointer;">Add</button>
                            </div>
                        </form>
                    </details>
                </div>

                <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                    <button type="button" class="btn-icon-action" style="font-size: 0.85rem;" title="Edit Staff" aria-label="Edit staff member" data-staff-id="{{ $s->id }}" data-staff-name="{{ $s->user?->name }}" data-staff-phone="{{ $s->user?->phone }}" data-staff-commission="{{ $s->commission_percent }}" data-staff-specializations="{{ implode(',', $s->specializations ?? []) }}" onclick="handleEditStaffModalTrigger(this)">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <form method="POST" action="{{ route('owner.staff.destroy', $s->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this staff member?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-icon-action" title="Remove Staff" aria-label="Remove staff member" style="font-size: 0.85rem; color: var(--rose);">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>

        </article>
    </div>
    @empty
    <div class="col-12">
        <x-empty-state icon="bi-people" title="No Staff Executives Registered" text="Database staff logs have returned empty fields. Registry operations nodes to assign active stylists." />
    </div>
    @endforelse
</div>
@if($staff->hasPages())
<div class="mt-4 d-flex justify-content-center">
    {{ $staff->links() }}
</div>
@endif
<x-cards.modal id="addStaffModal" title="Add Staff Member Account">
    <form method="POST" id="staffForm" action="{{ route('owner.staff.store') }}" enctype="multipart/form-data">
        @csrf
        <span id="staffMethodField"></span>

        <div class="row g-3">
            <div class="col-12">
                <label class="lux-label" for="photo">Profile Photo (shown on your public booking page)</label>
                <input type="file" name="photo" id="photo" accept="image/png,image/jpeg,image/webp" class="lux-input" />
                <small style="color:var(--text-3);font-size:0.72rem;">JPG, PNG or WEBP — max 2MB. Leave blank to keep the current photo.</small>
            </div>
            <div id="addOnlyFields" class="col-12 row g-3 m-0 p-0">
                <div class="col-12">
                    <x-forms.input name="email" id="email" label="Professional Login Email Address *" type="email" :required="true" />
                </div>
                <div class="col-12 col-md-6">
                    <x-forms.input name="password" id="password" label="Portal Access Password *" type="password" :required="true" />
                </div>
                <div class="col-12 col-md-6">
                    <x-forms.input name="password_confirmation" id="password_confirmation" label="Confirm Password Mapping *" type="password" />
                </div>
                <div class="col-12 my-2">
                    <div style="border-top: 1px solid var(--border);"></div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <x-forms.input name="name" id="name" label="Full Identity Name *" :required="true" />
            </div>
            <div class="col-12 col-md-6">
                <x-forms.input name="phone" id="phone" label="Primary Phone Connection *" :required="true" />
            </div>

            <div class="col-12">
                <x-forms.input name="commission_percent" id="commission_percent" label="Commission Payout Share (%)" type="number" min="0" max="100" step="0.5" />
            </div>
            <div class="col-12">
                <x-forms.input name="specializations" id="specializations" label="Specialization Tags Profile (comma-separated)" placeholder="e.g. Hair Cut, Bridal Makeup, Nail Artistry" />
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border); padding-top: 1rem;">
            <button type="button" onclick="LuxModal.close('addStaffModal')" class="btn-lux-ghost btn-sm border-0">Cancel</button>
            <button type="submit" class="btn-lux-gold btn-sm" data-loading-text="Saving Staff...">
                Confirm Save Account
            </button>
        </div>
    </form>
</x-cards.modal>

@endsection

@push('styles')
<style>
    /* FIXED: native number-input spinner arrows were eating into the
       already-tight width of the Min/Max revenue-tier inputs on mobile,
       clipping the placeholder text. Remove them for a clean compact look. */
    .tier-input::-webkit-outer-spin-button,
    .tier-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .tier-input[type="number"] {
        -moz-appearance: textfield;
        appearance: textfield;
    }
</style>
@endpush

@push('scripts')
<script>
    const STAFF_STORE_BASE_URL = "{{ route('owner.staff.store') }}";

    function openAddStaffModal() {
        const form = document.getElementById('staffForm');
        form.reset();
        form.action = STAFF_STORE_BASE_URL;
        document.getElementById('staffMethodField').innerHTML = '';

        const addFields = document.getElementById('addOnlyFields');
        if (addFields) {
            addFields.style.display = 'flex';
        }

        // Ensure nested fields inside component follow explicit configurations rules
        document.getElementById('email').required = true;
        document.getElementById('password').required = true;

        document.querySelector('#addStaffModal .lux-modal-title').textContent = 'Add Staff Member Account';
        LuxModal.open('addStaffModal');
    }

    function handleEditStaffModalTrigger(buttonElement) {
        const dataset = buttonElement.dataset;
        const form = document.getElementById('staffForm');

        document.querySelector('#addStaffModal .lux-modal-title').textContent = 'Edit Staff Member Attributes';
        form.action = `/owner/staff/${dataset.staffId}`;
        document.getElementById('staffMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        // Safety Hidden Form Elements Toggle Layers
        const addFields = document.getElementById('addOnlyFields');
        if (addFields) {
            addFields.style.display = 'none';
        }
        document.getElementById('email').required = false;
        document.getElementById('password').required = false;

        // Mappings
        document.getElementById('name').value = dataset.staffName;
        document.getElementById('phone').value = dataset.staffPhone;
        document.getElementById('commission_percent').value = dataset.staffCommission;
        document.getElementById('specializations').value = dataset.staffSpecializations;

        LuxModal.open('addStaffModal');
    }

</script>
@endpush