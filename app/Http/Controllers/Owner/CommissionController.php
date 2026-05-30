<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    // Saari commissions — filter ke saath
    public function index(Request $request)
    {

        $currentTenant = app('currentTenant');

        $query = Commission::with([
            'staff.user',
            'appointment.service',
        ])->where(
            'tenant_id',
            $currentTenant->id
        );

        // Month filter — optional
        if ($request->has('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        // Year filter — optional
        if ($request->has('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // Staff filter — optional
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Status filter — optional
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Total commission amount
        $totalAmount = $commissions->sum('commission_amount');

        return response()->json([
            'message' => 'Commissions fetched successfully',
            'total_amount' => '₹'.number_format($totalAmount, 2),
            'total_count' => $commissions->count(),
            'data' => $commissions,
        ]);
    }

    // Staff wise monthly summary
    public function staffSummary(Request $request)
    {
        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;

        $staff = Staff::with('user')
            ->where(
                'tenant_id',
                app('currentTenant')->id
            )
            ->get();

        $summary = $staff->map(function ($member) use ($month, $year) {

            // Is staff ki is month ki commissions
            $commissions = Commission::where(
                'tenant_id',
                app('currentTenant')->id
            )
                ->where(
                    'staff_id',
                    $member->id
                )
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->get();

            $totalEarned = $commissions->sum('commission_amount');
            $pendingAmount = $commissions
                ->where('status', 'pending')
                ->sum('commission_amount');
            $paidAmount = $commissions
                ->where('status', 'paid')
                ->sum('commission_amount');

            return [
                'staff_id' => $member->id,
                'staff_name' => $member->user->name,
                'total_services' => $commissions->count(),
                'total_earned' => '₹'.number_format($totalEarned, 2),
                'pending_payout' => '₹'.number_format($pendingAmount, 2),
                'paid_payout' => '₹'.number_format($paidAmount, 2),
                'commission_rate' => $member->commission_percent.'%',
            ];
        });

        return response()->json([
            'message' => 'Staff commission summary',
            'month' => Carbon::create($year, $month)->format('F Y'),
            'data' => $summary,
        ]);
    }

    // Commission pay kar do — pending → paid
    public function markAsPaid(Request $request, $staffId)
    {
        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;

        $commissions = Commission::where(
            'tenant_id',
            app('currentTenant')->id
        )
            ->where(
                'staff_id',
                $staffId
            )
            ->where('status', 'pending')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        if ($commissions->isEmpty()) {
            return response()->json([
                'message' => 'Koi pending commission nahi hai.',
            ], 404);
        }

        $totalPaid = $commissions->sum('commission_amount');

        // Sab pending → paid karo
        Commission::where(
            'tenant_id',
            app('currentTenant')->id
        )
            ->where(
                'staff_id',
                $staffId
            )
            ->where('status', 'pending')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->update(['status' => 'paid']);

        $staff = Staff::with('user')
            ->where(
                'tenant_id',
                app('currentTenant')->id
            )
            ->findOrFail($staffId);

        return response()->json([
            'message' => 'Commission marked as paid',
            'staff_name' => $staff->user->name,
            'month' => Carbon::create($year, $month)->format('F Y'),
            'total_paid' => '₹'.number_format($totalPaid, 2),
        ]);
    }
}
