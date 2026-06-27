<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class NotificationController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');

        $notifications = AuditLog::where('tenant_id', $tenant->id)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'title' => $this->getTitle($log->action, $log->payload),
                'time' => $log->created_at->diffForHumans(),
                'icon' => $this->getIcon($log->type),
                'color' => $this->getColor($log->type),
                'is_read' => $log->is_read,
            ]);

        $unreadCount = AuditLog::where('tenant_id', $tenant->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead()
    {
        $tenant = app('currentTenant');

        AuditLog::where('tenant_id', $tenant->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // 30 din purani read notifications delete karo
        AuditLog::where('tenant_id', $tenant->id)
            ->where('is_read', true)
            ->where('created_at', '<', now()->subDays(30))
            ->delete();

        return response()->json(['success' => true]);
    }

    private function getTitle(string $action, ?array $payload): string
    {
        return match ($action) {
            'appointment.booked' => ($payload['customer_name'] ?? 'A customer').' booked '.($payload['service_name'] ?? 'a service').' for '.($payload['date'] ?? ''),
            'appointment.cancelled' => ($payload['customer_name'] ?? 'A customer').' cancelled their '.($payload['service_name'] ?? 'appointment'),
            'appointment.completed' => 'Appointment completed — ₹'.number_format($payload['amount'] ?? 0),
            'stock.low' => 'Low stock alert: '.($payload['product_name'] ?? 'A product').' ('.($payload['quantity'] ?? 0).' left)',
            'review.received' => ($payload['customer_name'] ?? 'A customer').' left a '.($payload['rating'] ?? '').'★ review',
            default => ucfirst(str_replace('.', ' ', $action)),
        };
    }

    private function getIcon(string $type): string
    {
        return match ($type) {
            'booking' => 'bi-calendar-check',
            'stock' => 'bi-box-seam',
            'review' => 'bi-star',
            'payment' => 'bi-credit-card',
            default => 'bi-bell',
        };
    }

    private function getColor(string $type): string
    {
        return match ($type) {
            'booking' => 'var(--emerald)',
            'stock' => 'var(--rose)',
            'review' => 'var(--gold)',
            'payment' => 'var(--purple)',
            default => 'var(--text-3)',
        };
    }
}
