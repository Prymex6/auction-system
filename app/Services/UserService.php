<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getUserStats(User $user): array
    {
        return [
            'total_auctions' => $user->auctions()->count(),
            'won_auctions' => $user->bids()
                ->where('is_winning_bid', true)
                ->distinct('auction_id')
                ->count(),
            'total_bids' => $user->bids()->count(),
            'active_auctions' => $user->auctions()
                ->where('status', 'active')
                ->count(),
            'seller_rating' => round($user->reviews()->where('to_user_id', $user->id)->avg('rating') ?? 0, 2),
            'total_spent' => $user->bids()
                ->where('is_winning_bid', true)
                ->sum('amount'),
            'total_earned' => $user->auctions()
                ->where('status', 'ended')
                ->sum('final_bid_amount'),
            'member_since' => $user->created_at->format('Y-m-d'),
        ];
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return false;
        }

        return $user->update(['password' => Hash::make($newPassword)]);
    }

    public function updateProfile(User $user, array $data): bool
    {
        $allowedFields = ['name', 'bio', 'phone', 'address', 'city', 'postcode', 'country'];
        $updateData = array_filter($data, fn ($key) => in_array($key, $allowedFields), ARRAY_FILTER_USE_KEY);

        return $user->update($updateData);
    }

    public function updateAvatar(User $user, string $avatarPath): bool
    {
        return $user->update(['avatar' => $avatarPath]);
    }

    public function getLoginHistory(User $user, int $limit = 20): array
    {
        return $user->loginHistories()
            ->latest()
            ->take($limit)
            ->get()
            ->toArray();
    }

    public function isPremium(User $user): bool
    {
        if (! $user->premium_until) {
            return false;
        }

        return $user->premium_until->isFuture();
    }

    public function getRemainingPremiumDays(User $user): int
    {
        if (! $this->isPremium($user)) {
            return 0;
        }

        return now()->diffInDays($user->premium_until);
    }

    public function canListAuction(User $user): bool
    {
        // Get limits from platform settings
        $settings = PlatformSetting::first();

        if ($this->isPremium($user)) {
            $limit = $settings?->premium_user_auction_limit ?? 999;
            $activeAuctions = $user->auctions()
                ->where('status', 'active')
                ->count();

            return $activeAuctions < $limit;
        }

        $limit = $settings?->free_user_auction_limit ?? 5;
        $activeAuctions = $user->auctions()
            ->where('status', 'active')
            ->count();

        return $activeAuctions < $limit;
    }
}
