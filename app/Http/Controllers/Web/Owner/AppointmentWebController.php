<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AppointmentWebController extends Controller
{
    /**
     * Display a paginated list of all appointments.
     */
    public function index(Request $request)
    {
        $tenant = app('currentTenant');

        $query = Appointment::with(['customer', 'staff.user', 'service'])
            ->where('tenant_id', $tenant->id);

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }
        if ($request->filled('search')) {
            $query->whereHas('customer', fn ($q) => $q->where('name', 'like', "%{$request->search}%")
            );
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(20)
            ->withQueryString();

        $staffList = Staff::with('user')->where('tenant_id', $tenant->id)->get();

        $customers = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
            ->orderBy('name')
            ->get();

        $activeServices = Service::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $availableStaff = Staff::with('user')
            ->where('tenant_id', $tenant->id)
            ->where('is_available', true)
            ->get();

        $stats = Cache::remember(
            "appointment_stats_{$tenant->id}", 300,
            fn () => Appointment::where('tenant_id', $tenant->id)
                ->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN DATE(appointment_date)=CURDATE() THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status='no_show' THEN 1 ELSE 0 END) as no_show
        ")
                ->first()
                ->toArray()
        );

        return view('owner.appointments.index', compact(
            'appointments',
            'staffList',
            'stats',
            'customers',
            'activeServices',
            'availableStaff'
        ));
    }

    /**
     * Display today's appointments.
     */
    public function today()
    {
        $tenant = app('currentTenant');

        $appointments = Appointment::with(['customer', 'staff.user', 'service'])
            ->where('tenant_id', $tenant->id)
            ->whereDate('appointment_date', Carbon::today())
            ->orderBy('start_time')
            ->get();

        $stats = [
            'total' => $appointments->count(),
            'pending' => $appointments->whereIn('status', ['pending', 'confirmed'])->count(),
            'checked_in' => $appointments->where('status', 'checked_in')->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'no_show' => $appointments->where('status', 'no_show')->count(),
        ];

        $date = Carbon::today()->format('l, d F Y');

        return view('owner.appointments.today', compact('appointments', 'stats', 'date'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $tenant = app('currentTenant');
        $services = Service::where('tenant_id', $tenant->id)->where('is_active', true)->get();
        $staffList = Staff::with('user')->where('tenant_id', $tenant->id)->where('is_available', true)->get();
        $customers = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'customer')->where('guard_name', 'customer'))
            ->get();

        return view('owner.appointments.create', compact('services', 'staffList', 'customers'));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $tenant = app('currentTenant');

        $plan = Plan::where('slug', $tenant->plan)->first();
        $currentMonthAppointments = Appointment::where('tenant_id', $tenant->id)
            ->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->count();

        if ($plan && $currentMonthAppointments >= $plan->max_appointments_per_month) {
            return back()->withErrors(['limit' => 'Monthly appointment limit reached for your current plan. Please upgrade to book more appointments.']);
        }

        $validated = $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where('tenant_id', $tenant->id),
            ],
            'staff_id' => [
                'required',
                Rule::exists('staff', 'id')
                    ->where('tenant_id', $tenant->id),
            ],
            'service_id' => [
                'required',
                Rule::exists('services', 'id')
                    ->where('tenant_id', $tenant->id),
            ],
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        // Calculate end time based on service duration
        $service = Service::where('tenant_id', $tenant->id)
            ->findOrFail($validated['service_id']);
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes ?? 60);

        // Check for schedule conflicts
        try {
            DB::transaction(function () use ($tenant, $validated, $service, $endTime) {
                // lockForUpdate() ke saath conflict check — race condition safe
                $conflictExists = Appointment::where('tenant_id', $tenant->id)
                    ->where('staff_id', $validated['staff_id'])
                    ->whereDate('appointment_date', $validated['appointment_date'])
                    ->whereNotIn('status', ['cancelled', 'completed', 'no_show'])
                    ->where(function ($query) use ($validated, $endTime) {
                        $query->whereBetween('start_time', [
                            $validated['start_time'],
                            $endTime->format('H:i'),
                        ])
                            ->orWhereBetween('end_time', [
                                $validated['start_time'],
                                $endTime->format('H:i'),
                            ])
                            ->orWhere(function ($q) use ($validated, $endTime) {
                                $q->where('start_time', '<=', $validated['start_time'])
                                    ->where('end_time', '>=', $endTime->format('H:i'));
                            });
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($conflictExists) {
                    throw new \RuntimeException('STAFF_ALREADY_BOOKED');
                }

                Appointment::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $validated['customer_id'],
                    'staff_id' => $validated['staff_id'],
                    'service_id' => $validated['service_id'],
                    'amount' => $service->price,
                    'appointment_date' => $validated['appointment_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $endTime->format('H:i'),
                    'status' => 'confirmed',
                    'notes' => $validated['notes'],
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'start_time' => 'The selected staff member is already booked during this time slot.',
                ]);
        }

        // Flush dashboard & appointment stats cache so new booking reflects immediately
        Cache::forget("dashboard_stats_{$tenant->id}");
        Cache::forget("dashboard_revenue_{$tenant->id}");
        Cache::forget("appointment_stats_{$tenant->id}");

        return redirect()
            ->route('owner.appointments.today')
            ->with('success', 'Appointment successfully booked!');
    }

    /**
     * Update the status of an appointment.
     */
    public function updateStatus(Request $request, $id)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'checked_in', 'completed', 'cancelled', 'no_show'])],
        ]);

        $appointment = Appointment::where('tenant_id', $tenant->id)->findOrFail($id);

        $this->authorize('update', $appointment);

        if (in_array($appointment->status, ['cancelled', 'completed', 'no_show'])) {
            return back()->with('error', 'A closed, cancelled, or no-show appointment cannot be updated.');
        }

        $appointment->update(['status' => $request->status]);

        // Flush dashboard cache so updated status reflects immediately
        Cache::forget("dashboard_stats_{$tenant->id}");
        Cache::forget("appointment_stats_{$tenant->id}");

        return back()->with('success', 'Status updated successfully: '.ucfirst($request->status));
    }

    public function export(Request $request)
    {
        $tenant = app('currentTenant');

        $query = Appointment::with(['customer', 'staff.user', 'service'])
            ->where('tenant_id', $tenant->id);

        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->get();

        $filename = 'appointments-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($appointments) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Booking Ref', 'Customer', 'Service', 'Staff',
                'Date', 'Time', 'Status', 'Payment Method', 'Amount (₹)',
            ]);

            foreach ($appointments as $a) {
                fputcsv($file, [
                    '#LMR-'.str_pad($a->id, 5, '0', STR_PAD_LEFT),
                    $a->customer?->name ?? 'N/A',
                    $a->service?->name ?? 'N/A',
                    $a->staff?->user?->name ?? 'N/A',
                    Carbon::parse($a->appointment_date)->format('d M Y'),
                    Carbon::parse($a->start_time)->format('h:i A'),
                    ucfirst($a->status),
                    strtoupper($a->payment_method ?? 'cash'),
                    number_format($a->amount, 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
