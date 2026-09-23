<?php

namespace Tests\Feature\Admin;

use App\Models\Auction;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create();
        // AdminController::createAuctionWithListing() zaklada na sztywno
        Category::factory()->create();
    }

    /** @test */
    public function non_admin_cannot_access_admin_panel()
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'stats' => [
                    'total_users',
                    'active_auctions',
                    'total_auctions',
                    'banned_users',
                ],
            ]);
    }

    /** @test */
    public function admin_can_ban_user()
    {
        $userToBan = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/users/{$userToBan->id}/ban", [
                'reason' => 'Spam and inappropriate content',
            ]);

        $response->assertStatus(200);

        $userToBan->refresh();
        $this->assertTrue($userToBan->is_banned);
        $this->assertEquals('Spam and inappropriate content', $userToBan->ban_reason);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'admin_user_id' => $this->admin->id,
            'action' => 'banned',
            'model_type' => 'User',
            'model_id' => $userToBan->id,
        ]);
    }

    /** @test */
    public function admin_can_unban_user()
    {
        $bannedUser = User::factory()->create([
            'is_banned' => true,
            'ban_reason' => 'Spam',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/users/{$bannedUser->id}/unban");

        $response->assertStatus(200);

        $bannedUser->refresh();
        $this->assertFalse($bannedUser->is_banned);
    }

    /** @test */
    public function admin_can_approve_listing()
    {
        $auction = Auction::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/auctions/{$auction->id}/approve");

        $response->assertStatus(200);

        $auction->refresh();
        $this->assertEquals('active', $auction->status);

        // Check audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'admin_user_id' => $this->admin->id,
            'action' => 'approved',
            'model_type' => 'Auction',
            'model_id' => $auction->id,
        ]);
    }

    /** @test */
    public function admin_can_reject_listing()
    {
        $auction = Auction::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/auctions/{$auction->id}/reject", [
                'reason' => 'Offensive content and violates guidelines',
            ]);

        $response->assertStatus(200);

        $auction->refresh();
        $this->assertEquals('cancelled', $auction->status);
    }

    /** @test */
    public function admin_can_view_pending_listings()
    {
        Auction::factory(5)->create(['status' => 'pending']);
        Auction::factory(3)->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/auctions');

        $response->assertStatus(200);

        $pending = collect($response->json('data'))->where('status', 'pending');
        $this->assertCount(5, $pending);
    }

    /** @test */
    public function admin_can_view_audit_logs()
    {
        // Create some audit logs
        AuditLog::factory(5)->create(['admin_user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'action', 'model_type', 'created_at'],
                ],
            ]);
    }

    /** @test */
    public function admin_can_get_audit_stats()
    {
        AuditLog::factory(10)->create(['action' => 'banned']);
        AuditLog::factory(5)->create(['action' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/audit-logs/stats?period=month');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_actions',
                'by_action',
                'by_model',
                'by_admin',
            ]);
    }

    /** @test */
    public function admin_actions_are_logged()
    {
        $userToBan = User::factory()->create();

        $this->actingAs($this->admin)
            ->postJson("/api/admin/users/{$userToBan->id}/ban", [
                'reason' => 'Test ban',
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'admin_user_id' => $this->admin->id,
            'action' => 'banned',
            'model_type' => 'User',
        ]);
    }

    /** @test */
    public function admin_can_create_auction_with_listing_using_only_required_fields()
    {
        $seller = User::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $seller->id,
            'start_price' => 100,
            'title' => 'Minimalna aukcja testowa',
            'breed' => 'Janssen',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('auctions', [
            'title' => 'Minimalna aukcja testowa',
            'user_id' => $seller->id,
            'color' => null,
            'size' => null,
        ]);
    }

    /** @test */
    public function admin_can_create_auction_with_listing_using_full_payload()
    {
        $seller = User::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $seller->id,
            'start_price' => 150,
            'title' => 'Pelna aukcja testowa',
            'breed' => 'Janssen',
            'color' => 'Niebieska',
            'size' => 'sredni',
            'description' => 'Opis testowy',
            'status' => 'active',
            'type' => 'buy_now',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('auctions', [
            'title' => 'Pelna aukcja testowa',
            'color' => 'Niebieska',
            'size' => 'sredni',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_create_buy_now_auction_for_user_even_when_only_admin_can_list_is_enabled()
    {
        PlatformSetting::updateOrCreate(['id' => 1], ['only_admin_can_list' => true]);
        $seller = User::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $seller->id,
            'start_price' => 200,
            'title' => 'Aukcja mimo blokady wystawiania',
            'breed' => 'Janssen',
            'type' => 'buy_now',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('auctions', [
            'title' => 'Aukcja mimo blokady wystawiania',
            'user_id' => $seller->id,
            'type' => 'buy_now',
        ]);
    }

    /** @test */
    public function admin_can_create_auction_for_user_who_already_reached_daily_listing_limit()
    {
        PlatformSetting::updateOrCreate(['id' => 1], ['max_auctions_per_day' => 1]);
        $seller = User::factory()->create();
        Auction::factory()->create(['user_id' => $seller->id, 'created_at' => now()]);

        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $seller->id,
            'start_price' => 150,
            'title' => 'Druga aukcja mimo limitu dziennego',
            'breed' => 'Janssen',
            'type' => 'buy_now',
        ]);

        $response->assertStatus(201);
        $this->assertEquals(2, Auction::where('user_id', $seller->id)->count());
    }

    /** @test */
    public function admin_can_create_auction_for_user_who_reached_active_auction_limit()
    {
        PlatformSetting::updateOrCreate(['id' => 1], ['free_user_auction_limit' => 1]);
        $seller = User::factory()->create();
        Auction::factory()->create(['user_id' => $seller->id, 'status' => 'active']);

        $this->assertFalse(app(UserService::class)->canListAuction($seller->fresh()));

        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $seller->id,
            'start_price' => 150,
            'title' => 'Druga aktywna aukcja mimo limitu',
            'breed' => 'Janssen',
            'type' => 'buy_now',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function admin_created_buy_now_auction_defaults_to_pending_status_awaiting_admin_approval()
    {
        $seller = User::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $seller->id,
            'start_price' => 150,
            'title' => 'Czeka na potwierdzenie',
            'breed' => 'Janssen',
            'type' => 'buy_now',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('auction.status', 'pending');
        $this->assertDatabaseHas('auctions', [
            'title' => 'Czeka na potwierdzenie',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function admin_can_subsequently_approve_the_pending_auction_they_created_for_a_user()
    {
        $seller = User::factory()->create();

        $createResponse = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $seller->id,
            'start_price' => 150,
            'title' => 'Do zatwierdzenia',
            'breed' => 'Janssen',
            'type' => 'buy_now',
        ]);
        $auctionId = $createResponse->json('auction.id');

        $approveResponse = $this->actingAs($this->admin)
            ->postJson("/api/admin/auctions/{$auctionId}/approve");

        $approveResponse->assertStatus(200);
        $this->assertDatabaseHas('auctions', [
            'id' => $auctionId,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function non_admin_cannot_create_auction_with_listing_for_another_user()
    {
        $response = $this->actingAs($this->regularUser)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $this->regularUser->id,
            'start_price' => 100,
            'title' => 'Proba nieadmina',
            'breed' => 'Janssen',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('auctions', ['title' => 'Proba nieadmina']);
    }

    /** @test */
    public function guest_cannot_create_auction_with_listing()
    {
        $response = $this->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $this->regularUser->id,
            'start_price' => 100,
            'title' => 'Proba goscia',
            'breed' => 'Janssen',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function creating_auction_with_listing_requires_an_existing_user_id()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => 999999,
            'start_price' => 100,
            'title' => 'Nieistniejacy user',
            'breed' => 'Janssen',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }

    /** @test */
    public function creating_auction_with_listing_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'start_price', 'title', 'breed']);
    }

    /** @test */
    public function creating_auction_with_listing_rejects_invalid_type()
    {
        $seller = User::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $seller->id,
            'start_price' => 100,
            'title' => 'Zly typ',
            'breed' => 'Janssen',
            'type' => 'not-a-real-type',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    /** @test */
    public function creating_auction_with_listing_logs_an_audit_entry_with_target_user()
    {
        $seller = User::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/admin/auctions-with-listing', [
            'user_id' => $seller->id,
            'start_price' => 100,
            'title' => 'Z logiem audytu',
            'breed' => 'Janssen',
            'type' => 'buy_now',
        ]);

        $auctionId = $response->json('auction.id');
        $this->assertDatabaseHas('audit_logs', [
            'admin_user_id' => $this->admin->id,
            'action' => 'created',
            'model_type' => 'Auction',
            'model_id' => $auctionId,
        ]);
    }

    /** @test */
    public function admin_can_grant_listing_exception_to_a_user_via_update_endpoint()
    {
        $user = User::factory()->create(['can_list_when_restricted' => false]);

        $response = $this->actingAs($this->admin)->patchJson("/api/admin/users/{$user->id}", [
            'can_list_when_restricted' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'can_list_when_restricted' => true]);
    }

    /** @test */
    public function admin_can_revoke_listing_exception_from_a_user()
    {
        $user = User::factory()->create(['can_list_when_restricted' => true]);

        $response = $this->actingAs($this->admin)->patchJson("/api/admin/users/{$user->id}", [
            'can_list_when_restricted' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'can_list_when_restricted' => false]);
    }

    /** @test */
    public function admin_can_delete_user_account()
    {
        $userToDelete = User::factory()->create([
            'name' => 'jan_kowalski',
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => '123456789',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/users/{$userToDelete->id}");

        $response->assertStatus(200);

        $deleted = User::withTrashed()->find($userToDelete->id);
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNull($deleted->first_name);
        $this->assertNull($deleted->phone);
        $this->assertEquals('Użytkownik usunięty', $deleted->name);
        $this->assertStringContainsString('usuniety+', $deleted->email);
        $this->assertFalse($deleted->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'admin_user_id' => $this->admin->id,
            'action' => 'deleted',
            'model_type' => 'User',
            'model_id' => $userToDelete->id,
        ]);
    }

    /** @test */
    public function admin_cannot_delete_own_account_from_panel()
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/users/{$this->admin->id}");

        $response->assertStatus(422);
        $this->assertNull($this->admin->fresh()->deleted_at);
    }

    /** @test */
    public function non_admin_cannot_delete_user()
    {
        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/admin/users/{$this->admin->id}");

        $response->assertStatus(403);
    }
}
