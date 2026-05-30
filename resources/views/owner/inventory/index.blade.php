@extends('owner.layouts.app')
@section('title','Inventory')
@section('page-title','Inventory')
@section('page-sub', $stats['total'] . ' products · ' . $stats['low_stock'] . ' low stock alerts')
@section('topbar-actions')
  <button class="btn-gold-sm" onclick="document.getElementById('addModal').style.display='flex'">
    <i class="bi bi-plus-lg"></i> Add Product
  </button>
@endsection

@push('styles')
<style>
  .prod-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1.4rem;transition:all var(--transition);position:relative;overflow:hidden}
  .prod-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent)}
  .prod-card:hover{border-color:rgba(201,169,110,.2);box-shadow:0 10px 40px rgba(0,0,0,.4);transform:translateY(-3px)}
  .prod-card.low-stock{border-color:rgba(244,63,94,.2)}
  .prod-card.low-stock:hover{border-color:var(--rose)}
  .stock-bar-wrap{height:5px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden;margin-bottom:.3rem}
  .stock-bar{height:100%;border-radius:3px;transition:width 1.2s var(--ease)}
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);z-index:500;align-items:center;justify-content:center;padding:1rem}
  .modal-box{background:var(--bg-card);border:1px solid var(--border-2);border-radius:16px;padding:2rem;width:100%;max-width:460px;max-height:90vh;overflow-y:auto}
  .fl-group{margin-bottom:1rem}
  .fl-group label{display:block;font-size:.65rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--text-3);margin-bottom:.4rem}
  .fl-group input,.fl-group select{width:100%;background:var(--bg-input);border:1px solid var(--border-2);border-radius:8px;color:var(--text);font-family:var(--ff-body);font-size:.82rem;padding:.7rem 1rem;outline:none;transition:border-color .25s}
  .fl-group input:focus,.fl-group select:focus{border-color:var(--gold)}
