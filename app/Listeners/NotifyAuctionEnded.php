<?php

namespace App\Listeners;

use App\Events\AuctionEnded;
use App\Services\NotificationContentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAuctionEnded implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private NotificationContentService $notificationService,
    ) {}

    public function handle(AuctionEnded $event): void
    {
        $auction = $event->auction;

        // Notify winner
        if ($auction->winner_id) {
            $this->notificationService->notifyWonAuction(
                $auction->winner()->first(),
                $auction
            );
        }

        // Notify seller
        $this->notificationService->notifyAuctionEnded(
            $auction->seller,
            $auction
        );
    }
}
