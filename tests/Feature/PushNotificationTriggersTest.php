<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Category;
use App\Models\PlatformSetting;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushNotificationTriggersTest extends TestCase
{
    use RefreshDatabase;

    private function givePushSubscription(User $user): void
    {
        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-'.$user->id,
            'public_key' => 'BNbezJ_test_public_key_value_placeholder_00000000000000000000000',
            'auth_token' => 'test-auth-token-16b',
        ]);
    }

    /** @test */
    public function registration_succeeds_even_when_admin_has_a_push_subscription()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->givePushSubscription($admin);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'nowy_user',
            'first_name' => 'Nowy',
            'last_name' => 'User',
            'email' => 'nowy@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'nowy@example.com']);
    }

    /** @test */
    public function sending_a_message_succeeds_even_when_recipient_has_a_push_subscription()
    {
        $sender = User::factory()->create(['is_active' => true]);
        $recipient = User::factory()->create(['is_active' => true]);
        $this->givePushSubscription($recipient);

        $response = $this->actingAs($sender)->postJson("/api/messages/user/{$recipient->id}", [
            'content' => 'Cześć, interesuje mnie ten gołąb',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
        ]);
    }

    /** @test */
    public function submitting_a_report_succeeds_even_when_admin_has_a_push_subscription()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->givePushSubscription($admin);
        $reporter = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $seller = User::factory()->create(['is_active' => true]);
        $auction = Auction::factory()->create(['user_id' => $seller->id, 'status' => 'active']);

        $response = $this->actingAs($reporter)->postJson('/api/reports', [
            'auction_id' => $auction->id,
            'reason' => 'spam',
            'description' => 'To wyglada na oszustwo, cos jest nie tak.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reports', ['reportable_id' => $auction->id]);
    }

    /** @test */
    public function creating_an_auction_that_needs_approval_succeeds_even_when_admin_has_a_push_subscription()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->givePushSubscription($admin);
        $seller = User::factory()->create(['is_active' => true]);
        PlatformSetting::updateOrCreate(['id' => 1], ['require_auction_approval' => true]);

        $response = $this->actingAs($seller, 'sanctum')->postJson('/api/auctions', [
            'title' => 'Testowy gołąb do akceptacji',
            'breed' => 'Janssen',
            'gender' => 'samiec',
            'year' => 2024,
            'size' => 'sredni',
            'color' => 'Niebieska',
            'type' => 'buy_now',
            'start_price' => 150,
            'pigeon_images' => ['images/auction/test.jpg'],
        ]);

        $response->assertStatus(201)->assertJsonPath('data.status', 'pending');
        $this->assertDatabaseHas('auctions', ['title' => 'Testowy gołąb do akceptacji', 'status' => 'pending']);
    }

    /** @test */
    public function editing_an_active_buy_now_auction_back_to_pending_succeeds_even_when_admin_has_a_push_subscription()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->givePushSubscription($admin);
        $seller = User::factory()->create(['is_active' => true]);
        PlatformSetting::updateOrCreate(['id' => 1], ['require_auction_approval' => true]);
        $category = Category::factory()->create();
        $auction = Auction::factory()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'type' => 'buy_now',
            'status' => 'active',
        ]);

        $response = $this->actingAs($seller, 'sanctum')->putJson("/api/auctions/{$auction->id}", [
            'title' => 'Zmieniony tytul',
        ]);

        $response->assertStatus(200)->assertJsonPath('requeued_for_approval', true);
        $this->assertDatabaseHas('auctions', ['id' => $auction->id, 'status' => 'pending']);
    }
}
