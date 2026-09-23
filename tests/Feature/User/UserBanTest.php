<?php

namespace Tests\Feature\User;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_ban_uses_correct_field_names()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/users/{$user->id}/ban", [
                'reason' => 'Violation of terms',
            ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue($user->is_banned);
        $this->assertEquals('Violation of terms', $user->ban_reason);
        $this->assertNull($user->ban_until);
    }

    /** @test */
    public function user_can_be_banned_temporarily()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/users/{$user->id}/ban-temporary", [
                'reason' => 'Temporary suspension',
                'hours' => 168, // 7 days
            ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue($user->is_banned);
        $this->assertEquals('Temporary suspension', $user->ban_reason);
        $this->assertNotNull($user->ban_until);
    }

    /** @test */
    public function banned_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_banned' => true,
            'ban_reason' => 'Spam',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('reason', 'Spam');
    }

    /** @test */
    public function banned_user_expires_automatically()
    {
        $user = User::factory()->create([
            'is_banned' => true,
            'ban_reason' => 'Temporary',
            'ban_until' => now()->subDay(),
        ]);

        $isBanned = $user->isBanned();

        $this->assertFalse($isBanned);
        $user->refresh();
        $this->assertFalse($user->is_banned);
    }

    /** @test */
    public function admin_can_unban_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create([
            'is_banned' => true,
            'ban_reason' => 'Test ban',
            'ban_until' => now()->addDays(7),
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/users/{$user->id}/unban");

        $response->assertStatus(200);

        $user->refresh();
        $this->assertFalse($user->is_banned);
        $this->assertNull($user->ban_reason);
        $this->assertNull($user->ban_until);
    }

    /** @test */
    public function unbanned_user_with_stale_future_ban_until_can_still_create_auction()
    {
        // wczesniej TYMCZASOWY ban (real ban_until w przyszlosci) i tak
        // blokowal go az do naturalnego uplywu starej daty, mimo is_banned=false.
        $category = Category::factory()->create();
        $user = User::factory()->create([
            'is_active' => true,
            'is_banned' => false,
            'ban_until' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/auctions', [
            'title' => 'Test aukcja po odbanowaniu',
            'breed' => 'Janssen',
            'gender' => 'samiec',
            'year' => 2024,
            'size' => 'sredni',
            'color' => 'Niebieska',
            'start_price' => 100,
            'pigeon_images' => ['images/test.jpg'],
            'category_id' => $category->id,
        ]);

        $response->assertStatus(201);
    }
}
