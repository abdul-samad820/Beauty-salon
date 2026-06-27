<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToTenant , HasFactory , SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'price',
        'cost_price',
        'quantity',
        'low_stock_threshold',
        'is_active',
        'image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    // ── Helpers ────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->low_stock_threshold > 0
    && $this->quantity <= $this->low_stock_threshold;

    }

    public function getStockPercentAttribute(): int
    {
        $max = $this->low_stock_threshold * 5;
        if ($max <= 0) {
            return 100;
        }

        return min(100, (int) round(($this->quantity / $max) * 100));
    }

    public function getStockBarColorAttribute(): string
    {
        if ($this->isLowStock()) {
            return 'var(--rose)';
        }
        if ($this->stock_percent < 50) {
            return 'var(--amber)';
        }

        return 'var(--emerald)';
    }
}
