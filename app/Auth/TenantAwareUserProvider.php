<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TenantAwareUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $tenantId = $credentials['tenant_id'] ?? null;

        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (str_contains($key, 'password') || $key === 'tenant_id') {
                continue;
            }
            $query->where($key, $value);
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->first();
    }
}
