<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\Commission;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    /*
    |----------------------------------------------------------
    | Appointment update hone par ye chalega
    | Status "completed" hone par:
    | 1. Commission calculate hogi
    | 2. Low stock check hoga
    |----------------------------------------------------------
    */
    public function updated(Appointment $appointment): void
    {
        // Sirf tab kaam karo jab status "completed" hua ho
        if (! $appointment->wasChanged('status')) {
            return;
        }

        if ($appointment->status !== 'completed') {
            return;
        }

        // Commission pehle se bani hai? Dobara mat banao
        $alreadyExists = Commission::where('appointment_id', $appointment->id)->exists();
        if ($alreadyExists) {
            return;
        }

        $this->calculateCommission($appointment);
        $this->checkLowStock($appointment);
    }

    /*
    |----------------------------------------------------------
    | Commission Calculate karo
    |----------------------------------------------------------
    */
    private function calculateCommission(Appointment $appointment): void
    {
        $service = $appointment->service;
        $staff = $appointment->staff;

        // Commission amount nikalo
        // Priya ka 30% commission + Hair Spa ₹999
        // Commission = 999 * 30 / 100 = ₹299.70
        $commissionAmount = ($service->price * $staff->commission_percent) / 100;

        Commission::create([
            'tenant_id' => $appointment->tenant_id,
            'staff_id' => $staff->id,
            'appointment_id' => $appointment->id,
            'service_price' => $service->price,
            'commission_percent' => $staff->commission_percent,
            'commission_amount' => $commissionAmount,
            'status' => 'pending',
        ]);

        Log::info('Commission calculated', [
            'staff' => $staff->user->name,
            'service' => $service->name,
            'service_price' => $service->price,
            'commission_percent' => $staff->commission_percent.'%',
            'commission_amount' => '₹'.$commissionAmount,
            'appointment_id' => $appointment->id,
        ]);
    }

    /*
    |----------------------------------------------------------
    | Low Stock Check karo
    |----------------------------------------------------------
    */

}
