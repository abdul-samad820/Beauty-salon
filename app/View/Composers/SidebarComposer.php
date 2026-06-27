<?php

namespace App\View\Composers;

use App\Models\Appointment;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $tenantId = auth()->user()?->tenant_id;

        if (! $tenantId) {
            return;
        }

        $data = Cache::remember(
            "sidebar_stats_{$tenantId}",
            now()->addMinutes(5),
            function () use ($tenantId) {
                return [
                    'todayCount' => Appointment::where('tenant_id', $tenantId)
                        ->whereDate('appointment_date', today())
                        ->whereNotIn('status', ['cancelled'])
                        ->count(),

                    'lowStock' => Product::where('tenant_id', $tenantId)
                        ->whereRaw('quantity <= low_stock_threshold')
                        ->count(),
                ];
            }
        );

        $view->with($data);
    }
}
