<?php

namespace Tests\Feature\User;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_stranger_gets_404_for_user_without_any_auctions()
    {
        $stranger = User::factory()->create(['is_active' => true]);
        $emptyUser = User::factory()->create(['is_active' => true, 'is_public' => true]);

        $response = $this->actingAs($stranger, 'sanctum')
            ->getJson("/api/users/{$emptyUser->id}");

        $response->assertStatus(404);
    }

    public function test_guest_gets_404_for_user_without_any_auctions()
    {
        $emptyUser = User::factory()->create(['is_active' => true, 'is_public' => true]);

        $this->getJson("/api/users/{$emptyUser->id}")->assertStatus(404);
    }

    public function test_owner_can_view_own_profile_with_zero_auctions()
    {
        $emptyUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($emptyUser, 'sanctum')
            ->getJson("/api/users/{$emptyUser->id}");

        $response->assertStatus(200);
    }

    public function test_admin_can_view_any_profile_with_zero_auctions()
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $emptyUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/users/{$emptyUser->id}");

        $response->assertStatus(200);
    }

    public function test_stranger_can_view_profile_with_active_auction()
    {
        $stranger = User::factory()->create(['is_active' => true]);
        $seller = User::factory()->create(['is_active' => true, 'is_public' => true]);
        Auction::factory()->create([
            'user_id' => $seller->id,
            'status' => 'active',
            'ends_at' => now()->addDays(5),
        ]);

        $response = $this->actingAs($stranger, 'sanctum')
            ->getJson("/api/users/{$seller->id}");

        $response->assertStatus(200);
    }

    public function test_stranger_can_view_profile_with_only_ended_auction()
    {
        $stranger = User::factory()->create(['is_active' => true]);
        $seller = User::factory()->create(['is_active' => true, 'is_public' => true]);
        Auction::factory()->create([
            'user_id' => $seller->id,
            'status' => 'ended',
            'ends_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($stranger, 'sanctum')
            ->getJson("/api/users/{$seller->id}");

        $response->assertStatus(200);
    }

    public function test_stranger_gets_404_when_user_only_has_pending_auction()
    {
        $stranger = User::factory()->create(['is_active' => true]);
        $seller = User::factory()->create(['is_active' => true, 'is_public' => true]);
        Auction::factory()->create([
            'user_id' => $seller->id,
            'status' => 'pending',
            'ends_at' => now()->addDays(5),
        ]);

        $response = $this->actingAs($stranger, 'sanctum')
            ->getJson("/api/users/{$seller->id}");

        $response->assertStatus(404);
    }
}
