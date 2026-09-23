<?php

namespace App\Observers;

use App\Events\AuctionEnded;
use App\Models\Auction;

class AuctionObserver
{
    public function updated(Auction $auction): void
    {
        if ($auction->isDirty('status') && $auction->status === 'ended') {
            AuctionEnded::dispatch($auction);
        }
    }

    public function deleting(Auction $auction): void
    {
        // Cascade delete bids
        $auction->bids()->delete();
        $auction->payments()->delete();
    }
}
