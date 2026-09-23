<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PlatformSetting;
use App\Models\User;

class NotificationContentService
{
    public function sendNotification(User $user, string $type, string $title, string $message, ?array $data = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function markAsRead(Notification $notification): bool
    {
        return $notification->update(['read_at' => now()]);
    }

    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function getUserNotifications(User $user, int $limit = 20): array
    {
        return $user->notifications()
            ->orderByDesc('created_at')
            ->take($limit)
            ->get()
            ->toArray();
    }

    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function deleteNotification(Notification $notification): bool
    {
        return $notification->delete();
    }

    public function deleteOldNotifications(int $daysOld = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }

    public function notifyAuctionCreated(User $user, $auction): void
    {
        $this->sendNotification(
            $user,
            'auction_created',
            'Nowa aukcja',
            "Aukcja '{$auction->title}' została utworzona",
            ['auction_id' => $auction->id]
        );
    }

    public function notifyOutbid(User $user, $auction, $amount): void
    {
        if (! (PlatformSetting::first()?->send_outbid_notification ?? true)) {
            return;
        }
        $this->sendNotification(
            $user,
            'outbid',
            'Przebity',
            "Zostałeś przebity w aukcji '{$auction->title}'. Nowa cena: {$amount} PLN",
            ['auction_id' => $auction->id]
        );
    }

    public function notifyAuctionEnded(User $user, $auction): void
    {
        if (! (PlatformSetting::first()?->send_auction_ending_notification ?? true)) {
            return;
        }
        $this->sendNotification(
            $user,
            'auction_ended',
            'Aukcja zakończyła się',
            "Aukcja '{$auction->title}' się zakończyła",
            ['auction_id' => $auction->id]
        );
    }

    public function notifyWonAuction(User $user, $auction): void
    {
        if (! (PlatformSetting::first()?->send_won_auction_notification ?? true)) {
            return;
        }
        $this->sendNotification(
            $user,
            'auction_won',
            'Wygrałeś aukcję!',
            "Congratulations! Wygrałeś aukcję '{$auction->title}' za {$auction->final_bid_amount} PLN",
            ['auction_id' => $auction->id]
        );
    }

    public function notifyListingApproved(User $user, $listing): void
    {
        $this->sendNotification(
            $user,
            'listing_approved',
            'Twoja aukcja została zaakceptowana',
            "'{$listing->title}' została zaakceptowana i jest teraz widoczna",
            ['auction_id' => $listing->id]
        );
    }

    public function notifyListingRejected(User $user, $listing, string $reason): void
    {
        $this->sendNotification(
            $user,
            'listing_rejected',
            'Twoja aukcja została odrzucona',
            "'{$listing->title}' została odrzucona. Powód: {$reason}",
            ['auction_id' => $listing->id]
        );
    }
}
