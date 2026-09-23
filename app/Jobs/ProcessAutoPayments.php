<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAutoPayments implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Get all users with automatic payment enabled and winning bids
        $users = User::where('auto_payment_enabled', true)->get();

        foreach ($users as $user) {
            $winningBids = $user->bids()
                ->where('is_winning_bid', true)
                ->whereHas('auction', fn ($q) => $q->where('status', 'ended'))
                ->whereDoesntHave('payment')
                ->get();

            foreach ($winningBids as $bid) {
                // Create payment record
                $bid->auction->payments()->create([
                    'user_id' => $user->id,
                    'bid_id' => $bid->id,
                    'amount' => $bid->amount,
                    'status' => 'pending',
                    'payment_method' => 'automatic',
                ]);
            }
        }
    }
}
