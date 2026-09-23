<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingConversationsTest extends TestCase
{
    use RefreshDatabase;

    private User $anna;

    private User $piotr;

    protected function setUp(): void
    {
        parent::setUp();
        $this->anna = User::factory()->create(['name' => 'anna_h', 'is_active' => true]);
        $this->piotr = User::factory()->create(['name' => 'piotr_h', 'is_active' => true]);

        Message::create(['sender_id' => $this->anna->id, 'recipient_id' => $this->piotr->id, 'content' => 'Dzień dobry, czy gołąb dostępny?']);
        Message::create(['sender_id' => $this->piotr->id, 'recipient_id' => $this->anna->id, 'content' => 'Tak, zapraszam.']);
        Message::create(['sender_id' => $this->anna->id, 'recipient_id' => $this->piotr->id, 'content' => 'Świetnie, biorę!']);
    }

    public function test_conversations_return_partner_summary_not_raw_messages()
    {
        $response = $this->actingAs($this->piotr, 'sanctum')->getJson('/api/messages/conversations');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
        $conv = $response->json('data.0');

        $this->assertSame($this->anna->id, $conv['user_id']);
        $this->assertSame('anna_h', $conv['user_name']);
        $this->assertSame('Świetnie, biorę!', $conv['last_message']);
        $this->assertSame(2, $conv['unread_count']); // 2 nieprzeczytane od Anny
        $this->assertArrayHasKey('last_message_at', $conv);
    }

    public function test_conversation_messages_are_chronological_with_is_sent_flags()
    {
        $response = $this->actingAs($this->piotr, 'sanctum')
            ->getJson('/api/messages/user/'.$this->anna->id);

        $response->assertStatus(200)->assertJsonCount(3, 'data');
        $data = $response->json('data');

        $this->assertSame('Dzień dobry, czy gołąb dostępny?', $data[0]['content']);
        $this->assertFalse($data[0]['is_sent']);
        $this->assertTrue($data[1]['is_sent']);

        $unread = $this->actingAs($this->piotr, 'sanctum')->getJson('/api/messages/unread-count');
        $unread->assertStatus(200)->assertJsonPath('unread_count', 0);
    }

    public function test_conversations_skip_deleted_partner()
    {
        $ghost = User::factory()->create(['is_active' => true]);
        Message::create(['sender_id' => $ghost->id, 'recipient_id' => $this->piotr->id, 'content' => 'Widmo']);
        $ghost->delete();

        $response = $this->actingAs($this->piotr, 'sanctum')->getJson('/api/messages/conversations');
        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_send_message_to_themselves()
    {
        $response = $this->actingAs($this->piotr, 'sanctum')
            ->postJson('/api/messages/user/'.$this->piotr->id, [
                'content' => 'Notatka do siebie',
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('messages', [
            'sender_id' => $this->piotr->id,
            'recipient_id' => $this->piotr->id,
        ]);
    }
}
