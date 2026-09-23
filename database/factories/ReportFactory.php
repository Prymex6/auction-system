<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition()
    {
        // Create polymorphic report - can report User, Auction, or Message
        $reportableTypes = [
            ['type' => User::class, 'factory' => User::factory()],
            ['type' => Auction::class, 'factory' => Auction::factory()],
        ];

        $reportable = $this->faker->randomElement($reportableTypes);

        return [
            'reported_by' => User::factory(),
            'reportable_type' => $reportable['type'],
            'reportable_id' => $reportable['factory'],
            'reason' => $this->faker->randomElement(['spam', 'inappropriate', 'fraud', 'dead_pigeon', 'other']),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'reviewing', 'resolved', 'dismissed']),
            'reviewed_by' => null,
            'reviewed_at' => null,
            'resolution_reason' => null,
            'notes' => null,
        ];
    }

    public function withAuction()
    {
        return $this->state(function (array $attributes) {
            return [
                'reportable_type' => Auction::class,
                'reportable_id' => Auction::factory(),
            ];
        });
    }

    public function withUser()
    {
        return $this->state(function (array $attributes) {
            return [
                'reportable_type' => User::class,
                'reportable_id' => User::factory(),
            ];
        });
    }

    public function resolved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'resolved',
                'reviewed_at' => now(),
                'reviewed_by' => User::factory(),
                'resolution_reason' => $this->faker->sentence(),
                'notes' => $this->faker->paragraph(),
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'resolved_at' => null,
            ];
        });
    }
}
