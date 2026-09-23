<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Category;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * nigdzie nie odczytywalo/egzekwowalo - admin mogl "wymusic" 2FA, a zaden
 * middleware EnsureTwoFactorIfRequired blokujace bidowanie/wystawianie (nie
 */
class TwoFactorRequirementTest extends TestCase
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
        $this->seller = User::factory()->create(['is_active' => true]);
        $this->bidder = User::factory()->create(['is_active' => true, 'two_factor_enabled' => false]);

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

    private function enableRequire2fa(): void
    {
        $settings = PlatformSetting::first() ?? PlatformSetting::create([]);
        $settings->update(['require_2fa' => true]);
    }

    public function test_bidding_is_blocked_without_2fa_when_required()
    {
        $this->enableRequire2fa();

        $response = $this->actingAs($this->bidder, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1100]);

        $response->assertStatus(403)->assertJsonPath('code', 'TWO_FACTOR_REQUIRED');
        $this->assertDatabaseMissing('bids', ['user_id' => $this->bidder->id]);
    }

    public function test_bidding_works_with_2fa_enabled_when_required()
    {
        $this->enableRequire2fa();
        $this->bidder->update(['two_factor_enabled' => true]);

        $response = $this->actingAs($this->bidder, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1100]);

        $response->assertStatus(201);
    }

    public function test_bidding_works_without_2fa_when_not_required()
    {
        $response = $this->actingAs($this->bidder, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1100]);

        $response->assertStatus(201);
    }

    public function test_creating_auction_is_blocked_without_2fa_when_required()
    {
        $this->enableRequire2fa();

        $response = $this->actingAs($this->bidder, 'sanctum')->postJson('/api/auctions', [
            'title' => 'Aukcja bez 2FA',
            'breed' => 'Pocztowy',
            'gender' => 'samiec',
            'year' => 2024,
            'size' => 'sredni',
            'color' => 'Niebieska',
            'start_price' => 100,
            'pigeon_images' => ['auctions/test.jpg'],
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(403)->assertJsonPath('code', 'TWO_FACTOR_REQUIRED');
        $this->assertDatabaseMissing('auctions', ['title' => 'Aukcja bez 2FA']);
    }

    public function test_auto_bid_is_blocked_without_2fa_when_required()
    {
        $this->enableRequire2fa();

        $response = $this->actingAs($this->bidder, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}/auto-bid", ['max_amount' => 2000]);

        $response->assertStatus(403)->assertJsonPath('code', 'TWO_FACTOR_REQUIRED');
    }
}
