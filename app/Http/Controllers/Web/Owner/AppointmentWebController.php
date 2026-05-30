<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentWebController extends Controller
{
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

        $stats = [
            'total' => Appointment::where('tenant_id', $tenant->id)->count(),
            'today' => Appointment::where('tenant_id', $tenant->id)->whereDate('appointment_date', today())->count(),
            'pending' => Appointment::where('tenant_id', $tenant->id)->where('status', 'pending')->count(),
            'completed' => Appointment::where('tenant_id', $tenant->id)->where('status', 'completed')->count(),
        ];

        return view('owner.appointments.index', compact('appointments', 'staffList', 'stats'));
    }

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
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
        ];

        return view('owner.appointments.today', compact('appointments', 'stats'));
    }

    public function create()
    {
        $tenant = app('currentTenant');
        $services = Service::where('tenant_id', $tenant->id)->where('is_active', true)->get();
        $staffList = Staff::with('user')->where('tenant_id', $tenant->id)->where('is_available', true)->get();
        $customers = User::where('tenant_id', $tenant->id)->role('customer')->get();

        return view('owner.appointments.create', compact('services', 'staffList', 'customers'));
    }

    public function store(Request $request)
    {
        $tenant = app('currentTenant');

        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'staff_id' => 'required|exists:staff,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        // End time calculate karo service duration se
        $service = Service::find($validated['service_id']);
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes ?? 60);

        Appointment::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $validated['customer_id'],
            'staff_id' => $validated['staff_id'],
            'service_id' => $validated['service_id'],
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $endTime->format('H:i'),
            'status' => 'confirmed',
            'notes' => $validated['notes'],
        ]);

        return redirect()
            ->route('owner.appointments.today')
            ->with('success', 'Appointment successfully book ho gaya!');
    }

    public function updateStatus(Request $request, $id)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'completed', 'cancelled'])],
        ]);

        $appointment = Appointment::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($appointment->status === 'cancelled') {
            return back()->with('error', 'Cancelled appointment update nahi ho sakta.');
        }

        $appointment->update(['status' => $request->status]);

        return back()->with('success', 'Status update ho gaya: '.ucfirst($request->status));
    }
}
