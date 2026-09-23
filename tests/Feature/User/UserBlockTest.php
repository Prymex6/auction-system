<?php

namespace Tests\Feature\User;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBlockTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_block_another_user()
    {
        $user = User::factory()->create();
        $userToBlock = User::factory()->create();

        $user->blockedUsers()->attach($userToBlock->id);

        $this->assertTrue($user->blockedUsers->contains($userToBlock));
        $this->assertDatabaseHas('user_blocks', [
            'blocker_id' => $user->id,
            'blocked_user_id' => $userToBlock->id,
        ]);
    }

    /** @test */
    public function user_can_unblock_user()
    {
        $user = User::factory()->create();
        $blockedUser = User::factory()->create();

        $user->blockedUsers()->attach($blockedUser->id);
        $user->blockedUsers()->detach($blockedUser->id);

        $this->assertFalse($user->blockedUsers->contains($blockedUser));
        $this->assertDatabaseMissing('user_blocks', [
            'blocker_id' => $user->id,
            'blocked_user_id' => $blockedUser->id,
        ]);
    }

    /** @test */
    public function blocked_user_cannot_send_messages()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $recipient->blockedUsers()->attach($sender->id);

        $response = $this->actingAs($sender)
            ->postJson("/api/messages/user/{$recipient->id}", [
                'content' => 'This should be blocked',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_block_themselves()
    {
        $user = User::factory()->create();

        // Try to block yourself using the blockUser method
        $result = $user->blockUser($user->id);

        // Should return false
        $this->assertFalse($result);

        // Should not be in database
        $this->assertDatabaseMissing('user_blocks', [
            'blocker_id' => $user->id,
            'blocked_user_id' => $user->id,
        ]);
    }

    /** @test */
    public function user_can_block_another_user_via_api()
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/users/{$target->id}/block");

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_blocks', [
            'blocker_id' => $user->id,
            'blocked_user_id' => $target->id,
        ]);
    }

    /** @test */
    public function user_cannot_block_themselves_via_api()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/users/{$user->id}/block");

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_unblock_via_api()
    {
        $user = User::factory()->create();
        $target = User::factory()->create();
        $user->blockedUsers()->attach($target->id);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/users/{$target->id}/block");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('user_blocks', [
            'blocker_id' => $user->id,
            'blocked_user_id' => $target->id,
        ]);
    }

    /** @test */
    public function blocked_user_cannot_message_the_blocker_end_to_end_via_api()
    {
        $blocker = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $blocked = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);

        $this->actingAs($blocker, 'sanctum')->postJson("/api/users/{$blocked->id}/block")->assertStatus(200);

        $response = $this->actingAs($blocked, 'sanctum')->postJson("/api/messages/user/{$blocker->id}", [
            'content' => 'Probuje napisac mimo blokady',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_block_a_user()
    {
        $target = User::factory()->create();

        $this->postJson("/api/users/{$target->id}/block")->assertStatus(401);
    }

    /** @test */
    public function public_profile_reports_block_status_for_viewer()
    {
        $blocker = User::factory()->create();
        $target = User::factory()->create();
        Auction::factory()->create(['user_id' => $target->id, 'status' => 'active']);

        $before = $this->actingAs($blocker, 'sanctum')->getJson("/api/users/{$target->id}");
        $before->assertStatus(200)->assertJsonPath('data.is_blocked_by_viewer', false);

        $this->actingAs($blocker, 'sanctum')->postJson("/api/users/{$target->id}/block")->assertStatus(200);

        $after = $this->actingAs($blocker, 'sanctum')->getJson("/api/users/{$target->id}");
        $after->assertStatus(200)->assertJsonPath('data.is_blocked_by_viewer', true);
    }
}
