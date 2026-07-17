<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'action',
        'type',
        'entity_type',
        'entity_id',
        'payload',
        'ip_address',
        'is_read',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_read' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────

    // ── Helper — Record  ───────────────────────────────────
    public static function record(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $payload = [],
        ?int $tenantId = null,
        string $type = 'system'
    ): void {
        static::create([
            'user_id' => Auth::id(),
            'tenant_id' => $tenantId,
            'action' => $action,
            'type' => $type,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'payload' => $payload,
            'ip_address' => request()->ip(),
            'is_read' => false,
        ]);
    }
}
