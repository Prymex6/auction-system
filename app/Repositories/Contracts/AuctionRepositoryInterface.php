<?php

namespace App\Repositories\Contracts;

interface AuctionRepositoryInterface
{
    /**
     * Get active auctions
     */
    public function getActive(int $limit = 20, int $page = 1);

    /**
     * Get auctions by status
     */
    public function getByStatus(string $status, int $limit = 20, int $page = 1);

    /**
     * Get auctions by seller
     */
    public function getBySeller(int $sellerId, int $limit = 20, int $page = 1);

    /**
     * Get auctions ending soon
     */
    public function getEndingSoon(int $minutes = 60, int $limit = 20);

    /**
     * Search auctions
     */
    public function search(string $query, array $filters = [], int $limit = 20, int $page = 1);

    /**
     * Get auction by ID with relationships
     */
    public function getById(int $id);

    /**
     * Create auction
     */
    public function create(array $data);

    /**
     * Update auction
     */
    public function update(int $id, array $data);

    /**
     * Close auction
     */
    public function closeAuction(int $id);
}
