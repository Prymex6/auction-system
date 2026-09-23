<?php

namespace App\Http\Resources;

use App\Models\Bid;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Bid
 */
class BidResource extends JsonResource
{
    /**
     * (art. 6 ust. 1 lit. b RODO — wykonanie umowy).
     */
    private function canSeeBidderContact(): bool
    {
        $viewer = auth('sanctum')->user();
        $auction = $this->auction;

        return $viewer
            && $auction
            && $auction->status === 'ended'
            && $auction->winner_id === $this->user_id
            && ($viewer->id === $auction->user_id || $viewer->isAdmin());
    }

    public function toArray(Request $request): array
    {
        $fullName = trim(($this->user?->first_name ?? '').' '.($this->user?->last_name ?? ''));

        return [
            'id' => $this->id,
            'auction_id' => $this->auction_id,
            'user_id' => $this->user_id,
            'amount' => (float) $this->amount,
            'is_auto_bid' => $this->is_auto_bid,
            'max_auto_bid' => (float) $this->max_auto_bid,
            'bidder' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'bidder_name' => $this->user?->name,
            'created_at' => $this->created_at?->toIso8601String(),
            'placed_at' => $this->created_at?->toIso8601String(),
            'bidder_full_name' => $this->canSeeBidderContact() ? ($fullName ?: $this->user?->name) : null,
            'bidder_phone' => $this->canSeeBidderContact() ? $this->user?->phone : null,
            'bidder_email' => $this->canSeeBidderContact() ? $this->user?->email : null,
        ];
    }
}
