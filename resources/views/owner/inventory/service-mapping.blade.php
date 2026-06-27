@extends('layouts.owner')

@section('title', 'Service → Product Mapping')
@section('page-title', 'Service Mapping Framework')
@section('page-sub', 'Define which products are consumed per service automation cycle.')
@push('styles')
<style>
    /* Global Premium Scroller */
    .lux-scroller::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .lux-scroller::-webkit-scrollbar-thumb {
        background: rgba(201, 169, 110, 0.3);
        border-radius: 10px;
        transition: background 0.3s;
    }

    .lux-scroller::-webkit-scrollbar-thumb:hover {
        background: var(--gold);
    }

    /* Table Sticky Header */
    .lux-table thead th {
        position: sticky;
        top: 0;
        background: var(--bg-card);
        z-index: 10;
        border-bottom: 1px solid var(--border);
    }

</style>
@endpush

@section('content')

<!-- System Information Alert Box -->
<div class="card-lux mb-4 fade-up s1" style="display: flex; align-items: flex-start; gap: 1rem; border-left: 3px solid var(--teal-light); background: rgba(58, 158, 141, 0.05); padding: 1rem 1.5rem;" role="status">
    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--teal-dim); color: var(--teal-light); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <i class="bi bi-info-circle-fill"></i>
    </div>
    <div style="flex: 1; min-width: 0;">
        <h4 style="font-size: 0.85rem; font-weight: 600; color: var(--text); margin-bottom: 0.3rem;">Inventory Automation Network</h4>
        <p style="font-size: 0.75rem; color: var(--text-3); margin-bottom: 0; line-height: 1.5;">
            Jab koi appointment treatment queue status <span style="font-weight: 600; color: var(--emerald);">Completed</span> par phase shift karta hai, tab mapped material profiles stock logs volume se automatically clear deduct ho jate hain. Define precision material deployment mappings (e.g. Hair Spa execution triggers 50ml Shampoo allocation metrics).
        </p>
    </div>
</div>

