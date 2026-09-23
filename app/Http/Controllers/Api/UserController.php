<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        $currentUser = auth('sanctum')->user();
        $isAdmin = $currentUser && $currentUser->is_admin;

        // Check if profile is public or user is admin
        if (! $user->is_public && ! $isAdmin) {
            return response()->json(['message' => 'Ten profil jest prywatny'], 403);
        }

        $auctionsCount = $user->auctions()->whereIn('status', ['active', 'ended'])->count();

        $isOwner = $currentUser && $currentUser->id === $user->id;

        if ($auctionsCount === 0 && ! $isOwner && ! $isAdmin) {
            return response()->json(['message' => 'Profil niedostępny'], 404);
        }
        $contact = ($isOwner || $isAdmin) ? [
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'postcode' => $user->postcode,
        ] : [];

        $isBlockedByViewer = $currentUser && ! $isOwner
            ? $currentUser->blockedUsers()->where('blocked_user_id', $user->id)->exists()
            : false;

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                ...$contact,
                'city' => $user->city,
                'country' => $user->country,
                'bio' => $user->bio,
                'reputation' => $user->reputation,
                'is_premium' => $user->is_premium,
                'premium_plan' => $user->premium_plan,
                'premium_until' => $user->premium_until,
                'is_public' => $user->is_public,
                'auctions_count' => $auctionsCount,
                'created_at' => $user->created_at,
                'is_blocked_by_viewer' => $isBlockedByViewer,
            ],
        ]);
    }

    public function stats(User $user)
    {
        return response()->json([
            'data' => [
                'total_auctions' => $user->auctions()->count(),
                'active_auctions' => $user->auctions()->where('status', 'active')->count(),
                'total_bids' => $user->bids()->count(),
                'won_auctions' => $user->wonAuctions()->count(),
                'average_rating' => round($user->receivedReviews()->avg('rating') ?? 0, 1),
                'total_reviews' => $user->receivedReviews()->count(),
                'member_since' => $user->created_at,
            ],
        ]);
    }

    public function auctions(User $user, Request $request)
    {
        $currentUser = auth('sanctum')->user();
        $isAdmin = $currentUser && $currentUser->is_admin;

        // Check if profile is public or user is admin
        if (! $user->is_public && ! $isAdmin) {
            return response()->json(['message' => 'Ten profil jest prywatny'], 403);
        }

        $limit = $request->get('limit', 20);
        $status = $request->get('status');

        $query = $user->auctions()
            ->with('seller')
            ->withCount('bids');

        if ($status) {
            $query->where('status', $status);
        } else {
            // Admin sees all auctions, regular users only see active auctions
            if (! $isAdmin) {
                $query->where('status', 'active');
            }
        }

        $auctions = $query->paginate($limit);

        return response()->json([
            'data' => $auctions,
        ]);
    }

    public function reviews(User $user, Request $request)
    {
        $limit = $request->get('limit', 20);

        $reviews = $user->reviews()->paginate($limit);

        return response()->json([
            'data' => $reviews,
        ]);
    }

    public function block(Request $request, User $user)
    {
        $blocked = $request->user()->blockUser($user->id);

        if (! $blocked) {
            return response()->json(['message' => 'Nie możesz zablokować samego siebie'], 422);
        }

        return response()->json(['message' => 'Użytkownik zablokowany']);
    }

    public function unblock(Request $request, User $user)
    {
        $request->user()->unblockUser($user->id);

        return response()->json(['message' => 'Użytkownik odblokowany']);
    }
}
