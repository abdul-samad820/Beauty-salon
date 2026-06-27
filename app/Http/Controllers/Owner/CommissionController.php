<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    /**
     * Fetch all commissions with server-side filters and structural pagination ledger.
     */
    public function index(Request $request)
    {
        $currentTenant = app('currentTenant');

        $query = Commission::with([
            'staff.user',
            'appointment.service',
        ])->where('tenant_id', $currentTenant->id);

        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);
        $totalAmount = $query->sum('commission_amount'); // Aggregate over total filtered builder instance context directly

        return response()->json([
            'message' => 'Commissions fetched successfully.',
            'total_amount' => '₹'.number_format($totalAmount, 2),
            'total_count' => $commissions->total(),
            'data' => $commissions,
        ]);
    }

    /**
     * Optimized Staff Payout Aggregation Matrix resolving previous N+1 query vulnerability loops.
     */
    public function staffSummary(Request $request)
    {
        $tenantId = app('currentTenant')->id;
        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;

        $staff = Staff::with('user')->where('tenant_id', $tenantId)->get();

        // FIXED N+1 Query: Fetch all matching month records in single grouped memory buffer array map execution
        $monthlyCommissions = Commission::where('tenant_id', $tenantId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy('staff_id');

        $summary = $staff->map(function ($member) use ($monthlyCommissions) {
            $comms = $monthlyCommissions->get($member->id, collect());

            $totalEarned = $comms->sum('commission_amount');
            $pendingAmount = $comms->where('status', 'pending')->sum('commission_amount');
            $paidAmount = $comms->where('status', 'paid')->sum('commission_amount');

            return [
                'staff_id' => $member->id,
                'staff_name' => $member->user->name ?? 'Unknown',
                'total_services' => $comms->count(),
                'total_earned' => '₹'.number_format($totalEarned, 2),
                'pending_payout' => '₹'.number_format($pendingAmount, 2),
                'paid_payout' => '₹'.number_format($paidAmount, 2),
                'commission_rate' => $member->commission_percent.'%',
            ];
        });

        return response()->json([
            'message' => 'Staff commission summary fetched successfully.',
            'month' => Carbon::create($year, $month)->format('F Y'),
            'data' => $summary,
        ]);
    }

    /**
     * Secure status alteration pipeline guarding resource enumeration blocks.
     */
    public function markAsPaid(Request $request, $staffId)
    {
        $tenantId = app('currentTenant')->id;
        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;

        // FIXED SEC-013: Immediate input validation checkpoint preventing enumeration and path scanning probes
        $staff = Staff::with('user')
            ->where('tenant_id', $tenantId)
            ->find($staffId);

        if (! $staff) {
            return response()->json([
                'message' => 'Staff member not found or access unauthorized.',
            ], 404);
        }

        $commissions = Commission::where('tenant_id', $tenantId)
            ->where('staff_id', $staff->id)
            ->where('status', 'pending')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        if ($commissions->isEmpty()) {
            return response()->json([
                'message' => 'No pending commissions found for this period.',
            ], 422);
        }

        $totalPaid = $commissions->sum('commission_amount');

        Commission::where('tenant_id', $tenantId)
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
                'staff_name' => $staff->user->name,
                'month' => $month,
                'year' => $year,
                'total_paid' => $totalPaid,
                'commission_count' => $commissions->count(),
            ],
            $tenantId,
            'commission'
        );

        return response()->json([
            'message' => 'Commission payout marked as paid.',
            'staff_name' => $staff->user->name,
            'month' => Carbon::create($year, $month)->format('F Y'),
            'total_paid' => '₹'.number_format($totalPaid, 2),
        ]);
    }
}
