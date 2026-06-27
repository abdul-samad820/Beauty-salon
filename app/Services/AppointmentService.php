<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Service;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function create(array $data): Appointment
    {
        return DB::transaction(function () use ($data) {

            $service = Service::findOrFail(
                $data['service_id']
            );

            $startTime = Carbon::parse(
                $data['start_time']
            );

            $endTime = $startTime
                ->copy()
                ->addMinutes(
                    $service->duration_minutes
                );

            $conflict = Appointment::lockForUpdate()
                ->where('tenant_id', $data['tenant_id'])
                ->where('staff_id', $data['staff_id'])
                ->whereDate(
                    'appointment_date',
                    $data['appointment_date']
                )
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($q) use ($data, $endTime) {

                    $q->where(
                        'start_time',
                        '<',
                        $endTime->format('H:i')
                    )
                        ->where(
                            'end_time',
                            '>',
                            $data['start_time']
                        );
                })
                ->exists();

            if ($conflict) {
                throw new \RuntimeException(
                    'STAFF_ALREADY_BOOKED'
                );
            }

            $subscriptionActive = Subscription::where('tenant_id', $data['tenant_id'])
                            ->whereIn('status', ['active', 'trial'])
                            ->where('expires_at', '>', now())
                            ->exists();

            if (! $subscriptionActive) {
                throw new \RuntimeException(
                    'TENANT_SUBSCRIPTION_EXPIRED'
                );
            }
            // Plan limit check
            $subscription = Subscription::with('plan')
                ->where('tenant_id', $data['tenant_id'])
                ->whereIn('status', ['active', 'trial'])
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($subscription && $subscription->plan) {
                $currentMonthCount = Appointment::where('tenant_id', $data['tenant_id'])
                    ->whereMonth('appointment_date', now()->month)
                    ->whereYear('appointment_date', now()->year)
                    ->whereNotIn('status', ['cancelled'])
                    ->count();

                if ($currentMonthCount >= $subscription->plan->max_appointments_per_month) {
                    throw new \RuntimeException(
                        'PLAN_APPOINTMENT_LIMIT_REACHED'
                    );
                }
            }

            // to:
            $appointment = Appointment::create([
                'tenant_id' => $data['tenant_id'],
                'customer_id' => $data['customer_id'],
                'staff_id' => $data['staff_id'],
                'service_id' => $data['service_id'],
                'amount' => $service->price,
                'appointment_date' => $data['appointment_date'],
                'start_time' => $data['start_time'],
                'end_time' => $endTime->format('H:i'),
                'status' => $data['status'] ?? 'confirmed',
                'notes' => $data['notes'] ?? null,
                'reminder_sent' => false,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'not_required',
            ]);

            AuditLog::record(
                'appointment.booked',
                Appointment::class,
                $appointment->id,
                [
                    'customer_name' => $appointment->customer?->name,
                    'service_name' => $service->name,
                    'date' => $data['appointment_date'],
                    'time' => $data['start_time'],
                ],
                $data['tenant_id'],
                'booking'
            );

            return $appointment;
        });
    }
}
