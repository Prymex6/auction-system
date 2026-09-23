<?php

namespace App\Providers;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Message;
use App\Models\User;
use App\Observers\AuctionObserver;
use App\Observers\BidObserver;
use App\Observers\MessageObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auction::observe(AuctionObserver::class);
        User::observe(UserObserver::class);
        Bid::observe(BidObserver::class);
        Message::observe(MessageObserver::class);
    }
}
