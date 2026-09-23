<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $type = $request->get('type'); // seller, buyer

        $query = Review::query();

        if ($type === 'seller') {
            $query->where('to_user_id', $request->user()->id);
        } elseif ($type === 'buyer') {
            $query->where('from_user_id', $request->user()->id);
        }

        $reviews = $query->paginate($limit);

        return response()->json([
            'data' => $reviews,
        ]);
    }

    public function store(Request $request)
    {
        // Check if user is banned. Uwaga: isBanned() (nie sam ban_until) -
        if ($request->user()->isBanned()) {
            return response()->json([
                'message' => 'You are banned and cannot create reviews',
            ], 403);
        }

        $validated = $request->validate([
            'auction_id' => 'required|exists:auctions,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $auction = Auction::findOrFail($validated['auction_id']);

        // Only buyer can leave review for seller
        $highestBid = $auction->bids()->orderByDesc('amount')->first();

        if (! $highestBid || $highestBid->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Możesz oceniać tylko wygrane aukcje'], 422);
        }

        $review = Review::create([
            'auction_id' => $validated['auction_id'],
            'from_user_id' => $request->user()->id,
            'to_user_id' => $auction->user_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($review, 201);
    }

    public function update(Request $request, Review $review)
    {
        if ($review->from_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update($validated);

        return response()->json([
            'message' => 'Opinia zaktualizowana',
            'data' => $review,
        ]);
    }

    /**
     * Ocen profil hodowcy gwiazdkami 0.5-5.0 (jak Google) - niezalezne od
     */
    public function rateProfile(Request $request, User $user)
    {
        if ($request->user()->isBanned()) {
            return response()->json([
                'message' => 'Jesteś zbanowany i nie możesz wystawiać ocen',
            ], 403);
        }

        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Nie możesz ocenić samego siebie',
            ], 422);
        }

        $halfStarSteps = [0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5];

        $validated = $request->validate([
            'rating' => ['required', 'numeric', Rule::in($halfStarSteps)],
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::updateOrCreate(
            [
                'from_user_id' => $request->user()->id,
                'to_user_id' => $user->id,
                'auction_id' => null,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Ocena zapisana',
            'data' => $review,
            'average_rating' => round($user->receivedReviews()->avg('rating') ?? 0, 1),
            'total_reviews' => $user->receivedReviews()->count(),
        ]);
    }

    public function destroy(Request $request, Review $review)
    {
        if ($review->from_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Opinia usunięta']);
    }
}