<div class="row g-4">

    <!-- Mapping Creation Form Panel -->
    <div class="col-12 col-lg-4 fade-up s2">
        <div class="card-lux p-4" style="position: sticky; top: 1.5rem;">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 class="serif" style="font-size: 1.2rem; color: var(--gold); margin-bottom: 0;">Create Mapping Profile</h3>
                <p style="font-size: 0.7rem; color: var(--text-3); margin-bottom: 0; margin-top: 0.2rem;">Establish dynamic raw utilization links.</p>
            </div>

            <form method="POST" action="{{ route('owner.inventory.service-mapping.store') }}">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label class="lux-label" for="serviceSelect">Target Service Block *</label>
                    <div style="position: relative;">
                        <select name="service_id" id="serviceSelect" class="lux-input @error('service_id') border-rose @enderror" style="color-scheme: dark; padding-right: 2.5rem; background-color: var(--bg-input); color: var(--text); cursor: pointer;" required>
                            <option value="" style="background: var(--bg-card); color: var(--text-3);">Choose service variant...</option>
                            @foreach($services as $service)
                            <option value="{{ $service->id }}" style="background: var(--bg-card); color: var(--text);" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                            @endforeach
                        </select>
                        <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                            <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                    @error('service_id')
                    <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 1rem;">
                    <label class="lux-label">Consumed Product SKU *</label>
                    <div style="position: relative;">
                        <select name="product_id" class="lux-input @error('product_id') border-rose @enderror" style="color-scheme: dark; padding-right: 2.5rem; background-color: var(--bg-input); color: var(--text); cursor: pointer;" required>
                            <option value="" style="background: var(--bg-card); color: var(--text-3);">Choose item registration variant...</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" style="background: var(--bg-card); color: var(--text);" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (Stock: {{ $product->quantity }})
                            </option>
                            @endforeach
                        </select>
                        <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                            <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                    @error('product_id')
                    <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="row g-2" style="margin-bottom: 1.5rem;">
                    <div class="col-7">
                        <label class="lux-label">Volume Size Used *</label>
                        <input type="number" name="quantity_used" value="{{ old('quantity_used', 1) }}" class="lux-input @error('quantity_used') border-rose @enderror" step="0.01" min="0.01" required>
                        @error('quantity_used')
                        <p style="margin-top: 0.4rem; font-size: 0.75rem; color: var(--rose); display: flex; align-items: center; gap: 0.3rem;"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-5">
                        <label class="lux-label">Metric Unit</label>
                        <input type="text" name="unit" value="{{ old('unit') }}" class="lux-input" placeholder="e.g. ml, gm, pcs">
                    </div>
                </div>

                <button type="submit" class="btn-lux-gold" style="width: 100%; justify-content: center;">
                    <i class="bi bi-plus-lg"></i> Append Mapping Rules
                </button>
            </form>
        </div>
    </div>

    <!-- Active Mappings Registry List -->
    <div class="col-12 col-lg-8 fade-up s3">
        <div style="margin-bottom: 1.5rem;">
            <h3 class="serif" style="font-size: 1.2rem; color: var(--text); margin-bottom: 0;">Active System Mappings Matrix</h3>
            <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.2rem; margin-bottom: 0;">Chronological association breakdown per layout configuration rules.</p>
        </div>

        @if($services->isEmpty())
        <div style="text-align: center; padding: 4rem 1rem; border: 1px dashed var(--border); border-radius: var(--r-lg); background: rgba(255,255,255,0.01);">
            <i class="bi bi-scissors faint d-block mb-3" style="font-size: 2rem;"></i>
            <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--text);">No service items generated inside active tenant</h4>
            <p style="font-size: 0.75rem; color: var(--text-3); margin-top: 0.3rem;">Navigate to primary services panels registry to initialize options.</p>
        </div>
        @else

        @foreach($services as $service)
        @php $serviceMappings = $mappings->get($service->id, collect()); @endphp
        <div class="card-lux p-0 mb-4" style="overflow: hidden;">

            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.2);">
                <div style="min-width: 0; display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 0.95rem; font-weight: 600; color: var(--text);">{{ $service->name }}</span>
                    <span class="plan-badge" style="background: rgba(255,255,255,0.05); color: var(--text-3); font-size: 0.6rem; padding: 0.15rem 0.4rem;">{{ $service->category }}</span>
                </div>
                <div>
                    @if($serviceMappings->isEmpty())
                    <span class="status-badge" style="background: rgba(255,255,255,0.05); color: var(--text-3); font-size: 0.65rem;">Unmapped Empty State</span>
                    @else
                    <span class="status-badge" style="background: var(--purple-dim); color: var(--purple); font-size: 0.65rem;">{{ $serviceMappings->count() }} active mapping(s)</span>
                    @endif
                </div>
            </div>

            @if($serviceMappings->isNotEmpty())
            <div class="lux-table-wrapper lux-scroller" style="max-height: 350px; overflow-y: auto; border-radius: 0;">
                <table class="lux-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Material Model SKU</th>
                            <th>Consumption Quantity</th>
                            <th>Ledger Stock Status</th>
                            <th class="text-end" style="width: 80px;">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceMappings as $mapping)
                        @php
                        $stock = $mapping->product?->quantity ?? 0;
                        $threshold = $mapping->product?->low_stock_threshold ?? 5;
                        $isDeficit = $stock <= $threshold; @endphp <tr>
                            <td>
                                <div style="font-weight: 500; color: var(--text);">
                                    {{ $mapping->product?->name ?? 'Deleted/Missing SKU link' }}
                                </div>
                            </td>
                            <td class="font-mono" style="color: var(--teal-light); font-weight: 500;">
                                {{ $mapping->quantity_used }}
                                @if($mapping->unit)
                                <span style="font-size: 0.65rem; color: var(--text-3); font-family: var(--ff-body);">{{ $mapping->unit }}</span>
                                @endif
                            </td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.8rem; color: {{ $isDeficit ? 'var(--rose)' : 'var(--emerald)' }};">
                                    <span>{{ $stock }} units</span>
                                    @if($isDeficit) <i class="bi bi-exclamation-triangle-fill" title="Deficit Threshold alert!"></i> @endif
                                </span>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('owner.inventory.service-mapping.destroy', $mapping->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this mapping?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-action" title="Delete dependency mapping layer" style="color: var(--rose);">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                            </tr>
                            @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding: 1.5rem; text-align: center; border-top: 1px solid rgba(255,255,255,0.02);">
                <p style="font-size: 0.75rem; color: var(--text-3); display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin: 0;">
                    <i class="bi bi-diagram-2 faint"></i> No relational raw materials mapped yet. Use left operational panel fields structure.
                </p>
            </div>
            @endif
        </div>
        @endforeach

        @endif
    </div>
</div>

@endsection
