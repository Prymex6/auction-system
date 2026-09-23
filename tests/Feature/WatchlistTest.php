<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $seller;

    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
        $this->seller = User::factory()->create(['is_active' => true]);
        $this->auction = Auction::factory()->create([
            'user_id' => $this->seller->id,
            'status' => 'active',
            'ends_at' => now()->addDays(5),
        ]);
    }

    public function test_user_can_add_auction_to_watchlist()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/watchlist/auction/{$this->auction->id}");

        $response->assertStatus(201);
        $this->assertDatabaseHas('watchlists', [
            'user_id' => $this->user->id,
            'auction_id' => $this->auction->id,
        ]);
    }

    public function test_user_cannot_add_same_auction_twice()
    {
        Watchlist::create(['user_id' => $this->user->id, 'auction_id' => $this->auction->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/watchlist/auction/{$this->auction->id}");

        $response->assertStatus(422);
        $this->assertEquals(1, Watchlist::where('user_id', $this->user->id)
            ->where('auction_id', $this->auction->id)->count());
    }

    public function test_user_can_remove_auction_from_watchlist()
    {
        Watchlist::create(['user_id' => $this->user->id, 'auction_id' => $this->auction->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/watchlist/auction/{$this->auction->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('watchlists', [
            'user_id' => $this->user->id,
            'auction_id' => $this->auction->id,
        ]);
    }

    public function test_removing_auction_not_on_watchlist_returns_404()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/watchlist/auction/{$this->auction->id}");

        $response->assertStatus(404);
    }

    public function test_check_returns_true_when_watched()
    {
        Watchlist::create(['user_id' => $this->user->id, 'auction_id' => $this->auction->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/watchlist/auction/{$this->auction->id}/check");

        $response->assertStatus(200);
        $this->assertTrue($response->json('watched'));
    }

    public function test_check_returns_false_when_not_watched()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/watchlist/auction/{$this->auction->id}/check");

        $response->assertStatus(200);
        $this->assertFalse($response->json('watched'));
    }

    public function test_index_lists_only_own_watchlist_entries()
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $otherAuction = Auction::factory()->create([
            'user_id' => $this->seller->id,
            'status' => 'active',
            'ends_at' => now()->addDays(5),
        ]);

        Watchlist::create(['user_id' => $this->user->id, 'auction_id' => $this->auction->id]);
        Watchlist::create(['user_id' => $otherUser->id, 'auction_id' => $otherAuction->id]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/watchlist');

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('auction.id');
        $this->assertTrue($ids->contains($this->auction->id));
        $this->assertFalse($ids->contains($otherAuction->id));
    }

    public function test_guest_cannot_access_watchlist()
    {
        $this->getJson('/api/watchlist')->assertStatus(401);
    }
}
