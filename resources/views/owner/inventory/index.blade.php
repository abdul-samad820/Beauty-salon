@extends('layouts.owner')

@section('title', 'Inventory Register')
@section('page-title', 'Stock Ledger Matrix')
@section('breadcrumb', 'Manage / Inventory')
@push('styles')
<style>
    /* Premium Gold Scroller */
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

    /* Table Header Sticky fix */
    .lux-table thead th {
        position: sticky;
        top: 0;
        background: var(--bg-card);
        z-index: 10;
        border-bottom: 1px solid var(--border);
    }

</style>
@endpush
@section('topbar-actions')
<a href="{{ route('owner.inventory.valuation') }}" class="btn-lux-ghost btn-sm" style="padding: 0.5rem 1rem; border-radius: var(--r-md); background: rgba(255,255,255,0.03); border: 1px solid var(--border); color: var(--text-2); text-decoration: none;">
    <i class="bi bi-clipboard-data me-1"></i> Valuation Report
</a>
<button class="btn-lux-gold btn-sm" onclick="openAddProductModal()">
    <i class="bi bi-plus-lg" aria-hidden="true"></i> Add New Product Node
</button>
@endsection

@section('content')

<!-- System Metric Layout Row Component -->
<div class="mb-4 fade-up s1">
    <x-cards.stat-row :stats="[
        ['label' => 'Total SKU Products',          'value' => $stats['total'],                               'color' => 'var(--gold)'],
        ['label' => 'Low Stock Warning Profiles',  'value' => $stats['low_stock'],                           'color' => 'var(--rose)'],
        ['label' => 'Gross Ledger Valuation',      'value' => '₹' . number_format($stats['total_value'], 0), 'color' => 'var(--emerald)'],
    ]" />
</div>

<!-- Low Stock Critical Alert -->
@if($stats['low_stock'] > 0)
<div class="card-lux mb-4 fade-up s1" style="display: flex; align-items: center; gap: 1rem; border-left: 3px solid var(--rose); background: rgba(244, 63, 94, 0.05); padding: 1rem 1.5rem;" role="alert">
    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--rose-dim); color: var(--rose); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <i class="bi bi-exclamation-triangle-fill"></i>
    </div>
    <div style="font-size: 0.8rem; color: var(--text);">
        <span style="font-weight: 600; color: var(--rose);">{{ $stats['low_stock'] }} critical products</span> are running below safe threshold parameters. Reorder stock levels to stabilize parlour treatments workflows.
    </div>
</div>
@endif

<!-- Tab Navigation Layout -->
<div class="mb-4 fade-up s2" style="display: flex; gap: 1.5rem; border-bottom: 1px solid var(--border);" role="tablist">
    @foreach(['all' => 'All Material Stock', 'low' => 'Deficit Alert Registers'] as $tab => $label)
    <a href="{{ route('owner.inventory.index') }}?tab={{ $tab }}" style="padding-bottom: 0.8rem; font-size: 0.8rem; font-weight: 600; text-decoration: none; color: {{ request('tab','all') === $tab ? 'var(--gold)' : 'var(--text-3)' }}; border-bottom: 2px solid {{ request('tab','all') === $tab ? 'var(--gold)' : 'transparent' }}; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem;" role="tab" aria-selected="{{ request('tab','all') === $tab ? 'true' : 'false' }}">
        <span>{{ $label }}</span>
        @if($tab === 'low' && $stats['low_stock'] > 0)
        <span style="background: var(--rose-dim); color: var(--rose); font-size: 0.6rem; padding: 0.1rem 0.4rem; border-radius: 10px;">
            {{ $stats['low_stock'] }}
        </span>
        @endif
    </a>
    @endforeach
</div>

