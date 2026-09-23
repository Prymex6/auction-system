<?php

namespace App\Listeners;

use App\Events\BidPlaced;
use App\Services\NotificationContentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyBidPlaced implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private NotificationContentService $notificationService,
    ) {}

    public function handle(BidPlaced $event): void
    {
        $bid = $event->bid;

        // Notify previous highest bidder if outbid
        if ($bid->is_winning_bid) {
            $previousBid = $bid->auction->bids()
                ->where('id', '!=', $bid->id)
                ->orderByDesc('amount')
                ->first();

            if ($previousBid && $previousBid->user_id !== $bid->user_id) {
                $this->notificationService->notifyOutbid(
                    $previousBid->user,
                    $bid->auction,
                    $bid->amount
                );
            }
        }

        // Notify seller about bid
        $this->notificationService->sendNotification(
            $bid->auction->seller,
            'new_bid',
            'Nowa licytacja',
            "Nowa licytacja na '{$bid->auction->title}' - {$bid->amount} PLN",
            ['auction_id' => $bid->auction_id, 'bid_id' => $bid->id]
        );
    }
}
