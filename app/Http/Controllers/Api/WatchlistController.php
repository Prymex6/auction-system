<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WatchlistResource;
use App\Models\Auction;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $watchlist = $request->user()->watchlist()
            ->with('auction', 'auction.highestBidder')
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => WatchlistResource::collection($watchlist),
        ]);
    }

    public function store(Request $request, Auction $auction)
    {
        $exists = Watchlist::where('user_id', $request->user()->id)
            ->where('auction_id', $auction->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Aukcja jest już na Twojej liście obserwowanych',
            ], 422);
        }

        Watchlist::create([
            'user_id' => $request->user()->id,
            'auction_id' => $auction->id,
        ]);

        return response()->json([
            'message' => 'Aukcja dodana do watchlistu',
        ], 201);
    }

    public function destroy(Request $request, Auction $auction)
    {
        $watchlist = Watchlist::where('user_id', $request->user()->id)
            ->where('auction_id', $auction->id)
            ->first();

        if (! $watchlist) {
            return response()->json([
                'message' => 'Aukcja nie znajduje się na Twojej liście obserwowanych',
            ], 404);
        }

        $watchlist->delete();

        return response()->json([
            'message' => 'Aukcja usunięta z watchlistu',
        ]);
    }

    public function check(Request $request, Auction $auction)
    {
        $isWatched = Watchlist::where('user_id', $request->user()->id)
            ->where('auction_id', $auction->id)
            ->exists();

        return response()->json([
            'watched' => $isWatched,
        ]);
    }
}