<!-- Data Table Mesh -->
<div class="card-lux fade-up s3">
<div class="lux-table-wrapper lux-scroller" style="max-height: 500px; overflow-y: auto; overflow-x: auto;">
        <table class="lux-table">
            <thead>
                <tr>
                    <th>Product Variant Ledger</th>
                    <th>Category Tag</th>
                    <th>Base Valuation Rate</th>
                    <th>Ledger Active Stock</th>
                    <th>Safe Threshold Limit</th>
                    <th>Status Info</th>
                    <th class="text-end" style="width: 140px;">Actions Terminal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                @php
                $isLow = $p->quantity <= $p->low_stock_threshold;
                    $isCritical = $p->quantity <= max(1, $p->low_stock_threshold / 2);
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 500; color: var(--text);">{{ $p->name }}</div>
                            </td>
                            <td>
                                <span class="plan-badge" style="background: rgba(255,255,255,0.05); color: var(--text-2);">
                                    {{ ucfirst($p->category ?? 'General SKU') }}
                                </span>
                            </td>
                            <td class="serif" style="font-size: 1.1rem; color: var(--text);">
                                ₹{{ number_format($p->price, 0) }}
                            </td>
                            <td>
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.85rem; color: @if($isCritical) var(--rose) @elseif($isLow) var(--amber) @else var(--emerald) @endif;">
                                    @if($isLow) <i class="bi bi-exclamation-triangle-fill"></i> @endif
                                    {{ $p->quantity }} units
                                </div>
                            </td>
                            <td class="faint" style="font-family: monospace;">
                                {{ $p->low_stock_threshold }} units
                            </td>
                            <td>
                                <span class="status-badge {{ $p->is_active ? 'badge-active' : 'badge-suspended' }}">
                                    @if($p->is_active) <span class="live-dot" style="margin-right: 0.2rem;"></span> @endif
                                    {{ $p->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button class="btn-icon-action" style="color: var(--emerald);" title="Stock In - Add Entry" onclick="handleStockUpdateModalOpen('in', {{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->quantity }})">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                    <button class="btn-icon-action" style="color: var(--rose);" title="Stock Out - Adjust Deduct" onclick="handleStockUpdateModalOpen('out', {{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->quantity }})">
                                        <i class="bi bi-dash-circle"></i>
                                    </button>
                                    <button class="btn-icon-action" title="Edit Variant Profile" data-prod-id="{{ $p->id }}" data-prod-name="{{ $p->name }}" data-prod-price="{{ $p->price }}" data-prod-cost-price="{{ $p->cost_price }}" data-prod-threshold="{{ $p->low_stock_threshold }}" data-prod-category="{{ $p->category ?? '' }}" data-prod-image="{{ $p->image ?? '' }}" onclick="handleEditProductModalTrigger(this)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 4rem 2rem;">
                                <i class="bi bi-box-seam faint d-block mb-3" style="font-size: 2rem;"></i>
                                <h4 class="faint" style="font-size: var(--text-sm);">No active inventory records found</h4>
                                <p class="muted" style="font-size: var(--text-xs); max-width: 400px; margin: 0 auto;">
                                    The inventory registry is currently empty. Please add items to begin tracking your service material consumption.
                                </p>
                            </td>
                        </tr>
                        @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="lux-pagination-wrapper border-top" style="border-color: var(--border) !important; padding: 1rem 1.5rem;">
        <x-tables.pagination :paginator="$products" />
    </div>
    @endif
</div>

<!-- Modal 1: Add/Edit Product -->
<x-cards.modal id="addProductModal" title="Add Product SKU Node">
    <div class="lux-scroller" style="max-height: 70vh; overflow-y: auto; padding-right: 10px;">
        <form method="POST" id="productForm" action="{{ route('owner.inventory.store') }}" enctype="multipart/form-data">
            @csrf
            <span id="productMethod"></span>

            <div class="row g-3">
                <div class="col-12">
                    <x-forms.input name="name" id="name" label="Product Registry Variant Name *" :required="true" />
                </div>
                <div class="col-12">
                    <label class="lux-label">Product Image</label>
                    <input type="file" name="image" id="productImage" accept="image/*" class="lux-input" style="padding:0.5rem;" />
                    <div id="currentImageWrap" style="display:none;margin-top:0.5rem;">
                        <img id="currentImage" src="" style="height:60px;border-radius:6px;object-fit:cover;" />
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="lux-label" for="category">Category Module</label>
                    <div style="position: relative;">
                        <select name="category" id="category" class="lux-input" style="color-scheme: dark; padding-right: 2.5rem; background: var(--bg-input); color: var(--text); cursor: pointer;">

                            <option value="" style="background: var(--bg-card); color: var(--text-3);">Choose Group...</option>
                            @foreach(['hair'=>'Hair','skin'=>'Skin','nail'=>'Nail','tools'=>'Tools','other'=>'Other'] as $val => $lbl)
                            <option value="{{ $val }}" style="background: var(--bg-card); color: var(--text);">{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-3);">
                            <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <x-forms.input name="price" id="price" label="Selling Price (₹) *" type="number" min="0" step="10" />
                </div>

                <div class="col-12 col-md-6">
                    <x-forms.input name="cost_price" id="cost_price" label="Purchase Cost Rate (₹)" type="number" min="0" step="10" />
                </div>

                <div id="quantity_wrapper" class="col-12 col-md-6">
                    <x-forms.input name="quantity" id="quantity" label="Initial Stock Units *" type="number" min="0" />
                </div>

                <div id="threshold_wrapper" class="col-12 col-md-6">
                    <x-forms.input name="low_stock_threshold" id="low_stock_threshold" label="Safe Warning Threshold *" type="number" min="1" />
                </div>
            </div>

            <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <button type="button" onclick="LuxModal.close('addProductModal')" class="btn-lux-ghost btn-sm border-0">Cancel Operations</button>
                <button type="submit" class="btn-lux-gold btn-sm">Confirm Save Product</button>
            </div>
        </form>
    </div>
</x-cards.modal>

<!-- Modal 2: Adjust Stock -->
<x-cards.modal id="stockModal" title="Adjust Stock Inventory Ledger">
    <form method="POST" id="stockForm" action="">
        @csrf
        <input type="hidden" name="product_id" id="stockProductId" />

        <div style="margin-bottom: 1rem; border-radius: var(--r-md); border: 1px solid rgba(255,255,255,0.03); background: rgba(255,255,255,0.02); padding: 1rem;">
            <div id="stockProductName" style="font-size: 0.85rem; font-weight: 600; color: var(--text);"></div>
            <div id="stockCurrentQty" style="font-size: 0.7rem; color: var(--text-3); margin-top: 0.2rem;"></div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <x-forms.input name="quantity" label="Adjustment Value Count Units *" type="number" min="1" :required="true" />
            </div>
            <div class="col-12">
                <x-forms.input name="reason" label="Ledger Adjustment Cause Note" placeholder="e.g. Salon periodic re-stock allocation logs" />
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border); padding-top: 1rem;">
            <button type="button" onclick="LuxModal.close('stockModal')" class="btn-lux-ghost btn-sm border-0">Cancel Entry</button>
            <button type="submit" id="stockSubmitBtn" class="btn-lux-gold btn-sm">Apply Ledger Log</button>
        </div>
    </form>
</x-cards.modal>

@endsection

@push('scripts')
<script>
    const INVENTORY_PRODUCT_STORE_URL = "{{ route('owner.inventory.store') }}";

    function openAddProductModal() {
        const form = document.getElementById('productForm');
        form.reset();
        form.action = INVENTORY_PRODUCT_STORE_URL;
        document.getElementById('productMethod').innerHTML = '';

        const qtyWrapper = document.getElementById('quantity_wrapper');
        if (qtyWrapper) {
            qtyWrapper.style.display = 'block';
            const qtyInput = document.getElementById('quantity');
            if (qtyInput) qtyInput.required = true;
        }

        const threshWrapper = document.getElementById('threshold_wrapper');
        if (threshWrapper) {
            threshWrapper.className = "col-12 col-md-6";
        }

        document.querySelector('#addProductModal .lux-modal-title').textContent = 'Add Product SKU Node';
        LuxModal.open('addProductModal');
    }

    function handleEditProductModalTrigger(buttonElement) {
        const dataset = buttonElement.dataset;
        const form = document.getElementById('productForm');

        document.querySelector('#addProductModal .lux-modal-title').textContent = 'Edit Product SKU Configuration';
        form.action = `{{ url('owner/products') }}/${dataset.prodId}`;
        document.getElementById('productMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('name').value = dataset.prodName;
        document.getElementById('price').value = dataset.prodPrice;
        document.getElementById('cost_price').value = dataset.prodCostPrice;
        document.getElementById('low_stock_threshold').value = dataset.prodThreshold;
        document.getElementById('category').value = dataset.prodCategory;

        // Image preview
        const imgWrap = document.getElementById('currentImageWrap');
        const imgEl = document.getElementById('currentImage');
        if (dataset.prodImage) {
            imgEl.src = '/storage/' + dataset.prodImage;
            imgWrap.style.display = 'block';
        } else {
            imgWrap.style.display = 'none';
        }

        const qtyWrapper = document.getElementById('quantity_wrapper');
        if (qtyWrapper) {
            qtyWrapper.style.display = 'none';
            const qtyInput = document.getElementById('quantity');
            if (qtyInput) qtyInput.required = false;
        }

        const threshWrapper = document.getElementById('threshold_wrapper');
        if (threshWrapper) {
            threshWrapper.className = "col-12";
        }

        LuxModal.open('addProductModal');
    }

    function handleStockUpdateModalOpen(type, id, name, currentQty) {
        const modalTitle = document.querySelector('#stockModal .lux-modal-title');
        const submitBtn = document.getElementById('stockSubmitBtn');

        modalTitle.textContent = type === 'in' ? 'Stock In — Add Inventory Payout' : 'Stock Out — Deduct Waste Shortage';
        document.getElementById('stockForm').action = `/owner/inventory/stock-${type}`;
        document.getElementById('stockProductId').value = id;
        document.getElementById('stockProductName').textContent = name;
        document.getElementById('stockCurrentQty').textContent = `Current registered volume levels: ${currentQty} units`;

        if (type === 'out') {
            submitBtn.style.background = "var(--rose)";
            submitBtn.style.color = "white";
            submitBtn.style.border = "none";
        } else {
            submitBtn.style.background = "var(--emerald)";
            submitBtn.style.color = "white";
            submitBtn.style.border = "none";
        }

        LuxModal.open('stockModal');
    }

</script>
@endpush
