<?php

namespace Tests\Feature\Message;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_send_message_with_content_field()
    {
        $sender = User::factory()->create(['is_active' => true]);
        $recipient = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($sender)
            ->postJson("/api/messages/user/{$recipient->id}", [
                'content' => 'Hello, this is a test message',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'sender_id',
                'recipient_id',
                'content',
                'created_at',
            ])
            ->assertJsonPath('content', 'Hello, this is a test message');

        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'content' => 'Hello, this is a test message',
        ]);
    }

    /** @test */
    public function user_can_view_conversation()
    {
        $user1 = User::factory()->create(['is_active' => true]);
        $user2 = User::factory()->create(['is_active' => true]);

        Message::create([
            'sender_id' => $user1->id,
            'recipient_id' => $user2->id,
            'content' => 'First message',
        ]);

        Message::create([
            'sender_id' => $user2->id,
            'recipient_id' => $user1->id,
            'content' => 'Reply message',
        ]);

        $response = $this->actingAs($user1)
            ->getJson("/api/messages/user/{$user2->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.content', 'First message')
            ->assertJsonPath('data.1.content', 'Reply message');
    }

    /** @test */
    public function user_can_mark_message_as_read()
    {
        $sender = User::factory()->create(['is_active' => true]);
        $recipient = User::factory()->create(['is_active' => true]);

        $message = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'content' => 'Test message',
            'read_at' => null,
        ]);

        $response = $this->actingAs($recipient)
            ->postJson("/api/messages/{$message->id}/read");

        $response->assertStatus(200);

        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    /** @test */
    public function user_cannot_send_empty_message()
    {
        $sender = User::factory()->create(['is_active' => true]);
        $recipient = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($sender)
            ->postJson("/api/messages/user/{$recipient->id}", [
                'content' => '',
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_cannot_send_message_to_blocked_user()
    {
        $sender = User::factory()->create(['is_active' => true]);
        $recipient = User::factory()->create(['is_active' => true]);

        // Recipient blocks sender
        $recipient->blockedUsers()->attach($sender->id);

        $response = $this->actingAs($sender)
            ->postJson("/api/messages/user/{$recipient->id}", [
                'content' => 'This should be blocked',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function banned_user_cannot_send_messages()
    {
        $sender = User::factory()->create(['ban_until' => now()->addDays(7)]);
        $recipient = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($sender)
            ->postJson("/api/messages/user/{$recipient->id}", [
                'content' => 'Test message',
            ]);

        $response->assertStatus(403);
    }
}
