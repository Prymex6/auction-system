<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'is_active' => true,
            'password' => Hash::make('stare-haslo123'),
        ]);
    }

    public function test_user_can_view_own_profile()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/profile');

        $response->assertStatus(200);
        $this->assertEquals($this->user->id, $response->json('data.id'));
    }

    public function test_guest_cannot_view_profile()
    {
        $this->getJson('/api/profile')->assertStatus(401);
    }

    public function test_user_can_update_profile_fields()
    {
        $response = $this->actingAs($this->user, 'sanctum')->patchJson('/api/profile', [
            'name' => 'nowy_login123',
            'bio' => 'Nowe bio',
            'city' => 'Kraków',
        ]);

        $response->assertStatus(200);
        $this->user->refresh();
        $this->assertEquals('nowy_login123', $this->user->name);
        $this->assertEquals('Nowe bio', $this->user->bio);
        $this->assertEquals('Kraków', $this->user->city);
    }

    public function test_user_can_upload_avatar_and_it_actually_persists()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user, 'sanctum')->patch('/api/profile', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertStatus(200);
        $this->user->refresh();
        $this->assertNotNull($this->user->avatar);
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'avatar' => $this->user->avatar,
        ]);
        Storage::disk('public')->assertExists($this->user->avatar);
    }

    public function test_user_cannot_update_name_to_duplicate_of_another_user()
    {
        $other = User::factory()->create(['name' => 'zajetalogin']);

        $response = $this->actingAs($this->user, 'sanctum')->patchJson('/api/profile', [
            'name' => 'zajetalogin',
        ]);

        $response->assertStatus(422);
        $this->assertNotEquals('zajetalogin', $this->user->fresh()->name);
    }

    public function test_user_cannot_update_name_with_invalid_characters()
    {
        $response = $this->actingAs($this->user, 'sanctum')->patchJson('/api/profile', [
            'name' => 'nazwa ze spacja i żółć',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_keep_their_own_current_name_unchanged()
    {
        $this->user->update(['name' => 'stala_nazwa_login']);

        $response = $this->actingAs($this->user, 'sanctum')->patchJson('/api/profile', [
            'name' => 'stala_nazwa_login',
            'bio' => 'Aktualizacja bez zmiany nazwy',
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_toggle_own_profile_privacy()
    {
        $response = $this->actingAs($this->user, 'sanctum')->patchJson('/api/profile', [
            'is_public' => false,
        ]);

        $response->assertStatus(200);
        $this->assertFalse((bool) $this->user->fresh()->is_public);

        Auction::factory()->create(['user_id' => $this->user->id, 'status' => 'active']);
        $stranger = User::factory()->create();
        $this->actingAs($stranger, 'sanctum')
            ->getJson("/api/users/{$this->user->id}")
            ->assertStatus(403);
    }

    public function test_user_can_change_password_with_correct_current_password()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/profile/change-password', [
            'current_password' => 'stare-haslo123',
            'password' => 'nowe-haslo123',
            'password_confirmation' => 'nowe-haslo123',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('nowe-haslo123', $this->user->fresh()->password));
    }

    public function test_change_password_rejects_password_without_numbers()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/profile/change-password', [
            'current_password' => 'stare-haslo123',
            'password' => 'tylkolitery',
            'password_confirmation' => 'tylkolitery',
        ]);

        $response->assertStatus(422);
        $this->assertTrue(Hash::check('stare-haslo123', $this->user->fresh()->password));
    }

    public function test_change_password_fails_with_wrong_current_password()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/profile/change-password', [
            'current_password' => 'zle-haslo',
            'password' => 'nowe-haslo123',
            'password_confirmation' => 'nowe-haslo123',
        ]);

        $response->assertStatus(422);
        $this->assertTrue(Hash::check('stare-haslo123', $this->user->fresh()->password));
    }

    public function test_updating_notification_preferences_does_not_crash_when_none_exist()
    {
        $this->assertNull($this->user->notificationPreferences);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/profile/notification-preferences', [
                'email_notifications' => false,
            ]);

        $response->assertStatus(200);
    }

    public function test_updating_notification_preferences_works_when_they_exist()
    {
        NotificationPreference::create([
            'user_id' => $this->user->id,
            'email_notifications' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/profile/notification-preferences', []);

        $response->assertStatus(200);
    }

    public function test_notification_preferences_actually_persist_with_real_field_names()
    {
        // Regresja: ProfileController::updateNotificationPreferences() walidowal
        // pola (email_on_bid, push_on_outbid, digest_frequency...) ktorych model
        $this->assertNull($this->user->notificationPreferences);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/profile/notification-preferences', [
                'email_notifications' => false,
                'push_notifications' => false,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'email_notifications' => false,
            'push_notifications' => false,
        ]);

        $getResponse = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/profile/notification-preferences');

        $getResponse->assertStatus(200)
            ->assertJsonPath('data.email_notifications', false)
            ->assertJsonPath('data.push_notifications', false);
    }
}
