<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
    }

    private function makeNotification(User $user, bool $read = false): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => 'bid_placed',
            'title' => 'Nowa oferta',
            'message' => 'Ktoś złożył ofertę na Twoją aukcję',
            'read' => $read,
        ]);
    }

    public function test_user_can_list_own_notifications()
    {
        $this->makeNotification($this->user);
        $otherUser = User::factory()->create(['is_active' => true]);
        $this->makeNotification($otherUser);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/notifications');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_returns_unread_count()
    {
        $this->makeNotification($this->user, read: false);
        $this->makeNotification($this->user, read: false);
        $this->makeNotification($this->user, read: true);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/notifications');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('unread_count'));
    }

    public function test_index_can_filter_unread_only()
    {
        $this->makeNotification($this->user, read: false);
        $this->makeNotification($this->user, read: true);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications?unread_only=1');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_unread_count_endpoint()
    {
        $this->makeNotification($this->user, read: false);
        $this->makeNotification($this->user, read: false);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/notifications/unread-count');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('unread_count'));
    }

    public function test_user_can_mark_notification_as_read()
    {
        $notification = $this->makeNotification($this->user);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $this->assertTrue($notification->fresh()->read);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read()
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $notification = $this->makeNotification($otherUser);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/notifications/{$notification->id}/read")
            ->assertStatus(403);

        $this->assertFalse($notification->fresh()->read);
    }

    public function test_user_can_mark_all_as_read()
    {
        $this->makeNotification($this->user, read: false);
        $this->makeNotification($this->user, read: false);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/notifications/mark-all-read');

        $response->assertStatus(200);
        $this->assertEquals(0, $this->user->notifications()->where('read', false)->count());
    }

    public function test_mark_all_as_read_does_not_affect_other_users()
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $otherNotification = $this->makeNotification($otherUser, read: false);
        $this->makeNotification($this->user, read: false);

        $this->actingAs($this->user, 'sanctum')->postJson('/api/notifications/mark-all-read')->assertStatus(200);

        $this->assertFalse($otherNotification->fresh()->read);
    }

    public function test_user_can_delete_own_notification()
    {
        $notification = $this->makeNotification($this->user);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);
        $this->assertNull(Notification::find($notification->id));
    }

    public function test_user_cannot_delete_another_users_notification()
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $notification = $this->makeNotification($otherUser);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/notifications/{$notification->id}")
            ->assertStatus(403);

        $this->assertNotNull(Notification::find($notification->id));
    }

    public function test_guest_cannot_access_notifications()
    {
        $this->getJson('/api/notifications')->assertStatus(401);
    }
}
