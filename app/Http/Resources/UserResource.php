<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'country' => $this->country,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'is_public' => $this->is_public,
            'is_active' => $this->is_active,
            'is_premium' => $this->is_premium,
            'premium_plan' => $this->premium_plan,
            'premium_until' => $this->premium_until,
            'reputation' => $this->reputation,
            'created_at' => $this->created_at,
        ];
    }
}
