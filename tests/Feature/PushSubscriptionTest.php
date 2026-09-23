<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function vapid_public_key_endpoint_is_public_and_returns_configured_key()
    {
        config(['services.vapid.public_key' => 'test-public-key']);

        $response = $this->getJson('/api/push/vapid-public-key');

        $response->assertStatus(200)
            ->assertJson(['publicKey' => 'test-public-key']);
    }

    /** @test */
    public function guest_cannot_subscribe_to_push()
    {
        $response = $this->postJson('/api/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc',
            'keys' => ['p256dh' => 'key1', 'auth' => 'key2'],
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_subscribe_to_push_notifications()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'keys' => ['p256dh' => 'public-key-value', 'auth' => 'auth-token-value'],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'public_key' => 'public-key-value',
            'auth_token' => 'auth-token-value',
        ]);
    }

    /** @test */
    public function subscribing_twice_with_the_same_endpoint_updates_instead_of_duplicating()
    {
        $user = User::factory()->create();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/dup';

        $this->actingAs($user)->postJson('/api/push/subscribe', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'old-key', 'auth' => 'old-auth'],
        ]);
        $this->actingAs($user)->postJson('/api/push/subscribe', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'new-key', 'auth' => 'new-auth'],
        ]);

        $this->assertEquals(1, PushSubscription::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('push_subscriptions', ['endpoint' => $endpoint, 'public_key' => 'new-key']);
    }

    /** @test */
    public function user_can_unsubscribe()
    {
        $user = User::factory()->create();
        $subscription = PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/to-remove',
            'public_key' => 'k',
            'auth_token' => 'a',
        ]);

        $response = $this->actingAs($user)->postJson('/api/push/unsubscribe', [
            'endpoint' => $subscription->endpoint,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('push_subscriptions', ['id' => $subscription->id]);
    }

    /** @test */
    public function user_cannot_unsubscribe_someone_elses_subscription()
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $subscription = PushSubscription::create([
            'user_id' => $owner->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/protected',
            'public_key' => 'k',
            'auth_token' => 'a',
        ]);

        $this->actingAs($attacker)->postJson('/api/push/unsubscribe', [
            'endpoint' => $subscription->endpoint,
        ]);

        $this->assertDatabaseHas('push_subscriptions', ['id' => $subscription->id]);
    }
}
