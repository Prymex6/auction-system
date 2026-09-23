<?php

namespace App\Repositories\Contracts;

interface BidRepositoryInterface
{
    /**
     * Get bids for auction
     */
    public function getByAuction(int $auctionId, int $limit = 20, int $page = 1);

    /**
     * Get highest bid for auction
     */
    public function getHighestBid(int $auctionId);

    /**
     * Get user bids
     */
    public function getUserBids(int $userId, int $limit = 20, int $page = 1);

    /**
     * Create bid
     */
    public function create(array $data);

    /**
     * Get auto-bids for auction
     */
    public function getAutoBids(int $auctionId);
}
