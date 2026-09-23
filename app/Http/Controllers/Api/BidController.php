<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BidResource;
use App\Models\Auction;
use App\Models\PlatformSetting;
use App\Repositories\Contracts\BidRepositoryInterface;
use App\Services\Auction\BidService;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function __construct(
        private BidRepositoryInterface $bidRepository,
        private BidService $bidService
    ) {}

    public function getByAuction($auctionId)
    {
        $limit = request()->get('limit', 20);
        $page = request()->get('page', 1);

        $bids = $this->bidRepository->getByAuction($auctionId, $limit, $page);

        return response()->json([
            'data' => BidResource::collection($bids),
        ]);
    }

    public function store(Request $request, Auction $auction)
    {
        if (! (PlatformSetting::first()?->bidding_enabled ?? true)) {
            return response()->json([
                'message' => 'Licytacje są obecnie wyłączone — dostępna jest tylko opcja Kup teraz',
            ], 422);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'is_auto_bid' => 'sometimes|boolean',
            'max_bid' => 'nullable|numeric|min:0',
            'max_auto_bid' => 'nullable|numeric|min:0',
        ]);

        if ($auction->user_id === $request->user()->id) {
            return response()->json([
                'message' => 'Nie możesz licytować własnej aukcji',
            ], 403);
        }

        if ($auction->status !== 'active') {
            return response()->json([
                'message' => 'Nie można postawić licytacji',
            ], 422);
        }

        $currentPrice = (float) ($auction->current_price ?? 0);
        $incrementPercentage = (float) (PlatformSetting::first()?->bid_increment_percentage ?? 0);
        $minimumBid = $incrementPercentage > 0
            ? round($currentPrice * (1 + $incrementPercentage / 100), 2)
            : $currentPrice + 0.01;

        if ((float) $request->amount < $minimumBid) {
            return response()->json([
                'message' => "Stawka musi wynosić co najmniej {$minimumBid} PLN (minimalny krok licytacji: {$incrementPercentage}%)",
                'errors' => [
                    'amount' => ["Stawka musi wynosić co najmniej {$minimumBid} PLN"],
                ],
            ], 422);
        }

        if ($request->boolean('is_auto_bid')) {
            $bid = $this->bidService->placeAutoBid(
                $auction,
                $request->user(),
                (float) ($request->input('max_bid') ?? $request->input('max_auto_bid', 0))
            );
        } else {
            $bid = $this->bidService->placeBid(
                $auction,
                $request->user(),
                $request->amount
            );
        }

        if (! $bid) {
            return response()->json([
                'message' => 'Nie można postawić licytacji',
            ], 422);
        }

        return response()->json([
            'message' => 'Licytacja wystawiona',
            'data' => new BidResource($bid),
            'bid' => new BidResource($bid),
            'auction' => $auction->fresh(),
        ], 201);
    }

    /**
     * Legacy bid endpoint for /api/auctions/{auction}/bid
     */
    public function placeFromAuction(Request $request, Auction $auction)
    {
        if (! (PlatformSetting::first()?->bidding_enabled ?? true)) {
            return response()->json([
                'message' => 'Licytacje są obecnie wyłączone — dostępna jest tylko opcja Kup teraz',
            ], 422);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'is_auto_bid' => 'sometimes|boolean',
            'max_auto_bid' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();

        if ($auction->user_id === $user->id) {
            return response()->json([
                'error' => 'Sprzedawca nie może licytować własny przedmiot',
            ], 400);
        }

        $currentPrice = $auction->current_price ?? 0;
        if (! $request->boolean('is_auto_bid') && (float) $request->input('amount') <= (float) $currentPrice) {
            return response()->json([
                'error' => 'Stawka musi być wyższa niż obecna cena',
            ], 400);
        }

        if ($request->boolean('is_auto_bid')) {
            $bid = $this->bidService->placeAutoBid(
                $auction,
                $user,
                (float) $request->input('max_auto_bid', 0)
            );
        } else {
            $bid = $this->bidService->placeBid(
                $auction,
                $user,
                (float) $request->input('amount')
            );
        }

        if (! $bid) {
            return response()->json([
                'error' => 'Nie można postawić licytacji',
            ], 400);
        }

        return response()->json([
            'message' => 'Licytacja wystawiona',
            'bid' => $bid,
            'auction' => $auction->fresh(),
        ], 201);
    }

    public function placeAutoBid(Request $request, Auction $auction)
    {
        if (! (PlatformSetting::first()?->bidding_enabled ?? true)) {
            return response()->json([
                'message' => 'Licytacje są obecnie wyłączone — dostępna jest tylko opcja Kup teraz',
            ], 422);
        }

        $request->validate([
            'max_amount' => 'required|numeric|min:0',
        ]);

        if ($auction->user_id === $request->user()->id) {
            return response()->json([
                'message' => 'Nie możesz licytować własnej aukcji',
            ], 403);
        }

        $bid = $this->bidService->placeAutoBid(
            $auction,
            $request->user(),
            $request->max_amount
        );

        return response()->json([
            'message' => 'Auto-licytacja ustawiona',
            'data' => new BidResource($bid),
        ], 201);
    }

    public function userBids(Request $request)
    {
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $bids = $this->bidRepository->getUserBids($request->user()->id, $limit, $page);

        return response()->json([
            'data' => BidResource::collection($bids),
        ]);
    }
}
