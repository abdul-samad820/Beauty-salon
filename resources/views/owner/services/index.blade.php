@extends('layouts.owner')

@section('title', 'Services Management')
@section('page-title', 'Services Register')
@section('breadcrumb', 'Manage / Services')

@section('topbar-actions')
<button class="btn-lux-gold btn-sm" onclick="openAddServiceModal()">
    <i class="bi bi-plus-lg" aria-hidden="true"></i> Add Service
</button>
@endsection

@section('content')

<div class="mb-4 fade-up s1">
    <x-cards.stat-row :stats="[
        ['label' => 'Total Services Registry', 'value' => $stats['total'],    'color' => 'var(--gold)'],
        ['label' => 'Active Treatment Nodes',  'value' => $stats['active'],  'color' => 'var(--emerald)'],
        ['label' => 'Inactive Service Nodes',  'value' => $stats['inactive'],'color' => 'var(--rose)'],
    ]" />
</div>

<div class="card-lux p-3 mb-4 fade-up s2">
    <form method="GET" action="{{ route('owner.services.index') }}" id="servicesFilterForm" role="search" class="row g-3 align-items-center">

        <div class="col-12 col-md-5 col-lg-4 position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y faint" style="left: 1.2rem; font-size: 0.85rem;"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search services by name..." class="lux-input" style="padding-left: 2.4rem;" aria-label="Search services" />
        </div>

        <div class="col-12 col-md-4 col-lg-3 position-relative">
            <select name="category" class="lux-input" style="padding-right: 2.5rem; color-scheme: dark; background-color: var(--bg-input); color: var(--text);" aria-label="Filter by category">
                <option value="" style="background: var(--bg-card); color: var(--text-3);">All Categories</option>
                @foreach(['hair'=>'Hair Treatments','skin'=>'Skin Therapy','nail'=>'Nail Care','bridal'=>'Bridal packages','massage'=>'Massage Spa','other'=>'Other Treatments'] as $v => $l)
                <option value="{{ $v }}" style="background: var(--bg-card); color: var(--text);" {{ request('category') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
            </div>
        </div>

        <div class="col-12 col-md-3 col-lg-5 d-flex gap-2 justify-content-md-end">
            <button type="submit" class="btn-lux-ghost btn-sm border-0">Apply</button>
            <a href="{{ route('owner.services.index') }}" class="btn-lux-ghost btn-sm faint border-0">Reset</a>
        </div>
    </form>
</div>

<div class="row g-3 fade-up s3">
    @forelse($services as $svc)
    <div class="col-12 col-md-6 col-xl-4">
        <article class="card-lux p-4 h-100 d-flex flex-column" style="position: relative; border-left: 3px solid transparent; @if($svc->category === 'hair') border-left-color: var(--amber); @elseif($svc->category === 'skin') border-left-color: var(--purple); @elseif($svc->category === 'nail') border-left-color: var(--teal-light); @elseif($svc->category === 'bridal') border-left-color: var(--rose); @elseif($svc->category === 'massage') border-left-color: var(--emerald); @else border-left-color: var(--text-3); @endif">

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <span class="plan-badge" style="font-size: 0.6rem; letter-spacing: 0.05em; padding: 0.2rem 0.5rem; text-transform: uppercase;
                    @if($svc->category === 'hair') background: var(--amber-dim); color: var(--amber);
                    @elseif($svc->category === 'skin') background: var(--purple-dim); color: var(--purple);
                    @elseif($svc->category === 'nail') background: var(--teal-dim); color: var(--teal-light);
                    @elseif($svc->category === 'bridal') background: var(--rose-dim); color: var(--rose);
                    @elseif($svc->category === 'massage') background: var(--emerald-dim); color: var(--emerald);
                    @else background: rgba(255,255,255,0.05); color: var(--text-3);
                    @endif">
                    {{ $svc->category }}
                </span>

                @unless($svc->is_active)
                <span class="status-badge badge-suspended" style="font-size: 0.6rem; padding: 0.2rem 0.5rem;">Inactive</span>
                @endunless
            </div>

            <div style="flex: 1; min-width: 0; margin-bottom: 1rem;">
                <h4 class="serif truncate" style="font-size: 1.1rem; font-weight: 500; color: var(--text); margin-bottom: 0.3rem;">
                    {{ $svc->name }}
                </h4>
                @if($svc->description)
                <p style="font-size: 0.75rem; color: var(--text-3); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 0; line-height: 1.4;">
                    {{ $svc->description }}
                </p>
                @endif
            </div>

            <div style="display: flex; align-items: flex-end; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 1rem; margin-top: auto;">
                <div>
                    <div style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-3); margin-bottom: 0.2rem;">Platform Rate</div>
                    <div class="serif" style="font-size: 1.4rem; font-weight: 400; color: var(--gold); line-height: 1;">₹{{ number_format($svc->price, 0) }}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-3); margin-bottom: 0.2rem;">Time Span</div>
                    <div style="font-size: 0.8rem; font-weight: 500; color: var(--text-2); display: flex; align-items: center; justify-content: flex-end; gap: 0.3rem;">
                        <i class="bi bi-clock faint"></i> {{ $svc->duration_minutes }} min
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.03);">
                <button type="button" class="btn-icon-action" style="font-size: 0.85rem;" title="Edit Service" aria-label="Edit service" data-svc-id="{{ $svc->id }}" data-svc-name="{{ $svc->name }}" data-svc-category="{{ $svc->category }}" data-svc-duration="{{ $svc->duration_minutes }}" data-svc-price="{{ $svc->price }}" data-svc-description="{{ $svc->description ?? '' }}" onclick="handleEditServiceModalTrigger(this)">
                    <i class="bi bi-pencil" aria-hidden="true"></i>
                </button>
                <form method="POST" action="{{ route('owner.services.destroy', $svc->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this service?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon-action" title="Delete Service" aria-label="Delete service" style="font-size: 0.85rem; color: var(--rose);">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                </form>
            </div>

        </article>
    </div>
    @empty
    <div class="col-12">
        <x-empty-state icon="bi-scissors" title="No Services Registered" text="Database configuration logs have returned empty fields. Build package maps to active client operations." />
    </div>
    @endforelse
