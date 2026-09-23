<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return ! $user->is_banned;
    }

    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->from_user_id && $review->created_at->diffInDays(now()) < 30;
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->from_user_id || $user->is_admin;
    }

    public function view(User $user, Review $review): bool
    {
        return true; // Reviews are public
    }
}
