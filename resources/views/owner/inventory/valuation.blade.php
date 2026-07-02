@extends('layouts.owner')

@section('title', 'Inventory Valuation')
@section('page-title', 'Inventory Valuation Report')
@section('breadcrumb', 'Inventory / Valuation')

@section('topbar-actions')
<a href="{{ route('owner.inventory.index') }}" class="btn-lux-ghost btn-sm" style="padding: 0.5rem 1rem; border-radius: var(--r-md); background: rgba(255,255,255,0.03); border: 1px solid var(--border); color: var(--text-2); text-decoration: none;">
    <i class="bi bi-arrow-left me-1"></i> Back to Inventory
</a>
@endsection

@section('content')

{{-- Period Selector --}}
<div class="card-lux mb-4 fade-up s1" style="padding: 1rem 1.25rem;">
    <form method="GET" action="{{ route('owner.inventory.valuation') }}" class="d-flex align-items-center gap-2 flex-wrap">
        <span style="font-size: 0.8rem; color: var(--text-3);">Stock movement period:</span>
        @foreach([7 => '7 Days', 30 => '30 Days', 90 => '90 Days'] as $val => $lbl)
        <a href="{{ route('owner.inventory.valuation', ['days' => $val]) }}" class="btn-sm" style="padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.75rem; text-decoration: none;
               {{ (int) $days === $val ? 'background:var(--gold); color:#1a1400; font-weight:600;' : 'background:var(--bg-card); color:var(--text-3); border:1px solid var(--border);' }}">
            {{ $lbl }}
        </a>
        @endforeach
    </form>
</div>

{{-- KPI Row --}}
<div class="row g-3 mb-4 fade-up s2">
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Total Stock Units" :value="number_format($stats['total_units'])" icon="bi-boxes" color="var(--purple)" bg="var(--purple-dim)" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Retail Value" :value="'₹' . number_format($stats['total_retail_value'], 0)" icon="bi-tag" color="var(--gold)" bg="rgba(212,175,55,0.12)" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Cost Value" :value="'₹' . number_format($stats['total_cost_value'], 0)" icon="bi-cash-coin" color="var(--teal-light)" bg="var(--teal-dim)" />
    </div>
    <div class="col-6 col-md-3">
        <x-cards.kpi-card label="Potential Profit" :value="'₹' . number_format($stats['potential_profit'], 0) . ' (' . $stats['margin_percent'] . '%)'" icon="bi-graph-up-arrow" color="var(--emerald)" bg="var(--emerald-dim)" />
    </div>
</div>

@if($stats['missing_cost_count'] > 0)
<div class="card-lux mb-4 fade-up s2" style="padding: 1rem 1.25rem; border-left: 3px solid var(--amber);">
    <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.85rem; color: var(--text-2);">
        <i class="bi bi-exclamation-triangle-fill" style="color: var(--amber);"></i>
        <span>{{ $stats['missing_cost_count'] }} product(s) have no purchase cost set — cost value and profit margin are calculated from the remaining products only. Add a cost price to each product for a fully accurate figure.</span>
    </div>
</div>
@endif

{{-- Valuation Table --}}
<div class="card-lux fade-up s3">
    <div class="p-4 border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
        <h3 class="serif" style="font-size: 1.15rem; color: var(--gold); margin-bottom: 0;">Stock Ledger</h3>
        <p style="font-size: 0.65rem; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Opening &rarr; Movement &rarr; Closing</p>
    </div>

    <div class="lux-table-wrapper lux-scroller" style="overflow-x: auto;">
        <table class="lux-table mb-0">
            <thead>
                <tr>
                    <th style="padding-left: 1.5rem;">Product</th>
                    <th class="text-center">Opening</th>
                    <th class="text-center">Stock In</th>
                    <th class="text-center">Stock Out</th>
                    <th class="text-center">Closing</th>
                    <th>Cost Value</th>
                    <th class="text-end" style="padding-right: 1.5rem;">Retail Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($valuationRows as $row)
                <tr style="transition: background 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.015)'" onmouseout="this.style.background='transparent'">
                    <td style="padding-left: 1.5rem;">
                        <div style="font-weight: 600; font-size: 0.85rem; color: var(--text);">
                            {{ $row['name'] }}
                            @if($row['is_low_stock'])
                            <i class="bi bi-exclamation-triangle-fill" style="color: var(--rose); font-size: 0.7rem; margin-left: 0.3rem;" title="Low stock"></i>
                            @endif
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-3); text-transform: capitalize;">{{ $row['category'] ?? '—' }}</div>
                    </td>
                    <td class="text-center faint" style="font-family: monospace;">{{ $row['opening_stock'] }}</td>
                    <td class="text-center" style="font-family: monospace; color: var(--emerald);">+{{ $row['stock_in'] }}</td>
                    <td class="text-center" style="font-family: monospace; color: var(--rose);">-{{ $row['stock_out'] }}</td>
                    <td class="text-center" style="font-family: monospace; font-weight: 600; color: var(--text);">{{ $row['closing_stock'] }}</td>
                    <td>
                        @if($row['cost_value'] !== null)
                        <div style="font-size: 0.85rem; color: var(--text-2); font-family: monospace;">₹{{ number_format($row['cost_value'], 0) }}</div>
                        @else
                        <span style="font-size: 0.7rem; color: var(--text-3); font-style: italic;">No cost set</span>
                        @endif
                    </td>
                    <td class="text-end" style="padding-right: 1.5rem;">
                        <div style="font-size: 0.85rem; color: var(--gold); font-family: monospace; font-weight: 600;">₹{{ number_format($row['retail_value'], 0) }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 3rem 1rem; color: var(--text-3);">
                        <i class="bi bi-box-seam" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                        No active products found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
