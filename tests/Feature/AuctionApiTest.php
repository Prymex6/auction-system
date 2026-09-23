<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Category;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $this->user = User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->admin = User::factory()->create([
            'is_admin' => true,
            'is_active' => true,
        ]);
    }

    public function test_can_list_active_auctions()
    {
        Auction::factory()->count(10)->create([
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/auctions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'current_price',
                        'status',
                        'ends_at',
                    ],
                ],
            ]);
    }

    public function test_expired_but_not_yet_closed_auction_is_hidden_when_sort_param_present()
    {
        // ends_at > now(). CloseExpiredAuctions (co minute) sprzata status,
        $expired = Auction::factory()->create([
            'status' => 'active',
            'ends_at' => now()->subMinute(),
            'category_id' => $this->category->id,
        ]);
        $stillActive = Auction::factory()->create([
            'status' => 'active',
            'ends_at' => now()->addDay(),
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/auctions?sort=newest');

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse($ids->contains($expired->id));
        $this->assertTrue($ids->contains($stillActive->id));
    }

    public function test_can_view_single_auction()
    {
        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'active',
            'title' => 'Test Auction',
        ]);

        $response = $this->getJson("/api/auctions/{$auction->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $auction->id)
            ->assertJsonPath('data.title', 'Test Auction');
    }

    public function test_authenticated_user_can_create_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auctionData = [
            'title' => 'My New Auction',
            'description' => 'Detailed description of the pigeon',
            'breed' => 'Pocztowy Belgijski',
            'year' => 2024,
            'gender' => 'samiec',
            'color' => 'Niebieska',
            'ring_number' => 'PL-2024-12345',
            'size' => 'sredni',
            'pigeon_images' => ['auctions/test-pigeon.jpg'],
            'start_price' => 500,
            'type' => 'auction',
            'ends_at' => now()->addDays(7)->toDateTimeString(),
            'category_id' => $this->category->id,
        ];

        $response = $this->postJson('/api/auctions', $auctionData);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'My New Auction');

        $this->assertDatabaseHas('auctions', [
            'title' => 'My New Auction',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_guest_cannot_create_auction()
    {
        $auctionData = [
            'title' => 'Unauthorized Auction',
            'description' => 'Should fail',
            'start_price' => 100,
            'category_id' => $this->category->id,
        ];

        $response = $this->postJson('/api/auctions', $auctionData);

        $response->assertStatus(401); // Unauthorized
    }

    public function test_can_search_auctions_by_breed()
    {
        Auction::factory()->create([
            'breed' => 'Pocztowy Belgijski',
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        Auction::factory()->create([
            'breed' => 'Grzywacz Polski',
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/auctions?breed=Pocztowy%20Belgijski');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        foreach ($data as $auction) {
            $this->assertEquals('Pocztowy Belgijski', $auction['breed']);
        }
    }

    public function test_can_filter_auctions_by_price_range()
    {
        Auction::factory()->create([
            'start_price' => 100,
            'current_price' => 100,
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        Auction::factory()->create([
            'start_price' => 5000,
            'current_price' => 5000,
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson('/api/auctions?min_price=1000&max_price=10000');

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $auction) {
            $this->assertGreaterThanOrEqual(1000, $auction['current_price']);
            $this->assertLessThanOrEqual(10000, $auction['current_price']);
        }
    }

    public function test_owner_can_update_own_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Original Title',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/auctions/{$auction->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('auctions', [
            'id' => $auction->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_others_auction()
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->putJson("/api/auctions/{$auction->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403); // Forbidden
    }

    public function test_admin_can_update_any_auction()
    {
        $this->actingAs($this->admin, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'User Auction',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/auctions/{$auction->id}", [
            'title' => 'Admin Updated',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Admin Updated');
    }

    public function test_auction_validation_fails_with_invalid_data()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/auctions', [
            'title' => '', // Invalid: required
            'start_price' => -100, // Invalid: must be positive
            'type' => 'invalid_type', // Invalid: must be auction or buy_now
        ]);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['title', 'start_price', 'type']);
    }

    public function test_owner_cannot_update_active_licytacja_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'auction',
            'status' => 'active',
        ]);

        $response = $this->putJson("/api/auctions/{$auction->id}", ['title' => 'Zmiana warunkow w trakcie licytacji']);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('auctions', ['id' => $auction->id, 'title' => 'Zmiana warunkow w trakcie licytacji']);
    }

    public function test_owner_cannot_update_ended_licytacja_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'auction',
            'status' => 'ended',
        ]);

        $response = $this->putJson("/api/auctions/{$auction->id}", ['title' => 'Proba edycji zakonczonej']);

        $response->assertStatus(422);
    }

    public function test_owner_editing_active_buy_now_auction_sends_it_back_to_pending()
    {
        PlatformSetting::updateOrCreate(['id' => 1], ['require_auction_approval' => true]);
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
            'title' => 'Oryginalny tytul',
        ]);

        $response = $this->putJson("/api/auctions/{$auction->id}", ['title' => 'Zmieniony tytul po aktywacji']);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Zmieniony tytul po aktywacji')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('requeued_for_approval', true);

        $this->assertDatabaseHas('auctions', [
            'id' => $auction->id,
            'title' => 'Zmieniony tytul po aktywacji',
            'status' => 'pending',
        ]);
    }

    public function test_requeued_buy_now_auction_disappears_from_public_listing_until_reapproved()
    {
        PlatformSetting::updateOrCreate(['id' => 1], ['require_auction_approval' => true]);
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
            'title' => 'Widoczne zanim edytowane',
            'ends_at' => now()->addDays(7),
        ]);

        $this->putJson("/api/auctions/{$auction->id}", ['title' => 'Po edycji - powinno zniknac z listy']);

        $publicList = $this->getJson('/api/auctions')->json('data');
        $titles = collect($publicList)->pluck('title');
        $this->assertNotContains('Po edycji - powinno zniknac z listy', $titles);

        // Admin zatwierdza ponownie - wraca na publiczna liste.
        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/admin/auctions/{$auction->id}/approve")
            ->assertStatus(200);

        $publicListAfterApproval = $this->getJson('/api/auctions')->json('data');
        $titlesAfter = collect($publicListAfterApproval)->pluck('title');
        $this->assertContains('Po edycji - powinno zniknac z listy', $titlesAfter);
    }

    public function test_admin_editing_active_buy_now_auction_does_not_requeue_it()
    {
        PlatformSetting::updateOrCreate(['id' => 1], ['require_auction_approval' => true]);
        $this->actingAs($this->admin, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
        ]);

        $response = $this->putJson("/api/auctions/{$auction->id}", ['title' => 'Poprawka admina']);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('requeued_for_approval', false);
    }

    public function test_editing_active_buy_now_auction_does_not_requeue_when_platform_approval_disabled()
    {
        PlatformSetting::updateOrCreate(['id' => 1], ['require_auction_approval' => false]);
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
        ]);

        $response = $this->putJson("/api/auctions/{$auction->id}", ['title' => 'Edycja bez moderacji']);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('requeued_for_approval', false);
    }

    public function test_empty_update_payload_does_not_requeue_active_buy_now_auction()
    {
        PlatformSetting::updateOrCreate(['id' => 1], ['require_auction_approval' => true]);
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
        ]);

        $response = $this->putJson("/api/auctions/{$auction->id}", []);

        $response->assertStatus(200)->assertJsonPath('requeued_for_approval', false);
        $this->assertDatabaseHas('auctions', ['id' => $auction->id, 'status' => 'active']);
    }

    public function test_owner_can_delete_own_pending_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/api/auctions/{$auction->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('auctions', ['id' => $auction->id]);
    }

    public function test_owner_cannot_delete_active_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $response = $this->deleteJson("/api/auctions/{$auction->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('auctions', ['id' => $auction->id]);
    }

    public function test_owner_cannot_delete_active_buy_now_auction_either()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
        ]);

        $response = $this->deleteJson("/api/auctions/{$auction->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('auctions', ['id' => $auction->id]);
    }

    public function test_owner_cannot_delete_ended_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'ended',
        ]);

        $response = $this->deleteJson("/api/auctions/{$auction->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('auctions', ['id' => $auction->id]);
    }

    public function test_user_cannot_delete_others_auction()
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/api/auctions/{$auction->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('auctions', ['id' => $auction->id]);
    }

    public function test_guest_cannot_delete_auction()
    {
        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);

        $this->deleteJson("/api/auctions/{$auction->id}")->assertStatus(401);

        $this->assertDatabaseHas('auctions', ['id' => $auction->id]);
    }

    public function test_owner_can_complete_own_active_buy_now_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
        ]);

        $response = $this->patchJson("/api/auctions/{$auction->id}/complete");

        $response->assertStatus(200)->assertJsonPath('data.status', 'ended');
        $this->assertDatabaseHas('auctions', ['id' => $auction->id, 'status' => 'ended']);
        $this->assertNotNull($auction->fresh()->ended_at);
    }

    public function test_owner_cannot_complete_own_active_licytacja_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'auction',
            'status' => 'active',
        ]);

        $response = $this->patchJson("/api/auctions/{$auction->id}/complete");

        $response->assertStatus(422);
        $this->assertDatabaseHas('auctions', ['id' => $auction->id, 'status' => 'active']);
    }

    public function test_owner_cannot_complete_active_both_type_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'both',
            'status' => 'active',
        ]);

        $response = $this->patchJson("/api/auctions/{$auction->id}/complete");

        $response->assertStatus(422);
    }

    public function test_cannot_complete_already_ended_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'ended',
        ]);

        $response = $this->patchJson("/api/auctions/{$auction->id}/complete");

        $response->assertStatus(422)->assertJsonPath('message', 'Aukcja jest już zakończona');
    }

    public function test_owner_can_complete_own_pending_buy_now_auction()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'pending',
        ]);

        $response = $this->patchJson("/api/auctions/{$auction->id}/complete");

        $response->assertStatus(200)->assertJsonPath('data.status', 'ended');
    }

    public function test_user_cannot_complete_others_auction()
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
        ]);

        $response = $this->patchJson("/api/auctions/{$auction->id}/complete");

        $response->assertStatus(403);
        $this->assertDatabaseHas('auctions', ['id' => $auction->id, 'status' => 'active']);
    }

    public function test_guest_cannot_complete_auction()
    {
        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
        ]);

        $this->patchJson("/api/auctions/{$auction->id}/complete")->assertStatus(401);
    }

    public function test_completed_buy_now_auction_disappears_from_public_listing()
    {
        $this->actingAs($this->user, 'sanctum');

        $auction = Auction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'buy_now',
            'status' => 'active',
            'title' => 'Znika po zakonczeniu',
            'ends_at' => now()->addDays(7),
        ]);

        $this->patchJson("/api/auctions/{$auction->id}/complete");

        $publicList = $this->getJson('/api/auctions')->json('data');
        $this->assertNotContains('Znika po zakonczeniu', collect($publicList)->pluck('title'));
    }
}
