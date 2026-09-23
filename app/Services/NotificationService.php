<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Auction;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public static function accountActivated(User $user): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'account_activated',
            'title' => '✅ Twoje konto zostało aktywowane',
            'message' => 'Twoje konto na platformie aukcji gołębi zostało pomyślnie aktywowane. Możesz teraz wystawiać aukcje i brać w nich udział!',
            'read' => false,
        ]);

        broadcast(new NotificationCreated($notification, $user))->toOthers();

        return $notification;
    }

    public static function auctionActivated(Auction $auction): Notification
    {
        return Notification::create([
            'user_id' => $auction->user_id,
            'type' => 'auction_activated',
            'title' => '🎉 Twoja aukcja została aktywowana',
            'message' => 'Aukcja "'.$auction->title.'" jest teraz aktywna i widoczna dla innych użytkowników.',
            'auction_id' => $auction->id,
            'read' => false,
        ]);
    }

    public static function priceChanged(Auction $auction, float $oldPrice, float $newPrice): Notification
    {
        return Notification::create([
            'user_id' => $auction->user_id,
            'type' => 'price_changed',
            'title' => '💰 Zmiana ceny w aukcji "Kup teraz"',
            'message' => 'Cena w aukcji "'.$auction->title.'" zmieniła się z '.number_format($oldPrice, 2).' zł na '.number_format($newPrice, 2).' zł',
            'auction_id' => $auction->id,
            'read' => false,
        ]);
    }

    public static function newBidInYourAuction(Auction $auction, User $bidder): Notification
    {
        return Notification::create([
            'user_id' => $auction->user_id,
            'type' => 'new_bid_in_your_auction',
            'title' => '🔥 Nowa oferta w Twojej aukcji!',
            'message' => 'W aukcji "'.$auction->title.'" pojawiła się nowa oferta od '.$bidder->name.' na kwotę '.number_format((float) $auction->current_price, 2).' zł',
            'auction_id' => $auction->id,
            'related_user_id' => $bidder->id,
            'read' => false,
        ]);
    }

    public static function newBidInWatchlistAuction(Auction $auction, User $bidder, User $watcher): Notification
    {
        return Notification::create([
            'user_id' => $watcher->id,
            'type' => 'new_bid_in_watchlist_auction',
            'title' => '🔥 Nowa oferta w obserwowanej aukcji!',
            'message' => 'W aukcji "'.$auction->title.'" pojawiła się nowa oferta na kwotę '.number_format((float) $auction->current_price, 2).' zł',
            'auction_id' => $auction->id,
            'related_user_id' => $bidder->id,
            'read' => false,
        ]);
    }

    public static function outbid(Auction $auction, User $bidder): Notification
    {
        return Notification::create([
            'user_id' => $bidder->id,
            'type' => 'outbid',
            'title' => '⚠️ Przebito Twoją ofertę!',
            'message' => 'W aukcji "'.$auction->title.'" została postawiona wyższa oferta. Obecna najwyższa stawka to '.number_format((float) $auction->current_price, 2).' zł',
            'auction_id' => $auction->id,
            'read' => false,
        ]);
    }

    public static function auctionWon(Auction $auction): Notification
    {
        return Notification::create([
            'user_id' => $auction->winner_id,
            'type' => 'auction_won',
            'title' => '🏆 Wygrałeś aukcję!',
            'message' => 'Gratulacje! Wygrałeś aukcję "'.$auction->title.'" za kwotę '.number_format((float) $auction->current_price, 2).' zł. Czekamy na Twoją wpłatę.',
            'auction_id' => $auction->id,
            'read' => false,
        ]);
    }

    public static function auctionExtended(Auction $auction): Notification
    {
        return Notification::create([
            'user_id' => $auction->user_id,
            'type' => 'auction_extended',
            'title' => '⏱️ Aukcja została przedłużona',
            'message' => 'Twoja aukcja "'.$auction->title.'" została automatycznie przedłużona o kilka minut.',
            'auction_id' => $auction->id,
            'read' => false,
        ]);
    }

    public static function markAsRead(Notification $notification): void
    {
        $notification->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    public static function markAllAsRead(User $user): void
    {
        $user->notifications()
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
    }
}
