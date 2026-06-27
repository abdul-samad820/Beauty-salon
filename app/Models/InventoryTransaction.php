<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use BelongsToTenant, HasFactory , SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'type',       // 'in' | 'out'
        'quantity',
        'reason',
        'reference_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
