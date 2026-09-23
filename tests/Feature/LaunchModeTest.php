<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaunchModeTest extends TestCase
{
    use RefreshDatabase;

    private function launchMode(array $overrides = []): void
    {
        PlatformSetting::updateOrCreate(['id' => 1], array_merge([
            'platform_name' => 'Pigeon Auction',
            'only_admin_can_list' => true,
            'bidding_enabled' => false,
        ], $overrides));
    }

    private function auctionPayload(): array
    {
        return [
            'title' => 'Testowy gołąb',
            'breed' => 'Pocztowy Belgijski',
            'gender' => 'samiec',
            'year' => 2024,
            'size' => 'sredni',
            'color' => 'Niebieska',
            'start_price' => 500,
            'type' => 'buy_now',
            'pigeon_images' => ['auctions/test.jpg'],
        ];
    }

    public function test_regular_user_cannot_create_auction_in_launch_mode()
    {
        $this->launchMode();
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())
            ->assertStatus(403);

        $this->assertDatabaseMissing('auctions', ['title' => 'Testowy gołąb']);
    }

    public function test_admin_can_create_auction_in_launch_mode()
    {
        $this->launchMode();
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())
            ->assertStatus(201);

        $this->assertDatabaseHas('auctions', ['title' => 'Testowy gołąb']);
    }

    public function test_regular_user_can_create_auction_when_flag_off()
    {
        $this->launchMode(['only_admin_can_list' => false]);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())
            ->assertStatus(201);
    }

    public function test_user_with_listing_exception_can_create_auction_despite_launch_mode()
    {
        $this->launchMode();
        $user = User::factory()->create(['is_active' => true, 'can_list_when_restricted' => true]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())
            ->assertStatus(201);

        $this->assertDatabaseHas('auctions', ['title' => 'Testowy gołąb', 'user_id' => $user->id]);
    }

    public function test_user_with_listing_exception_is_forced_to_buy_now_even_if_auction_type_requested()
    {
        $this->launchMode();
        $user = User::factory()->create(['is_active' => true, 'can_list_when_restricted' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auctions', array_merge($this->auctionPayload(), ['type' => 'auction']));

        $response->assertStatus(201);
        $this->assertDatabaseHas('auctions', ['title' => 'Testowy gołąb', 'type' => 'buy_now']);
    }

    public function test_user_with_listing_exception_auction_still_requires_admin_approval()
    {
        $this->launchMode(['require_auction_approval' => true]);
        $user = User::factory()->create(['is_active' => true, 'can_list_when_restricted' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload());

        $response->assertStatus(201);
        $this->assertDatabaseHas('auctions', ['title' => 'Testowy gołąb', 'status' => 'pending']);
    }

    public function test_user_without_listing_exception_is_still_blocked_in_launch_mode()
    {
        $this->launchMode();
        $user = User::factory()->create(['is_active' => true, 'can_list_when_restricted' => false]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())
            ->assertStatus(403);
    }

    public function test_bidding_blocked_when_disabled()
    {
        $this->launchMode();
        $seller = User::factory()->create(['is_active' => true]);
        $bidder = User::factory()->create(['is_active' => true]);
        $auction = Auction::factory()->create([
            'user_id' => $seller->id,
            'type' => 'auction',
            'status' => 'active',
            'current_price' => 1000,
            'ends_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($bidder, 'sanctum')
            ->postJson("/api/bids/auction/{$auction->id}", ['amount' => 1100]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Licytacje są obecnie wyłączone — dostępna jest tylko opcja Kup teraz']);

        $this->assertDatabaseMissing('bids', ['auction_id' => $auction->id]);
    }

    public function test_bidding_works_when_enabled()
    {
        $this->launchMode(['bidding_enabled' => true]);
        $seller = User::factory()->create(['is_active' => true]);
        $bidder = User::factory()->create(['is_active' => true]);
        $auction = Auction::factory()->create([
            'user_id' => $seller->id,
            'type' => 'auction',
            'status' => 'active',
            'current_price' => 1000,
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($bidder, 'sanctum')
            ->postJson("/api/bids/auction/{$auction->id}", ['amount' => 1100])
            ->assertStatus(201);
    }

    public function test_admin_can_configure_google_analytics()
    {
        $this->launchMode();
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/admin/settings', ['google_analytics_id' => 'G-ABC123XYZ'])
            ->assertStatus(200);

        $this->assertDatabaseHas('platform_settings', ['google_analytics_id' => 'G-ABC123XYZ']);

        $this->getJson('/api/settings')
            ->assertJsonPath('data.google_analytics_id', 'G-ABC123XYZ');
    }

    public function test_invalid_analytics_id_is_rejected()
    {
        $this->launchMode();
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/admin/settings', ['google_analytics_id' => 'not-a-ga-id!'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['google_analytics_id']);
    }

    public function test_launch_flags_visible_in_public_settings()
    {
        $this->launchMode();

        $response = $this->getJson('/api/settings');

        $response->assertStatus(200)
            ->assertJsonPath('data.only_admin_can_list', true)
            ->assertJsonPath('data.bidding_enabled', false);
    }
}
