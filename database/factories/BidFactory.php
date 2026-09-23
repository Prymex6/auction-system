<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidFactory extends Factory
{
    protected $model = Bid::class;

    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'user_id' => User::factory(),
            'amount' => $this->faker->numberBetween(50, 500),
            'is_auto_bid' => false,
            'max_auto_bid' => null,
            'auto_bid_status' => null,
            'auto_bid_max_amount' => null,
            'increment_used' => null,
            'placed_at' => now(),
        ];
    }
}
