<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->tenant_id === $appointment->tenant_id
            && $user->hasAnyRole(['owner', 'staff']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('owner');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->tenant_id === $appointment->tenant_id
            && $user->hasRole('owner');
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->tenant_id === $appointment->tenant_id
            && $user->hasRole('owner');
    }
}