</style>
@endpush

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3 fade-up s1"><div class="card-lux kpi-pad gold-border"><div class="kpi-label">Total Products</div><div class="kpi-value" style="color:var(--gold)">{{ $stats['total'] }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s2"><div class="card-lux kpi-pad" style="border-top:2px solid var(--rose)"><div class="kpi-label">Low Stock</div><div class="kpi-value" style="color:var(--rose)">{{ $stats['low_stock'] }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s3"><div class="card-lux kpi-pad" style="border-top:2px solid var(--emerald)"><div class="kpi-label">Total Value</div><div class="kpi-value" style="color:var(--emerald)">₹{{ number_format($stats['total_value']) }}</div></div></div>
  <div class="col-6 col-md-3 fade-up s4"><div class="card-lux kpi-pad" style="border-top:2px solid var(--amber)"><div class="kpi-label">Transactions</div><div class="kpi-value" style="color:var(--amber)">{{ $recentTransactions->count() }}</div></div></div>
</div>

{{-- Low stock alert banner --}}
@if($stats['low_stock'] > 0)
<div style="background:var(--rose-dim);border:1px solid rgba(244,63,94,.2);border-radius:10px;padding:.9rem 1.2rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.7rem;font-size:.82rem;color:var(--rose)" class="fade-up s2">
  <i class="bi bi-exclamation-triangle-fill"></i>
  <strong>{{ $stats['low_stock'] }} products</strong> ka stock low hai — replenishment needed.
</div>
@endif

<div class="row g-3 fade-up s3">
  @forelse($products as $prod)
  @php
    $pct = $prod->low_stock_threshold > 0 ? min(100, round(($prod->quantity / ($prod->low_stock_threshold * 5)) * 100)) : 100;
    $barColor = $prod->isLowStock() ? 'var(--rose)' : ($pct < 50 ? 'var(--amber)' : 'var(--emerald)');
  @endphp
  <div class="col-sm-6 col-lg-4">
    <div class="prod-card {{ $prod->isLowStock() ? 'low-stock' : '' }}">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.8rem">
        <div>
          @if($prod->category)
            <div style="font-size:.58rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--text-3);margin-bottom:.3rem">{{ $prod->category }}</div>
          @endif
          <div style="font-size:.92rem;font-weight:500;color:var(--text)">{{ $prod->name }}</div>
        </div>
        @if($prod->isLowStock())
          <span class="lux-badge lb-red"><i class="bi bi-exclamation-triangle-fill"></i> Low</span>
        @endif
      </div>
      <div style="display:flex;justify-content:space-between;margin-bottom:.6rem">
        <div><div style="font-family:var(--ff-display);font-size:1.4rem;color:{{ $prod->isLowStock() ? 'var(--rose)' : 'var(--text)' }}">{{ $prod->quantity }}</div><div style="font-size:.62rem;color:var(--text-3)">in stock</div></div>
        <div style="text-align:right"><div style="font-size:.82rem;color:var(--gold)">₹{{ number_format($prod->price) }}</div><div style="font-size:.62rem;color:var(--text-3)">per unit</div></div>
      </div>
      <div class="stock-bar-wrap"><div class="stock-bar" style="width:{{ $pct }}%;background:{{ $barColor }}"></div></div>
      <div style="font-size:.62rem;color:var(--text-3);margin-bottom:1rem">Min: {{ $prod->low_stock_threshold }} units</div>
      <div style="display:flex;gap:.4rem">
        <button class="btn-ghost-sm" style="flex:1;justify-content:center;border-color:var(--emerald);color:var(--emerald)" onclick="openStockIn({{ $prod->id }},'{{ addslashes($prod->name) }}')">
          <i class="bi bi-plus-lg"></i> Stock In
        </button>
        <button class="btn-ghost-sm" style="flex:1;justify-content:center" onclick="openStockOut({{ $prod->id }},'{{ addslashes($prod->name) }}',{{ $prod->quantity }})">
          <i class="bi bi-dash-lg"></i> Stock Out
        </button>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12" style="text-align:center;padding:3rem;color:var(--text-3)">
    <i class="bi bi-box-seam" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
    Koi product nahi. Pehla product add karein!
  </div>
  @endforelse
</div>

{{-- Recent Transactions --}}
@if($recentTransactions->count() > 0)
<div class="card-lux p-4 mt-3 fade-up">
  <div class="sec-hdr"><div class="sec-title">Recent Transactions</div><div class="sec-sub">Last 10 stock movements</div></div>
  <table class="lux-table">
    <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>Reason</th><th>Date</th></tr></thead>
    <tbody>
      @foreach($recentTransactions as $tx)
      <tr>
        <td style="color:var(--text)">{{ $tx->product?->name }}</td>
        <td><span class="lux-badge {{ $tx->type==='in' ? 'lb-green' : 'lb-red' }}">{{ strtoupper($tx->type) }}</span></td>
        <td style="{{ $tx->type==='in' ? 'color:var(--emerald)' : 'color:var(--rose)' }}">{{ $tx->type==='in' ? '+' : '-' }}{{ $tx->quantity }}</td>
        <td>{{ $tx->reason }}</td>
        <td>{{ $tx->created_at->format('d M, h:i A') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

{{-- Add Product Modal --}}
<div class="modal-overlay" id="addModal" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
      <div style="font-family:var(--ff-display);font-size:1.3rem;color:var(--text)">Add New Product</div>
      <button onclick="document.getElementById('addModal').style.display='none'" style="background:none;border:none;color:var(--text-3);font-size:1.2rem;cursor:pointer"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="{{ route('owner.inventory.store') }}">
      @csrf
      <div class="fl-group"><label>Product Name *</label><input type="text" name="name" required /></div>
      <div class="row g-2">
        <div class="col-6"><div class="fl-group"><label>Category</label><input type="text" name="category" placeholder="Hair Care, Skin…" /></div></div>
        <div class="col-6"><div class="fl-group"><label>Price (₹) *</label><input type="number" name="price" min="0" step="10" required /></div></div>
        <div class="col-6"><div class="fl-group"><label>Initial Qty *</label><input type="number" name="quantity" min="0" required /></div></div>
        <div class="col-6"><div class="fl-group"><label>Low Stock Alert At</label><input type="number" name="low_stock_threshold" min="1" value="5" /></div></div>
      </div>
      <div style="display:flex;gap:.8rem;margin-top:.5rem">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="btn-ghost-sm" style="flex:1;justify-content:center">Cancel</button>
        <button type="submit" class="btn-gold-sm" style="flex:2;justify-content:center"><i class="bi bi-check-lg"></i> Add Product</button>
      </div>
    </form>
  </div>
</div>

{{-- Stock In Modal --}}
<div class="modal-overlay" id="stockInModal" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box" style="max-width:380px">
    <div style="font-family:var(--ff-display);font-size:1.3rem;color:var(--emerald);margin-bottom:.3rem">Stock In</div>
    <div id="stockInProductName" style="font-size:.75rem;color:var(--text-3);margin-bottom:1.2rem"></div>
    <form method="POST" action="{{ route('owner.inventory.stock-in') }}">
      @csrf
      <input type="hidden" name="product_id" id="stockInId" />
      <div class="fl-group"><label>Quantity *</label><input type="number" name="quantity" min="1" required /></div>
      <div class="fl-group"><label>Reason</label><input type="text" name="reason" placeholder="Purchase, Return…" /></div>
      <div style="display:flex;gap:.8rem;margin-top:.5rem">
        <button type="button" onclick="document.getElementById('stockInModal').style.display='none'" class="btn-ghost-sm" style="flex:1;justify-content:center">Cancel</button>
        <button type="submit" class="btn-gold-sm" style="flex:2;justify-content:center;background:var(--emerald);color:white"><i class="bi bi-plus-lg"></i> Add Stock</button>
      </div>
    </form>
  </div>
</div>

{{-- Stock Out Modal --}}
<div class="modal-overlay" id="stockOutModal" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal-box" style="max-width:380px">
    <div style="font-family:var(--ff-display);font-size:1.3rem;color:var(--rose);margin-bottom:.3rem">Stock Out</div>
    <div id="stockOutProductName" style="font-size:.75rem;color:var(--text-3);margin-bottom:1.2rem"></div>
    <form method="POST" action="{{ route('owner.inventory.stock-out') }}">
      @csrf
      <input type="hidden" name="product_id" id="stockOutId" />
      <div class="fl-group"><label>Quantity *</label><input type="number" name="quantity" min="1" id="stockOutQty" required /></div>
      <div class="fl-group"><label>Reason</label><input type="text" name="reason" placeholder="Service use, Wastage…" /></div>
      <div style="display:flex;gap:.8rem;margin-top:.5rem">
        <button type="button" onclick="document.getElementById('stockOutModal').style.display='none'" class="btn-ghost-sm" style="flex:1;justify-content:center">Cancel</button>
        <button type="submit" class="btn-gold-sm" style="flex:2;justify-content:center;background:var(--rose);color:white"><i class="bi bi-dash-lg"></i> Use Stock</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openStockIn(id, name) {
  document.getElementById('stockInId').value = id;
  document.getElementById('stockInProductName').textContent = name;
  document.getElementById('stockInModal').style.display = 'flex';
}
function openStockOut(id, name, qty) {
  document.getElementById('stockOutId').value = id;
  document.getElementById('stockOutProductName').textContent = name + ' · ' + qty + ' in stock';
  document.getElementById('stockOutQty').max = qty;
  document.getElementById('stockOutModal').style.display = 'flex';
}
</script>
@endpush
