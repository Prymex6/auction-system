<?php

namespace App\Providers;

use App\Events\AuctionEnded;
use App\Events\BidPlaced;
use App\Listeners\NotifyAuctionEnded;
use App\Listeners\NotifyBidPlaced;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AuctionEnded::class => [
            NotifyAuctionEnded::class,
        ],
        BidPlaced::class => [
            NotifyBidPlaced::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
