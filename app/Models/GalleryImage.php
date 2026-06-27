<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalleryImage extends Model
{
    use BelongsToTenant, HasFactory , SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'image',
        'caption',
        'sort_order',
        'is_active',
    ];
}
