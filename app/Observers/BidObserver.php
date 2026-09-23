<?php

namespace App\Observers;

use App\Events\BidPlaced;
use App\Models\Bid;

class BidObserver
{
    public function created(Bid $bid): void
    {
        // Dispatch BidPlaced event for real-time notifications
        BidPlaced::dispatch($bid);
    }
}
