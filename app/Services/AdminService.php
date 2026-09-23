<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminService
{
    public function banUser(User $user, string $reason, ?int $durationDays = null): bool
    {
        $bannedUntil = $durationDays ? now()->addDays($durationDays) : null;

        return $user->update([
            'is_banned' => true,
            'ban_reason' => $reason,
            'ban_until' => $bannedUntil,
        ]);
    }

    public function unbanUser(User $user): bool
    {
        return $user->update([
            'is_banned' => false,
            'ban_reason' => null,
            'ban_until' => null,
        ]);
    }

    public function checkBannedUsers(): int
    {
        $unbanCount = 0;

        User::where('is_banned', true)
            ->where('ban_until', '<', now())
            ->whereNotNull('ban_until')
            ->each(function ($user) use (&$unbanCount) {
                $this->unbanUser($user);
                $unbanCount++;
            });

        return $unbanCount;
    }

    public function deleteAuction(Auction $auction, string $reason): bool
    {
        DB::transaction(function () use ($auction, $reason) {
            $auction->update(['status' => 'cancelled', 'rejection_reason' => $reason]);
            $auction->delete();
        });

        return true;
    }

    public function approveMessage(Message $message): bool
    {
        return $message->update(['is_approved' => true]);
    }

    public function rejectMessage(Message $message, string $reason): bool
    {
        return $message->update([
            'is_approved' => false,
            'rejection_reason' => $reason,
        ]);
    }

    public function getSystemStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_banned', false)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
            'total_auctions' => Auction::count(),
            'active_auctions' => Auction::where('status', 'active')->count(),
            'pending_auctions' => Auction::where('status', 'pending')->count(),
            'completed_auctions' => Auction::where('status', 'ended')->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'total_messages' => Message::count(),
        ];
    }

    public function getRevenueStats(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'today' => $this->calculateRevenue($today),
            'this_week' => $this->calculateRevenue($thisWeek),
            'this_month' => $this->calculateRevenue($thisMonth),
            'all_time' => $this->calculateRevenue(null),
        ];
    }

    private function calculateRevenue($startDate = null): float
    {
        $query = Auction::where('status', 'ended');

        if ($startDate) {
            $query->where('ended_at', '>=', $startDate);
        }

        // Assuming 5% commission on each auction final price
        return round(($query->sum('final_bid_amount') ?? 0) * 0.05, 2);
    }

    public function exportUserData(User $user): array
    {
        return [
            'user' => $user->toArray(),
            'auctions' => $user->auctions()->get()->toArray(),
            'bids' => $user->bids()->get()->toArray(),
            'reviews' => $user->reviews()->get()->toArray(),
            'messages' => $user->sentMessages()->get()->toArray(),
        ];
    }
}
