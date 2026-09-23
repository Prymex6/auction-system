<?php

namespace App\Http\Resources;

use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Auction
 */
class AuctionResource extends JsonResource
{
    private function canSeeWinnerContact(): bool
    {
        $user = auth('sanctum')->user();

        return $user
            && $this->status === 'ended'
            && $this->winner_id
            && ($user->id === $this->user_id || $user->isAdmin());
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category_id,
            'breed' => $this->breed,
            'gender' => $this->gender,
            'color' => $this->color,
            'ring_number' => $this->ring_number,
            'year' => $this->year,
            'size' => $this->size,
            'type' => $this->type,
            'start_price' => (float) $this->start_price,
            'current_price' => (float) $this->current_price,
            'bids_count' => $this->bids()->count(),
            'status' => $this->status,
            'started_at' => $this->started_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'winner' => [
                'id' => $this->winner?->id,
                'name' => $this->winner?->name,
                'phone' => $this->canSeeWinnerContact() ? $this->winner?->phone : null,
                'email' => $this->canSeeWinnerContact() ? $this->winner?->email : null,
            ],
            'seller' => [
                'id' => $this->seller?->id,
                'name' => $this->seller?->name,
                'rating' => $this->seller?->reputation,
            ],
            'seller_phone' => auth('sanctum')->user()?->is_active ? $this->seller?->phone : null,
            'seller_bio' => $this->seller?->bio,
            'image' => $this->pigeon_images
                ? ($this->pigeon_images[0] ?? null)
                : null,
            'pigeon_images' => $this->pigeon_images ?? [],
            'images' => $this->pigeon_images ?? [],
            'pedigree_images' => $this->pedigree_images ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
