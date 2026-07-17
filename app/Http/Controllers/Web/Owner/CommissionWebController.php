<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionWebController extends Controller
{
    /**
     * Render the centralized owner commission management terminal window interface.
     */
    public function index(Request $request)
    {
        $tenant = app('currentTenant');
        if (! $tenant->canUseFeature('commission_enabled')) {
            return redirect()->route('owner.dashboard')
                ->with('error', 'Commission feature is not available on your current plan.');
        }
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $staffList = Staff::with('user')->where('tenant_id', $tenant->id)->get();

        $allTenantComms = Commission::where('tenant_id', $tenant->id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy('staff_id');

        $summary = $staffList->map(function ($s) use ($allTenantComms) {
            $comms = $allTenantComms->get($s->id, collect());

            return [
                'staff_id' => $s->id,
                'name' => $s->user?->name ?? 'Unknown',
                'initials' => strtoupper(substr($s->user?->name ?? 'U', 0, 2)),
                'commission_percent' => $s->commission_percent,
                'pending_amount' => $comms->where('status', 'pending')->sum('commission_amount'),
                'pending_count' => $comms->where('status', 'pending')->count(),
                'paid_month' => $comms->where('status', 'paid')->sum('commission_amount'),
                'total_services' => $comms->count(),
                'total_earned' => $comms->sum('commission_amount'),
                'has_pending' => $comms->where('status', 'pending')->count() > 0,
            ];
        });

        // Fast runtime optimizations over local collection variables instead of rerunning total aggregation rows scanner
        $flattenedComms = $allTenantComms->flatten(1);
        $commissions = Commission::with(['staff.user', 'appointment.service'])
            ->where('tenant_id', $tenant->id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->when($request->filled('staff_id'), fn ($q) => $q->where('staff_id', $request->staff_id))
            ->when($request->status && $request->status !== 'all', fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);
        $stats = [
            'total_accumulated' => $flattenedComms->sum('commission_amount'),
            'total_pending' => $flattenedComms->where('status', 'pending')->sum('commission_amount'),
            'total_settled' => $flattenedComms->where('status', 'paid')->sum('commission_amount'),
            'staff_count' => $staffList->count(),
            'avg_percent' => round($staffList->avg('commission_percent') ?? 0, 2),
        ];

        $currentMonth = Carbon::create($year, $month)->format('F Y');

        $months = collect(range(0, 5))->map(fn ($i) => [
            'label' => now()->subMonths($i)->format('M Y'),
            'month' => now()->subMonths($i)->month,
            'year' => now()->subMonths($i)->year,
        ]);

        return view('owner.commissions.index', compact('summary', 'stats', 'currentMonth', 'months', 'month', 'year', 'staffList', 'commissions'));
    }

    /**
     * Process updates securely over specific entity instances.
     */
    public function markAsPaid(Request $request, $staffId)
    {
        $tenant = app('currentTenant');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $staff = Staff::with('user')
            ->where('tenant_id', $tenant->id)
            ->find($staffId);

        if (! $staff) {
            return back()->with('error', 'Security Alert: Unauthorized access signature or resource targeting matching failed.');
        }

        $updated = Commission::where('tenant_id', $tenant->id)
            ->where('staff_id', $staff->id)
            ->where('status', 'pending')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->update(['status' => 'paid']);

        AuditLog::record(
            'commission.marked_paid',
            Staff::class,
            $staff->id,
            [
                'staff_name' => $staff->user?->name,
                'month' => $month,
                'year' => $year,
                'records_updated' => $updated,
            ],
            $tenant->id,
            'commission'
        );
        $name = $staff->user?->name ?? 'Staff';

        return back()->with('success', "Success: Commission ledger for {$name} has been updated to paid status.");
    }

    /**
     * Settle a single commission record as paid.
     */
    public function settle($id)
    {
        $tenant = app('currentTenant');

        $commission = Commission::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $commission->update(['status' => 'paid']);

        AuditLog::record(
            'commission.settled',
            Commission::class,
            $commission->id,
            [
                'staff_id' => $commission->staff_id,
                'amount' => $commission->commission_amount,
            ],
            $tenant->id,
            'commission'
        );

        return back()->with('success', 'Success: Commission record marked as paid successfully.');
    }
}
