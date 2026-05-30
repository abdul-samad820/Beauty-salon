<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'price',
        'quantity',
        'low_stock_threshold',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    // ── Helpers ────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
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
