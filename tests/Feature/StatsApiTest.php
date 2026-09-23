<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_endpoint_returns_real_counts()
    {
        Cache::forget('platform-stats');

        User::factory()->count(3)->create();
        Auction::factory()->count(2)->create(['status' => 'active']);
        Auction::factory()->create(['status' => 'ended']);

        $response = $this->getJson('/api/stats');

        $response->assertStatus(200)
            ->assertJsonStructure(['active_auctions', 'breeders', 'ended_auctions']);

        $data = $response->json();
        $this->assertSame(2, $data['active_auctions']);
        $this->assertSame(1, $data['ended_auctions']);
        $this->assertGreaterThanOrEqual(3, $data['breeders']);
    }
}
