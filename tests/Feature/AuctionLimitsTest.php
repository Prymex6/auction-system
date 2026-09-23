<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionLimitsTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Testowy gołąb',
            'breed' => 'Janssen',
            'gender' => 'samiec',
            'year' => 2024,
            'size' => 'sredni',
            'color' => 'Niebieska',
            'start_price' => 100,
            'type' => 'buy_now',
            'pigeon_images' => ['auctions/test.jpg'],
        ], $overrides);
    }

    public function test_daily_auction_limit_is_enforced()
    {
        PlatformSetting::updateOrCreate([], ['max_auctions_per_day' => 2]);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user, 'sanctum')->postJson('/api/auctions', $this->validPayload())->assertStatus(201);
        $this->actingAs($user, 'sanctum')->postJson('/api/auctions', $this->validPayload())->assertStatus(201);
        $this->actingAs($user, 'sanctum')->postJson('/api/auctions', $this->validPayload())->assertStatus(422);
    }

    public function test_daily_auction_limit_does_not_apply_to_admin()
    {
        PlatformSetting::updateOrCreate([], ['max_auctions_per_day' => 1]);
        $admin = User::factory()->create(['is_active' => true, 'is_admin' => true]);

        $this->actingAs($admin, 'sanctum')->postJson('/api/auctions', $this->validPayload())->assertStatus(201);
        $this->actingAs($admin, 'sanctum')->postJson('/api/auctions', $this->validPayload())->assertStatus(201);
    }

    public function test_free_user_active_auction_limit_is_enforced()
    {
        PlatformSetting::updateOrCreate([], ['free_user_auction_limit' => 1]);
        $user = User::factory()->create(['is_active' => true]);
        Auction::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/auctions', $this->validPayload());

        $response->assertStatus(422);
    }

    public function test_premium_user_gets_premium_limit_not_free_limit()
    {
        PlatformSetting::updateOrCreate([], ['free_user_auction_limit' => 1, 'premium_user_auction_limit' => 5]);
        $user = User::factory()->create([
            'is_active' => true,
            'is_premium' => true,
            'premium_until' => now()->addMonth(),
        ]);
        Auction::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/auctions', $this->validPayload());

        $response->assertStatus(201);
    }

    public function test_auction_is_active_immediately_when_approval_not_required()
    {
        PlatformSetting::updateOrCreate([], ['require_auction_approval' => false]);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user, 'sanctum')->postJson('/api/auctions', $this->validPayload(['title' => 'Bez akceptacji']));

        $this->assertDatabaseHas('auctions', ['title' => 'Bez akceptacji', 'status' => 'active']);
    }

    public function test_auction_duration_uses_platform_setting()
    {
        PlatformSetting::updateOrCreate([], ['default_auction_duration' => 3]);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user, 'sanctum')->postJson('/api/auctions', $this->validPayload(['title' => 'Krotka aukcja']));

        $auction = Auction::where('title', 'Krotka aukcja')->first();
        $this->assertNotNull($auction);
        $this->assertEqualsWithDelta(
            now()->addDays(3)->timestamp,
            $auction->ends_at->timestamp,
            5
        );
    }

    public function test_image_count_over_setting_limit_is_rejected()
    {
        PlatformSetting::updateOrCreate([], ['max_images_per_auction' => 2]);
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/auctions', $this->validPayload([
            'pigeon_images' => ['a.jpg', 'b.jpg', 'c.jpg'],
        ]));

        $response->assertStatus(422);
    }

    public function test_new_registration_gets_listings_free_count_from_setting()
    {
        PlatformSetting::updateOrCreate([], ['free_user_auction_limit' => 7]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'limitsynctest',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'limitsynctest@example.com',
            'password' => 'TestHaslo123!',
            'password_confirmation' => 'TestHaslo123!',
            'terms' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'limitsynctest@example.com', 'listings_free_count' => 7]);
    }
}
