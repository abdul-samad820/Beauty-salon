<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function create(array $data): Appointment
    {
        return DB::transaction(function () use ($data) {

            $service = Service::findOrFail($data['service_id']);

            $staff = Staff::with('tenant')->findOrFail($data['staff_id']);

            $tenantTz = $staff->tenant?->settings['timezone']
                     ?? config('app.timezone', 'UTC');

            $workingHours = $staff->working_hours ?? [];
            $dayName = strtolower(
                Carbon::parse($data['appointment_date'])->format('D') // mon, tue...
            );

            if (empty($workingHours[$dayName])) {
                throw new \RuntimeException('STAFF_NOT_WORKING_THIS_DAY');
            }

            $tenantWorkingHours = $staff->tenant?->settings['working_hours'][$dayName] ?? null;

            if (empty($tenantWorkingHours)) {
                throw new \RuntimeException('SALON_CLOSED_THIS_DAY');
            }

            [$tenantOpen, $tenantClose] = explode('-', $tenantWorkingHours);
            $tenantOpenTime = Carbon::parse($data['appointment_date'].' '.$tenantOpen, $tenantTz);
            $tenantCloseTime = Carbon::parse($data['appointment_date'].' '.$tenantClose, $tenantTz);

            [$workStart, $workEnd] = explode('-', $workingHours[$dayName]);

            $shiftStart = Carbon::parse($data['appointment_date'].' '.$workStart, $tenantTz);
            $shiftEnd = Carbon::parse($data['appointment_date'].' '.$workEnd, $tenantTz);

            $shiftStart = $shiftStart->max($tenantOpenTime);
            $shiftEnd = $shiftEnd->min($tenantCloseTime);

            $startTime = Carbon::parse($data['appointment_date'].' '.$data['start_time'], $tenantTz);
            $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

            if ($shiftStart->gte($shiftEnd) || $startTime->lt($shiftStart) || $endTime->gt($shiftEnd)) {
                throw new \RuntimeException('SLOT_OUTSIDE_WORKING_HOURS');
            }

            // ── Step 3: Slot conflict check (race condition safe) ──────

            $conflict = Appointment::lockForUpdate()
                ->where('tenant_id', $data['tenant_id'])
                ->where('staff_id', $data['staff_id'])
                ->whereDate('appointment_date', $data['appointment_date'])
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($q) {
                    $q->where('payment_method', '!=', 'razorpay')
                        ->orWhere('payment_status', 'paid');
                })
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime->format('H:i:s'))
                        ->where('end_time', '>', $startTime->format('H:i:s'));
                })
                ->exists();

            if ($conflict) {
                throw new \RuntimeException('STAFF_ALREADY_BOOKED');
            }

            // ── Step 4: Subscription check (single query) ─────────────
            $subscription = Subscription::with('plan')
                ->where('tenant_id', $data['tenant_id'])
                ->whereIn('status', ['active', 'trial'])
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (! $subscription) {
                throw new \RuntimeException('TENANT_SUBSCRIPTION_EXPIRED');
            }

            // ── Step 5: Plan appointment limit check ───────────────────
            if ($subscription->plan) {

                $currentMonthCount = Appointment::where('tenant_id', $data['tenant_id'])
                    ->whereMonth('appointment_date', now()->month)
                    ->whereYear('appointment_date', now()->year)
                    ->whereNotIn('status', ['cancelled'])
                    ->where(function ($q) {
                        $q->where('payment_method', '!=', 'razorpay')
                            ->orWhere('payment_status', 'paid');
                    })
                    ->count();

                if ($currentMonthCount >= $subscription->plan->max_appointments_per_month) {
                    throw new \RuntimeException('PLAN_APPOINTMENT_LIMIT_REACHED');
                }
            }

            // ── Step 6: Create appointment ────────────────────────────
            $appointment = Appointment::create([
                'tenant_id' => $data['tenant_id'],
                'customer_id' => $data['customer_id'],
                'staff_id' => $data['staff_id'],
                'service_id' => $data['service_id'],
                'amount' => $service->price,
                'appointment_date' => $data['appointment_date'],
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'status' => $data['status'] ?? 'confirmed',
                'notes' => $data['notes'] ?? null,
                'reminder_sent' => false,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'not_required',
            ]);

            return $appointment;
        });
    }
}
