<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeviceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
    }

    public function test_user_can_list_own_devices()
    {
        UserDevice::create([
            'user_id' => $this->user->id,
            'device_token' => 'token-a',
            'device_name' => 'Chrome on Windows',
            'device_type' => 'web',
            'last_activity_at' => now(),
        ]);
        $otherUser = User::factory()->create(['is_active' => true]);
        UserDevice::create([
            'user_id' => $otherUser->id,
            'device_token' => 'token-b',
            'device_name' => 'Safari on Mac',
            'device_type' => 'web',
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/devices');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Chrome on Windows', $response->json('data.0.device_name'));
    }

    public function test_user_can_delete_own_device()
    {
        $device = UserDevice::create([
            'user_id' => $this->user->id,
            'device_token' => 'token-c',
            'device_name' => 'Firefox on Linux',
            'device_type' => 'web',
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/devices/{$device->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('user_devices', ['id' => $device->id]);
    }

    public function test_user_cannot_delete_another_users_device()
    {
        $otherUser = User::factory()->create(['is_active' => true]);
        $device = UserDevice::create([
            'user_id' => $otherUser->id,
            'device_token' => 'token-d',
            'device_name' => 'Edge on Windows',
            'device_type' => 'web',
            'last_activity_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/devices/{$device->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('user_devices', ['id' => $device->id]);
    }

    public function test_logout_others_keeps_current_device_and_removes_rest()
    {
        $newToken = $this->user->createToken('device-session');
        $token = $newToken->plainTextToken;

        $current = UserDevice::create([
            'user_id' => $this->user->id,
            'device_token' => (string) $newToken->accessToken->id,
            'device_name' => 'Current device',
            'device_type' => 'web',
            'last_activity_at' => now(),
        ]);
        $other = UserDevice::create([
            'user_id' => $this->user->id,
            'device_token' => 'some-other-token-id',
            'device_name' => 'Other device',
            'device_type' => 'web',
            'last_activity_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/devices/logout-others');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('count'));
        $this->assertDatabaseHas('user_devices', ['id' => $current->id]);
        $this->assertDatabaseMissing('user_devices', ['id' => $other->id]);
    }

    public function test_is_current_reflects_the_token_used_for_this_request_not_a_stale_stored_flag()
    {
        // uzywac (wciaz waznego tokenu) na komputerze, telefon dostaje
        $desktopToken = $this->user->createToken('desktop-session');
        $phoneToken = $this->user->createToken('phone-session');

        UserDevice::create([
            'user_id' => $this->user->id,
            'device_token' => (string) $desktopToken->accessToken->id,
            'device_name' => 'Chrome on Windows',
            'device_type' => 'web',
            'last_activity_at' => now()->subHours(10),
            'is_current' => false,
        ]);
        UserDevice::create([
            'user_id' => $this->user->id,
            'device_token' => (string) $phoneToken->accessToken->id,
            'device_name' => 'Chrome on AndroidOS',
            'device_type' => 'mobile',
            'last_activity_at' => now()->subHours(9),
            'is_current' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$desktopToken->plainTextToken}")
            ->getJson('/api/devices');

        $response->assertStatus(200);
        $devices = collect($response->json('data'))->keyBy('device_name');
        $this->assertTrue($devices['Chrome on Windows']['is_current']);
        $this->assertFalse($devices['Chrome on AndroidOS']['is_current']);
    }

    public function test_guest_cannot_access_devices()
    {
        $this->getJson('/api/devices')->assertStatus(401);
    }
}
