<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationGateTest extends TestCase
{
    use RefreshDatabase;

    private function activeAuction(User $seller): Auction
    {
        return Auction::factory()->create([
            'user_id' => $seller->id,
            'type' => 'buy_now',
            'status' => 'active',
            'ends_at' => now()->addDays(7),
        ]);
    }

    public function test_registration_creates_unverified_account()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'nowyhodowca',
            'first_name' => 'Jan',
            'last_name' => 'Nowak',
            'email' => 'nowy@hodowca.pl',
            'password' => 'Haslo1234',
            'password_confirmation' => 'Haslo1234',
            'terms' => true,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('users', [
            'email' => 'nowy@hodowca.pl',
            'is_active' => false,
        ]);
    }

    public function test_unverified_user_cannot_send_message()
    {
        $seller = User::factory()->create(['is_active' => true]);
        $unverified = User::factory()->create(['is_active' => false]);

        $this->actingAs($unverified, 'sanctum')
            ->postJson("/api/messages/user/{$seller->id}", ['content' => 'Dzień dobry, czy gołąb dostępny?'])
            ->assertStatus(403)
            ->assertJsonPath('code', 'USER_NOT_ACTIVE');

        $this->assertDatabaseMissing('messages', ['sender_id' => $unverified->id]);
    }

    public function test_verified_user_can_send_message()
    {
        $seller = User::factory()->create(['is_active' => true]);
        $buyer = User::factory()->create(['is_active' => true]);

        $this->actingAs($buyer, 'sanctum')
            ->postJson("/api/messages/user/{$seller->id}", ['content' => 'Dzień dobry, czy gołąb dostępny?'])
            ->assertSuccessful();

        $this->assertDatabaseHas('messages', [
            'sender_id' => $buyer->id,
            'recipient_id' => $seller->id,
        ]);
    }

    public function test_unverified_user_cannot_submit_report()
    {
        $seller = User::factory()->create(['is_active' => true]);
        $auction = $this->activeAuction($seller);
        $unverified = User::factory()->create(['is_active' => false]);

        $this->actingAs($unverified, 'sanctum')
            ->postJson('/api/reports', [
                'auction_id' => $auction->id,
                'reason' => 'fraud',
                'description' => 'Podejrzana aukcja, proszę sprawdzić.',
            ])
            ->assertStatus(403);
    }

    public function test_verified_user_can_submit_report()
    {
        $seller = User::factory()->create(['is_active' => true]);
        $auction = $this->activeAuction($seller);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports', [
                'auction_id' => $auction->id,
                'reason' => 'illegal',
                'description' => 'Podejrzana aukcja, proszę sprawdzić.',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('reports', [
            'reported_by' => $user->id,
            'reportable_id' => $auction->id,
            'reason' => 'illegal',
        ]);
    }

    public function test_duplicate_report_is_rejected()
    {
        $seller = User::factory()->create(['is_active' => true]);
        $auction = $this->activeAuction($seller);
        $user = User::factory()->create(['is_active' => true]);

        $payload = [
            'auction_id' => $auction->id,
            'reason' => 'spam',
            'description' => 'Zgłaszam tę aukcję jako spam.',
        ];

        $this->actingAs($user, 'sanctum')->postJson('/api/reports', $payload)->assertStatus(201);
        $this->actingAs($user, 'sanctum')->postJson('/api/reports', $payload)->assertStatus(422);
    }

    public function test_unverified_user_does_not_see_seller_phone()
    {
        $seller = User::factory()->create(['is_active' => true, 'phone' => '+48500600700']);
        $auction = $this->activeAuction($seller);
        $unverified = User::factory()->create(['is_active' => false]);

        $this->actingAs($unverified, 'sanctum')
            ->getJson("/api/auctions/{$auction->id}")
            ->assertJsonPath('data.seller_phone', null);
    }

    public function test_verified_user_sees_seller_phone()
    {
        $seller = User::factory()->create(['is_active' => true, 'phone' => '+48500600700']);
        $auction = $this->activeAuction($seller);
        $verified = User::factory()->create(['is_active' => true]);

        $this->actingAs($verified, 'sanctum')
            ->getJson("/api/auctions/{$auction->id}")
            ->assertJsonPath('data.seller_phone', '+48500600700');
    }

    public function test_guest_does_not_see_seller_phone()
    {
        $seller = User::factory()->create(['is_active' => true, 'phone' => '+48500600700']);
        $auction = $this->activeAuction($seller);

        $this->getJson("/api/auctions/{$auction->id}")
            ->assertJsonPath('data.seller_phone', null);
    }

    public function test_unverified_user_cannot_bid_even_when_bidding_enabled()
    {
        PlatformSetting::updateOrCreate(['id' => 1], [
            'platform_name' => 'Pigeon Auction',
            'bidding_enabled' => true,
        ]);
        $seller = User::factory()->create(['is_active' => true]);
        $auction = Auction::factory()->create([
            'user_id' => $seller->id,
            'type' => 'auction',
            'status' => 'active',
            'current_price' => 1000,
            'ends_at' => now()->addDays(7),
        ]);
        $unverified = User::factory()->create(['is_active' => false]);

        $this->actingAs($unverified, 'sanctum')
            ->postJson("/api/bids/auction/{$auction->id}", ['amount' => 1100])
            ->assertStatus(403);
    }

    public function test_new_auction_requires_admin_approval()
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user, 'sanctum')->postJson('/api/auctions', [
            'title' => 'Gołąb do zatwierdzenia',
            'breed' => 'Janssen',
            'gender' => 'samiec',
            'year' => 2024,
            'size' => 'sredni',
            'color' => 'Niebieska',
            'start_price' => 500,
            'type' => 'buy_now',
            'pigeon_images' => ['auctions/test.jpg'],
        ])->assertStatus(201);

        $this->assertDatabaseHas('auctions', [
            'title' => 'Gołąb do zatwierdzenia',
            'status' => 'pending',
        ]);
    }
}
