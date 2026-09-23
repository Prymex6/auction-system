<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Category;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BidApiTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;

    private User $bidder;

    private Category $category;

    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();

        $this->seller = User::factory()->create([
            'is_active' => true,
        ]);

        $this->bidder = User::factory()->create([
            'is_active' => true,
        ]);

        $this->auction = Auction::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'type' => 'auction',
            'start_price' => 1000,
            'current_price' => 1000,
            'status' => 'active',
            'ends_at' => now()->addDays(7),
        ]);
    }

    public function test_authenticated_user_can_place_bid()
    {
        $this->actingAs($this->bidder, 'sanctum');

        $response = $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1100,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.user_id', $this->bidder->id);

        $this->assertDatabaseHas('bids', [
            'auction_id' => $this->auction->id,
            'user_id' => $this->bidder->id,
            'amount' => 1100,
        ]);

        // Verify auction price updated
        $this->auction->refresh();
        $this->assertEquals(1100, $this->auction->current_price);
    }

    public function test_bid_must_be_higher_than_current_price()
    {
        $this->actingAs($this->bidder, 'sanctum');

        $response = $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 900, // Lower than current price
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_bid_below_minimum_increment_percentage_is_rejected()
    {
        PlatformSetting::updateOrCreate([], ['bid_increment_percentage' => 5]);
        $this->actingAs($this->bidder, 'sanctum');

        $response = $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1020,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);
    }

    public function test_bid_meeting_minimum_increment_percentage_is_accepted()
    {
        PlatformSetting::updateOrCreate([], ['bid_increment_percentage' => 5]);
        $this->actingAs($this->bidder, 'sanctum');

        $response = $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1050,
        ]);

        $response->assertStatus(201);
    }

    public function test_second_bid_still_respects_increment_percentage_not_hardcoded_ladder()
    {
        PlatformSetting::updateOrCreate([], ['bid_increment_percentage' => 1]);
        $this->actingAs($this->bidder, 'sanctum');

        $this->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1010])
            ->assertStatus(201);

        $otherBidder = User::factory()->create(['is_active' => true]);
        $this->actingAs($otherBidder, 'sanctum');

        // to (a nie stara sztywna drabinka +50 = 1060) powinno decydowac.
        $this->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1020.1])
            ->assertStatus(201);

        $this->assertEquals(1020.1, (float) $this->auction->fresh()->current_price);
    }

    public function test_seller_cannot_bid_on_own_auction()
    {
        $this->actingAs($this->seller, 'sanctum');

        $response = $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1500,
        ]);

        $response->assertStatus(403); // Forbidden
    }

    public function test_guest_cannot_place_bid()
    {
        $response = $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1500,
        ]);

        $response->assertStatus(401); // Unauthorized
    }

    public function test_cannot_bid_on_ended_auction()
    {
        $this->actingAs($this->bidder, 'sanctum');

        $this->auction->update([
            'status' => 'ended',
            'ends_at' => now()->subHours(1),
        ]);

        $response = $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1500,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Nie można postawić licytacji',
            ]);
    }

    public function test_multiple_bids_track_highest_bidder()
    {
        $bidder2 = User::factory()->create(['is_active' => true]);

        // First bid
        $this->actingAs($this->bidder, 'sanctum');
        $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1100,
        ]);

        // Second bid from another user
        $this->actingAs($bidder2, 'sanctum');
        $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1300,
        ]);

        $this->auction->refresh();

        $this->assertEquals(1300, $this->auction->current_price);
        $this->assertDatabaseHas('bids', [
            'auction_id' => $this->auction->id,
            'user_id' => $bidder2->id,
            'amount' => 1300,
        ]);
    }

    public function test_auto_bid_functionality()
    {
        $this->actingAs($this->bidder, 'sanctum');

        // Place auto bid with max amount
        $response = $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1100,
            'is_auto_bid' => true,
            'max_auto_bid' => 2000,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bids', [
            'auction_id' => $this->auction->id,
            'user_id' => $this->bidder->id,
            'is_auto_bid' => true,
            'max_auto_bid' => 2000,
        ]);
    }

    public function test_anti_sniper_extends_auction()
    {
        $this->auction->update([
            'anti_sniper_enabled' => true,
            'sniper_threshold' => 2, // 2 minutes
            'extension_minutes' => 5,
            'ends_at' => now()->addMinutes(1), // Ending soon
        ]);

        $originalEnd = $this->auction->ends_at;

        $this->actingAs($this->bidder, 'sanctum');
        $this->postJson("/api/bids/auction/{$this->auction->id}", [
            'amount' => 1100,
        ]);

        $this->auction->refresh();

        // Auction should be extended
        $this->assertGreaterThan($originalEnd, $this->auction->ends_at);
        $this->assertEquals(1, $this->auction->times_extended);
    }
}