</div>
@if($services->hasPages())
<div class="mt-4 d-flex justify-content-center">
    {{ $services->links() }}
</div>
@endif

<x-cards.modal id="addServiceModal" title="Add New Service Unit">
    <form method="POST" id="svcForm" action="{{ route('owner.services.store') }}">
        @csrf
        <span id="methodField"></span>

        <div class="row g-3">
            <div class="col-12">
                <x-forms.input name="name" id="name" label="Service Variant Name *" :required="true" />
            </div>

            <div class="col-12 col-md-6">
                <div style="position: relative;">
                    <label class="lux-label" for="category">Structural Category *</label>
                    <select name="category" id="category" class="lux-input" style="padding-right: 2.5rem; color-scheme: dark; background-color: var(--bg-input); color: var(--text);" required>
                        <option value="" style="background: var(--bg-card); color: var(--text-3);">Choose Category Module...</option>
                        @foreach(['hair'=>'Hair','skin'=>'Skin','nail'=>'Nail','bridal'=>'Bridal','massage'=>'Massage','other'=>'Other'] as $val => $label)
                        <option value="{{ $val }}" style="background: var(--bg-card); color: var(--text);">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div style="position: absolute; right: 1rem; top: 70%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <x-forms.input name="duration_minutes" id="duration_minutes" label="Duration Step (min) *" type="number" min="15" step="15" :required="true" />
            </div>

            <div class="col-12">
                <x-forms.input name="price" id="price" label="Platform Rate Amount (₹) *" type="number" min="0" step="1" :required="true" />
            </div>

            <div class="col-12">
                <x-forms.textarea name="description" id="description" label="Service Structural Description" placeholder="Describe aesthetic process step profiles..." rows="3" />
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border); padding-top: 1rem;">
            <button type="button" onclick="LuxModal.close('addServiceModal')" class="btn-lux-ghost btn-sm border-0">Cancel</button>
            <button type="submit" class="btn-lux-gold btn-sm" data-loading-text="Saving Service...">
                Save Service Variant
            </button>
        </div>
    </form>
</x-cards.modal>

@endsection

@push('scripts')
<script>
    const SERVICE_STORE_BASE_URL = "{{ route('owner.services.store') }}";

    function openAddServiceModal() {
        const form = document.getElementById('svcForm');
        form.reset();
        form.action = SERVICE_STORE_BASE_URL;
        document.getElementById('methodField').innerHTML = '';
        document.querySelector('#addServiceModal .lux-modal-title').textContent = 'Add New Service Unit';
        LuxModal.open('addServiceModal');
    }

    function handleEditServiceModalTrigger(buttonElement) {
        const dataset = buttonElement.dataset;

        document.querySelector('#addServiceModal .lux-modal-title').textContent = 'Edit Service Unit Configuration';
        document.getElementById('svcForm').action = `/owner/services/${dataset.svcId}`;
        document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        // Binding safe internal layout mappings values to design schemas inputs
        document.getElementById('name').value = dataset.svcName;
        document.getElementById('category').value = dataset.svcCategory;
        document.getElementById('duration_minutes').value = dataset.svcDuration;
        document.getElementById('price').value = dataset.svcPrice;
        document.getElementById('description').value = dataset.svcDescription;

        LuxModal.open('addServiceModal');
    }

</script>
@endpush
