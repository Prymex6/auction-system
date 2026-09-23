<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auction>
 */
class AuctionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startPrice = $this->faker->numberBetween(100, 500);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'breed' => $this->faker->randomElement(['Pocztowy', 'Grzywacz', 'Dublet', 'Saksoman']),
            'year' => $this->faker->numberBetween(2020, now()->year),
            'gender' => $this->faker->randomElement(['samiec', 'samica', 'golab_mlody']),
            'color' => $this->faker->randomElement(['Niebieska', 'Czerwona', 'Żółta', 'Biała']),
            'size' => $this->faker->randomElement(['maly', 'sredni', 'duzy']),
            'ring_number' => 'PL-'.$this->faker->year().'-'.$this->faker->numberBetween(1000, 9999),
            'type' => 'auction',
            'start_price' => $startPrice,
            'current_price' => $startPrice,
            'winner_id' => null,
            'started_at' => now(),
            'ends_at' => now()->addDays(7),
            'status' => 'active',
            'anti_sniper_enabled' => true,
            'sniper_threshold' => 2,
            'extension_minutes' => 5,
            'times_extended' => 0,
        ];
    }
}
