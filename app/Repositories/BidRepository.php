<?php

namespace App\Repositories;

use App\Models\Bid;
use App\Repositories\Contracts\BidRepositoryInterface;

class BidRepository implements BidRepositoryInterface
{
    public function getByAuction(int $auctionId, int $limit = 20, int $page = 1)
    {
        return Bid::where('auction_id', $auctionId)
            ->with(['user', 'auction'])
            ->orderByDesc('amount')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function getHighestBid(int $auctionId)
    {
        return Bid::where('auction_id', $auctionId)
            ->orderByDesc('amount')
            ->with('user')
            ->first();
    }

    public function getUserBids(int $userId, int $limit = 20, int $page = 1)
    {
        return Bid::where('user_id', $userId)
            ->with(['auction', 'auction.highestBidder'])
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function create(array $data)
    {
        return Bid::create($data);
    }

    public function getAutoBids(int $auctionId)
    {
        return Bid::where('auction_id', $auctionId)
            ->where('is_auto_bid', true)
            ->where('auto_bid_status', 'active')
            ->with('user')
            ->get();
    }
}
