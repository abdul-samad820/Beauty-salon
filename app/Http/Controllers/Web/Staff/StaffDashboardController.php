<?php

namespace App\Http\Controllers\Web\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Commission;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffDashboardController extends Controller
{
    private function getStaff()
    {
        $staff = Staff::withoutGlobalScopes()
            ->where('user_id', Auth::id())
            ->with('tenant')
            ->first();

        if (! $staff) {
            Auth::logout();
            abort(403, 'Staff record not found.');
        }

        // currentTenant bind karo
        if ($staff->tenant && ! app()->has('currentTenant')) {
            app()->instance('currentTenant', $staff->tenant);
        }

        return $staff;
    }

    public function index()
    {
        $staff = $this->getStaff();
        $tenant = $staff->tenant;

        $today = Carbon::today();
        $month = Carbon::now();

        $todayAppointments = Appointment::with(['customer', 'service'])
            ->where('staff_id', $staff->id)
            ->where('tenant_id', $staff->tenant_id)
            ->whereDate('appointment_date', $today)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_time')
            ->get();

        $monthCompleted = Appointment::where('staff_id', $staff->id)
            ->where('tenant_id', $staff->tenant_id)
            ->where('status', 'completed')
            ->whereMonth('appointment_date', $month->month)
            ->whereYear('appointment_date', $month->year)
            ->count();

        $commissionStats = Commission::where('staff_id', $staff->id)
            ->whereMonth('created_at', $month->month)
            ->whereYear('created_at', $month->year)
            ->selectRaw('
        SUM(commission_amount) as month_earnings,
        SUM(CASE WHEN status = "pending" THEN commission_amount ELSE 0 END) as pending_amount
    ')
            ->first();

        $stats = [
            'today_total' => $todayAppointments->count(),
            'month_completed' => $monthCompleted,
            'month_earnings' => $commissionStats->month_earnings ?? 0,
            'pending_commission' => $commissionStats->pending_amount ?? 0,
        ];

        return view('staff.dashboard.index', compact('stats', 'todayAppointments', 'tenant', 'staff'));
    }

    public function appointments()
    {
        $staff = $this->getStaff(); // PEHLE
        $tenant = $staff->tenant;

        $status = request('status', 'all');

        $appointments = Appointment::with(['customer', 'service'])
            ->where('staff_id', $staff->id)
            ->where('tenant_id', $staff->tenant_id)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('staff.appointments.index', compact('appointments', 'tenant', 'staff', 'status'));
    }

    public function commissions()
    {
        $staff = $this->getStaff(); // PEHLE
        $tenant = $staff->tenant;

        $commissions = Commission::with('appointment.service')
            ->where('staff_id', $staff->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $summary = [
            'total_earned' => Commission::where('staff_id', $staff->id)->sum('commission_amount'),
            'pending' => Commission::where('staff_id', $staff->id)->where('status', 'pending')->sum('commission_amount'),
            'paid' => Commission::where('staff_id', $staff->id)->where('status', 'paid')->sum('commission_amount'),
        ];

        return view('staff.commissions.index', compact('commissions', 'summary', 'tenant', 'staff'));
    }

    public function profile()
    {
        $staff = $this->getStaff(); // PEHLE
        $tenant = $staff->tenant;    // BAAD ME
        $user = Auth::user();

        return view('staff.profile.index', compact('user', 'staff', 'tenant'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($request->only('name', 'phone'));

        return back()->with('success', 'Profile updated successfully.');
    }
}
