<?php

namespace App\Repositories;

use App\Models\Auction;
use App\Repositories\Contracts\AuctionRepositoryInterface;

class AuctionRepository implements AuctionRepositoryInterface
{
    public function getActive(int $limit = 20, int $page = 1)
    {
        return Auction::where('status', 'active')
            ->where('ends_at', '>', now())
            ->with(['highestBidder'])
            ->withCount('bids')
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function getByStatus(string $status, int $limit = 20, int $page = 1)
    {
        return Auction::where('status', $status)
            ->with(['highestBidder'])
            ->withCount('bids')
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function getBySeller(int $sellerId, int $limit = 20, int $page = 1)
    {
        return Auction::where('user_id', $sellerId)
            ->with(['highestBidder'])
            ->withCount('bids')
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function getEndingSoon(int $minutes = 60, int $limit = 20)
    {
        $soon = now()->addMinutes($minutes);

        return Auction::where('status', 'active')
            ->where(function ($query) use ($soon) {
                $query->whereNotNull('ends_at')
                    ->where('ends_at', '<=', $soon)
                    ->where('ends_at', '>', now());
            })
            ->with(['highestBidder'])
            ->withCount('bids')
            ->orderBy('ends_at')
            ->limit($limit)
            ->get();
    }

    public function search(string $query, array $filters = [], int $limit = 20, int $page = 1)
    {
        $search = Auction::query();

        // Search in auction title/description
        $search->where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
        });

        // Apply filters
        if (! empty($filters['status'])) {
            $search->where('status', $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $search->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['min_price'])) {
            $search->where('start_price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $search->where('current_price', '<=', $filters['max_price']);
        }

        return $search->with(['highestBidder'])
            ->withCount('bids')
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function getById(int $id)
    {
        return Auction::with([
            'bids' => fn ($q) => $q->orderByDesc('amount'),
            'highestBidder',
        ])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Auction::create($data);
    }

    public function update(int $id, array $data)
    {
        $auction = Auction::findOrFail($id);
        $auction->update($data);

        return $auction;
    }

    public function closeAuction(int $id)
    {
        $auction = Auction::findOrFail($id);

        // Get highest bidder
        $highestBid = $auction->bids()->orderByDesc('amount')->first();

        if ($highestBid) {
            $auction->bids()->update(['is_winning_bid' => false]);
            $highestBid->update(['is_winning_bid' => true]);
            $auction->update([
                'status' => 'ended',
                'ended_at' => now(),
                'winner_id' => $highestBid->user_id,
                'final_bid_amount' => $highestBid->amount,
                'current_price' => $highestBid->amount,
            ]);

            return $highestBid->user;
        }

        // No bids, auction failed
        $auction->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        return null;
    }
}
