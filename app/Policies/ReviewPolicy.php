<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function approve(User $user, Review $review): bool
    {
        return $user->tenant_id === $review->tenant_id
            && $user->hasRole('owner');
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->tenant_id === $review->tenant_id
            && $user->hasRole('owner');
    }
}
