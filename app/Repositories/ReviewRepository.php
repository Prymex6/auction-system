<?php

namespace App\Repositories;

use App\Models\Auction;
use App\Models\Review;
use App\Models\User;

class ReviewRepository
{
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    public function update(Review $review, array $data): bool
    {
        return $review->update($data);
    }

    public function delete(Review $review): bool
    {
        return $review->delete();
    }

    public function getById(int $id): ?Review
    {
        return Review::with(['from', 'to', 'auction'])->find($id);
    }

    public function getByUser(User $user, int $perPage = 15)
    {
        return Review::where('to_user_id', $user->id)
            ->with(['from', 'auction'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getByReviewer(User $user, int $perPage = 15)
    {
        return Review::where('from_user_id', $user->id)
            ->with(['to', 'auction'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getForAuction(Auction $auction): array
    {
        return Review::where('auction_id', $auction->id)
            ->with(['from', 'to'])
            ->get()
            ->toArray();
    }

    public function hasReviewed(User $reviewer, Auction $auction): bool
    {
        return Review::where('from_user_id', $reviewer->id)
            ->where('auction_id', $auction->id)
            ->exists();
    }

    public function getAverageRating(User $user): float
    {
        return Review::where('to_user_id', $user->id)
            ->avg('rating') ?? 0;
    }

    public function getStats(User $user): array
    {
        $reviews = Review::where('to_user_id', $user->id)->get();

        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => $reviews->avg('rating') ?? 0,
            'rating_distribution' => [
                5 => $reviews->where('rating', 5)->count(),
                4 => $reviews->where('rating', 4)->count(),
                3 => $reviews->where('rating', 3)->count(),
                2 => $reviews->where('rating', 2)->count(),
                1 => $reviews->where('rating', 1)->count(),
            ],
        ];
    }
}
