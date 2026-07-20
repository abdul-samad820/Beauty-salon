<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CommissionTier extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'min_revenue',
        'max_revenue',
        'commission_percent',
    ];

    protected $casts = [
        'min_revenue' => 'decimal:2',
        'max_revenue' => 'decimal:2',
        'commission_percent' => 'decimal:2',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Find the matching tier for a given monthly revenue amount.
     * Returns the commission_percent, or null if no tier matches.
     */
    public static function rateForStaff(int $staffId, float $monthlyRevenue, int $tenantId): ?float
    {
        $tier = static::withoutGlobalScopes()
            ->where('staff_id', $staffId)
            ->where('tenant_id', $tenantId)
            ->where('min_revenue', '<=', $monthlyRevenue)
            ->where(function ($q) use ($monthlyRevenue) {
                $q->whereNull('max_revenue')
                    ->orWhere('max_revenue', '>', $monthlyRevenue);
            })
            ->orderByDesc('min_revenue')
            ->first();

        return $tier ? (float) $tier->commission_percent : null;
    }
}
