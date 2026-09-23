<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;

class AuctionPolicy
{
    public function placeBid(User $user, Auction $auction): bool
    {
        return ! $user->is_banned &&
               $auction->status === 'active' &&
               $user->id !== $auction->user_id;
    }

    public function view(User $user, Auction $auction): bool
    {
        return $auction->status === 'active' ||
               $user->id === $auction->user_id ||
               $user->is_admin;
    }
}
