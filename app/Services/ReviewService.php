<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Review;
use App\Models\User;

class ReviewService
{
    public function canReviewAuction(User $user, Auction $auction): bool
    {
        // User can review if they won the auction
        return $auction->winner_id === $user->id && $auction->status === 'ended';
    }

    public function createReview(User $reviewer, Auction $auction, int $rating, ?string $comment = null): Review
    {
        return Review::create([
            'from_user_id' => $reviewer->id,
            'to_user_id' => $auction->user_id,
            'auction_id' => $auction->id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    public function updateReview(Review $review, int $rating, ?string $comment = null): bool
    {
        return $review->update([
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    public function deleteReview(Review $review): bool
    {
        return $review->delete();
    }

    public function getAverageRating(User $user): float
    {
        return Review::where('to_user_id', $user->id)
            ->avg('rating') ?? 0;
    }

    public function getReviewStats(User $user): array
    {
        $reviews = Review::where('to_user_id', $user->id)->get();

        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => $reviews->avg('rating') ?? 0,
            'five_star' => $reviews->where('rating', 5)->count(),
            'four_star' => $reviews->where('rating', 4)->count(),
            'three_star' => $reviews->where('rating', 3)->count(),
            'two_star' => $reviews->where('rating', 2)->count(),
            'one_star' => $reviews->where('rating', 1)->count(),
        ];
    }

    public function hasReviewedAuction(User $user, Auction $auction): bool
    {
        return Review::where('from_user_id', $user->id)
            ->where('auction_id', $auction->id)
            ->exists();
    }
}
