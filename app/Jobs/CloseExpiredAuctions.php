<?php

namespace App\Jobs;

use App\Models\Auction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CloseExpiredAuctions implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $expiredAuctions = Auction::where('status', 'active')
            ->where('type', '!=', 'buy_now')
            ->where('ends_at', '<', now())
            ->get();

        foreach ($expiredAuctions as $auction) {
            $auction->update(['status' => 'ended', 'ended_at' => now()]);

            // If there's a winning bid, set winner
            $winningBid = $auction->bids()
                ->orderByDesc('amount')
                ->first();

            if ($winningBid) {
                $auction->bids()->update(['is_winning_bid' => false]);
                $winningBid->update(['is_winning_bid' => true]);
                $auction->update([
                    'winner_id' => $winningBid->user_id,
                    'final_bid_amount' => $winningBid->amount,
                ]);
            }
        }
    }
}
