<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\LegalPage;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $owner;

    private User $stranger;   // zweryfikowana obca osoba

    private User $unverified;

    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();

        PlatformSetting::updateOrCreate(['id' => 1], [
            'platform_name' => 'Gołębiowy Lot',
            'only_admin_can_list' => false,
            'bidding_enabled' => true,
        ]);

        $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $this->owner = User::factory()->create(['is_active' => true, 'phone' => '+48111222333']);
        $this->stranger = User::factory()->create(['is_active' => true]);
        $this->unverified = User::factory()->create(['is_active' => false]);

        $this->auction = Auction::factory()->create([
            'user_id' => $this->owner->id,
            'type' => 'auction',
            'status' => 'active',
            'current_price' => 1000,
            'ends_at' => now()->addDays(7),
        ]);
    }

    private function auctionPayload(): array
    {
        return [
            'title' => 'Gołąb testowy',
            'breed' => 'Janssen',
            'gender' => 'samiec',
            'year' => 2024,
            'size' => 'sredni',
            'color' => 'Niebieska',
            'start_price' => 500,
            'type' => 'buy_now',
            'pigeon_images' => ['auctions/test.jpg'],
        ];
    }

    public function test_everyone_can_browse_auctions()
    {
        $this->getJson('/api/auctions')->assertStatus(200);
        $this->getJson("/api/auctions/{$this->auction->id}")->assertStatus(200);

        // niezweryfikowany
        $this->actingAs($this->unverified, 'sanctum')
            ->getJson("/api/auctions/{$this->auction->id}")->assertStatus(200);

        LegalPage::create(['slug' => 'terms', 'title' => 'Regulamin', 'content' => '## Test']);
        $this->getJson('/api/pages/terms')->assertStatus(200);
    }

    // ===================== WYSTAWIANIE =====================

    public function test_listing_permissions_matrix()
    {
        $this->postJson('/api/auctions', $this->auctionPayload())->assertStatus(401);

        $this->actingAs($this->unverified, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())->assertStatus(403);

        $response = $this->actingAs($this->stranger, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload());
        $response->assertStatus(201);
        $this->assertEquals('pending', $response->json('data.status'));

        // admin → 201
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())->assertStatus(201);
    }

    public function test_listing_locked_to_admin_in_launch_mode()
    {
        PlatformSetting::where('id', 1)->update(['only_admin_can_list' => true]);

        $this->actingAs($this->stranger, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())->assertStatus(403);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())->assertStatus(201);
    }

    // ===================== EDYCJA / USUWANIE =====================

    public function test_edit_and_delete_permissions_matrix()
    {
        $pending = Auction::factory()->create([
            'user_id' => $this->owner->id,
            'status' => 'pending',
            'title' => 'Do edycji',
        ]);

        // obcy zweryfikowany → 403
        $this->actingAs($this->stranger, 'sanctum')
            ->putJson("/api/auctions/{$pending->id}", ['title' => 'Hack'])->assertStatus(403);
        $this->actingAs($this->stranger, 'sanctum')
            ->deleteJson("/api/auctions/{$pending->id}")->assertStatus(403);

        $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/auctions/{$pending->id}", ['title' => 'Poprawiony tytuł'])
            ->assertStatus(200);

        $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/auctions/{$this->auction->id}", ['title' => 'Zmiana'])
            ->assertStatus(422);

        $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/auctions/{$this->auction->id}")->assertStatus(422);
        $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/auctions/{$pending->id}")->assertStatus(200);

        $pending2 = Auction::factory()->create(['user_id' => $this->owner->id, 'status' => 'pending']);
        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/auctions/{$pending2->id}", ['title' => 'Admin edit'])->assertStatus(200);
    }

    public function test_bidding_permissions_matrix()
    {
        $this->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1100])
            ->assertStatus(401);

        // niezweryfikowany → 403
        $this->actingAs($this->unverified, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1100])
            ->assertStatus(403);

        $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1100])
            ->assertStatus(403);

        $this->actingAs($this->stranger, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1100])
            ->assertStatus(201);
        $this->assertEquals(1100, (float) $this->auction->fresh()->current_price);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1200])
            ->assertStatus(201);
    }

    public function test_messaging_permissions_matrix()
    {
        $this->postJson("/api/messages/user/{$this->owner->id}", ['content' => 'Hej'])
            ->assertStatus(401);

        // niezweryfikowany → 403
        $this->actingAs($this->unverified, 'sanctum')
            ->postJson("/api/messages/user/{$this->owner->id}", ['content' => 'Hej'])
            ->assertStatus(403);

        // zweryfikowany → sukces
        $this->actingAs($this->stranger, 'sanctum')
            ->postJson("/api/messages/user/{$this->owner->id}", ['content' => 'Czy gołąb dostępny?'])
            ->assertSuccessful();

        $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/messages/user/{$this->stranger->id}")
            ->assertStatus(200);
    }

    public function test_seller_phone_visibility_matrix()
    {
        $this->getJson("/api/auctions/{$this->auction->id}")
            ->assertJsonPath('data.seller_phone', null);

        // niezweryfikowany → null
        $this->actingAs($this->unverified, 'sanctum')
            ->getJson("/api/auctions/{$this->auction->id}")
            ->assertJsonPath('data.seller_phone', null);

        // zweryfikowany → widzi
        $this->actingAs($this->stranger, 'sanctum')
            ->getJson("/api/auctions/{$this->auction->id}")
            ->assertJsonPath('data.seller_phone', '+48111222333');

        // admin → widzi
        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/auctions/{$this->auction->id}")
            ->assertJsonPath('data.seller_phone', '+48111222333');
    }

    public function test_profile_pii_visibility_matrix()
    {
        $this->owner->update(['is_public' => true, 'email' => 'owner@test.pl']);

        // obcy zweryfikowany NIE widzi e-maila/adresu
        $response = $this->actingAs($this->stranger, 'sanctum')
            ->getJson("/api/users/{$this->owner->id}");
        $response->assertStatus(200);
        $this->assertArrayNotHasKey('email', $response->json('data'));

        $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/users/{$this->owner->id}")
            ->assertJsonPath('data.email', 'owner@test.pl');

        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/users/{$this->owner->id}")
            ->assertJsonPath('data.email', 'owner@test.pl');
    }

    public function test_reporting_permissions_matrix()
    {
        $payload = [
            'auction_id' => $this->auction->id,
            'reason' => 'fraud',
            'description' => 'Podejrzane ogłoszenie do sprawdzenia.',
        ];

        $this->postJson('/api/reports', $payload)->assertStatus(401);

        $this->actingAs($this->unverified, 'sanctum')
            ->postJson('/api/reports', $payload)->assertStatus(403);

        $this->actingAs($this->stranger, 'sanctum')
            ->postJson('/api/reports', $payload)->assertStatus(201);

        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/reports')
            ->assertStatus(200)
            ->assertJsonFragment(['reason' => 'fraud']);
    }

    // ===================== RECENZJE =====================

    public function test_review_permissions_matrix()
    {
        $ended = Auction::factory()->create([
            'user_id' => $this->owner->id,
            'status' => 'ended',
            'ends_at' => now()->subDay(),
        ]);
        Bid::create([
            'auction_id' => $ended->id,
            'user_id' => $this->stranger->id,
            'amount' => 1500,
        ]);

        $payload = ['auction_id' => $ended->id, 'rating' => 5, 'comment' => 'Świetny kontakt'];

        // niezweryfikowany → 403
        $this->actingAs($this->unverified, 'sanctum')
            ->postJson('/api/reviews', $payload)->assertStatus(403);

        $other = User::factory()->create(['is_active' => true]);
        $this->actingAs($other, 'sanctum')
            ->postJson('/api/reviews', $payload)->assertStatus(422);

        $this->actingAs($this->stranger, 'sanctum')
            ->postJson('/api/reviews', $payload)->assertSuccessful();
    }

    // ===================== PANEL ADMINA =====================

    public function test_admin_panel_permissions_matrix()
    {
        $endpoints = [
            ['GET', '/api/admin/dashboard'],
            ['GET', '/api/admin/users'],
            ['GET', '/api/admin/auctions'],
            ['GET', '/api/admin/reports'],
            ['GET', '/api/admin/audit-logs'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $this->json($method, $uri)->assertStatus(401);
        }
        $this->actingAs($this->stranger, 'sanctum');
        foreach ($endpoints as [$method, $uri]) {
            $this->json($method, $uri)->assertStatus(403);
        }
        // admin → 200
        $this->actingAs($this->admin, 'sanctum');
        foreach ($endpoints as [$method, $uri]) {
            $this->json($method, $uri)->assertStatus(200);
        }
    }

    public function test_admin_moderation_flow()
    {
        $response = $this->actingAs($this->stranger, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload());
        $auctionId = $response->json('data.id');
        $this->assertEquals('pending', Auction::find($auctionId)->status);

        $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/admin/auctions/{$auctionId}/approve")->assertStatus(403);

        // admin zatwierdza → active + wpis w audit logu
        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/admin/auctions/{$auctionId}/approve")->assertStatus(200);
        $this->assertEquals('active', Auction::find($auctionId)->status);
        $this->assertDatabaseHas('audit_logs', [
            'admin_user_id' => $this->admin->id,
            'action' => 'approved',
            'model_id' => $auctionId,
        ]);
    }

    public function test_admin_settings_and_pages_matrix()
    {
        LegalPage::create(['slug' => 'terms', 'title' => 'Regulamin', 'content' => '## Test']);

        $this->actingAs($this->stranger, 'sanctum')
            ->patchJson('/api/admin/settings', ['bidding_enabled' => true])->assertStatus(403);
        $this->actingAs($this->stranger, 'sanctum')
            ->patchJson('/api/admin/pages/terms', ['title' => 'Hack'])->assertStatus(403);

        // admin tak
        $this->actingAs($this->admin, 'sanctum')
            ->patchJson('/api/admin/settings', ['bidding_enabled' => false])->assertStatus(200);
        $this->actingAs($this->admin, 'sanctum')
            ->patchJson('/api/admin/pages/terms', ['title' => 'Regulamin v2'])->assertStatus(200);

        $this->actingAs($this->stranger, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1100])
            ->assertStatus(422);
    }

    public function test_winner_contact_exchange_matrix()
    {
        $winner = User::factory()->create([
            'is_active' => true,
            'phone' => '+48999888777',
            'email' => 'zwyciezca@test.pl',
        ]);
        $ended = Auction::factory()->create([
            'user_id' => $this->owner->id,
            'status' => 'ended',
            'winner_id' => $winner->id,
            'ends_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/auctions/{$ended->id}");
        $response->assertJsonPath('data.winner.phone', '+48999888777')
            ->assertJsonPath('data.winner.email', 'zwyciezca@test.pl');

        // obcy zweryfikowany NIE widzi
        $this->actingAs($this->stranger, 'sanctum')
            ->getJson("/api/auctions/{$ended->id}")
            ->assertJsonPath('data.winner.phone', null)
            ->assertJsonPath('data.winner.email', null);

        $guest = $this->getJson("/api/auctions/{$ended->id}");
        $guest->assertJsonPath('data.winner.phone', null);

        // admin widzi
        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/auctions/{$ended->id}")
            ->assertJsonPath('data.winner.phone', '+48999888777');

        $this->auction->update(['winner_id' => $winner->id]);
        $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/auctions/{$this->auction->id}")
            ->assertJsonPath('data.winner.phone', null);
    }

    public function test_bid_history_contact_gating()
    {
        $winner = User::factory()->create([
            'is_active' => true,
            'phone' => '+48999888777',
            'first_name' => 'Adam',
            'last_name' => 'Wygrany',
        ]);
        $ended = Auction::factory()->create([
            'user_id' => $this->owner->id,
            'status' => 'ended',
            'winner_id' => $winner->id,
            'ends_at' => now()->subHour(),
        ]);
        Bid::create(['auction_id' => $ended->id, 'user_id' => $winner->id, 'amount' => 2000]);
        Bid::create(['auction_id' => $ended->id, 'user_id' => $this->stranger->id, 'amount' => 1500]);

        $asOwner = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/auctions/{$ended->id}/bids")->json('data');
        $this->assertNotEmpty($asOwner[0]['bidder_name']);
        $this->assertNotEmpty($asOwner[0]['created_at']);

        $this->assertEquals('+48999888777', $asOwner[0]['bidder_phone']);
        $this->assertEquals('Adam Wygrany', $asOwner[0]['bidder_full_name']);
        $this->assertNull($asOwner[1]['bidder_phone']);

        $asStranger = $this->actingAs($this->stranger, 'sanctum')
            ->getJson("/api/auctions/{$ended->id}/bids")->json('data');
        $this->assertNull($asStranger[0]['bidder_phone']);

        Bid::create(['auction_id' => $this->auction->id, 'user_id' => $winner->id, 'amount' => 1100]);
        $active = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/auctions/{$this->auction->id}/bids")->json('data');
        $this->assertNull($active[0]['bidder_phone']);
    }

    // ===================== BAN =====================

    public function test_banned_user_is_blocked()
    {
        $banned = User::factory()->create([
            'is_active' => true,
            'is_banned' => true,
            'ban_until' => now()->addDays(7),
        ]);

        $this->actingAs($banned, 'sanctum')
            ->postJson("/api/bids/auction/{$this->auction->id}", ['amount' => 1100])
            ->assertStatus(422)
            ->assertJsonPath('code', 'USER_BANNED');

        $this->actingAs($banned, 'sanctum')
            ->postJson('/api/auctions', $this->auctionPayload())
            ->assertStatus(422)
            ->assertJsonPath('code', 'USER_BANNED');
    }
}
