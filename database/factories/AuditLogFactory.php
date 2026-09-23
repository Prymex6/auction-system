<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition()
    {
        return [
            'admin_user_id' => User::factory(),
            'action' => $this->faker->randomElement(['banned', 'approved', 'rejected']),
            'model_type' => $this->faker->randomElement(['User', 'Listing', 'Auction']),
            'model_id' => $this->faker->numberBetween(1, 50),
            'meta' => ['note' => $this->faker->sentence()],
        ];
    }
}
