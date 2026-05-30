<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionWebController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('currentTenant');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $staffList = Staff::with('user')->where('tenant_id', $tenant->id)->get();

        $summary = $staffList->map(function ($s) use ($month, $year) {
            $comms = Commission::where('tenant_id', app('currentTenant')->id)
                ->where('staff_id', $s->id)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->get();

            return [
                'staff' => $s,
                'total_services' => $comms->count(),
                'total_earned' => $comms->sum('commission_amount'),
                'pending_amount' => $comms->where('status', 'pending')->sum('commission_amount'),
                'paid_amount' => $comms->where('status', 'paid')->sum('commission_amount'),
                'has_pending' => $comms->where('status', 'pending')->count() > 0,
            ];
        });

        $stats = [
            'total_commission' => Commission::where('tenant_id', $tenant->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('commission_amount'),
            'pending' => Commission::where('tenant_id', $tenant->id)->where('status', 'pending')->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('commission_amount'),
            'paid' => Commission::where('tenant_id', $tenant->id)->where('status', 'paid')->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('commission_amount'),
            'staff_paid_count' => $summary->where('pending_amount', 0)->where('total_services', '>', 0)->count(),
            'staff_pending_count' => $summary->where('has_pending', true)->count(),
        ];

        $currentMonth = Carbon::create($year, $month)->format('F Y');

        // Last 6 months for dropdown
        $months = collect(range(0, 5))->map(fn ($i) => [
            'label' => now()->subMonths($i)->format('M Y'),
            'month' => now()->subMonths($i)->month,
            'year' => now()->subMonths($i)->year,
        ]);

        return view('owner.commissions.index', compact('summary', 'stats', 'currentMonth', 'months', 'month', 'year'));
    }

    public function markAsPaid(Request $request, $staffId)
    {
        $tenant = app('currentTenant');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $updated = Commission::where('tenant_id', $tenant->id)
            ->where('staff_id', $staffId)
            ->where('status', 'pending')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->update(['status' => 'paid']);

        $staff = Staff::with('user')->where('tenant_id', $tenant->id)->find($staffId);
        $name = $staff?->user?->name ?? 'Staff';

        return back()->with('success', "{$name} ki commission paid mark ho gayi!");
    }
}
