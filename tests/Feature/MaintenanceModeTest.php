<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_request_blocked_when_maintenance_mode_enabled()
    {
        PlatformSetting::updateOrCreate([], [
            'maintenance_mode' => true,
            'maintenance_message' => 'Wracamy za chwile',
        ]);
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/auctions');

        $response->assertStatus(503)->assertJsonPath('message', 'Wracamy za chwile');
    }

    public function test_guest_also_blocked_when_maintenance_mode_enabled()
    {
        PlatformSetting::updateOrCreate([], ['maintenance_mode' => true]);

        $this->getJson('/api/auctions')->assertStatus(503);
    }

    /**
     * (nie actingAs($admin, 'sanctum')). actingAs() z jawnym guardem
     * wykryte dopiero przy prawdziwym requescie HTTP (curl), nie w PHPUnit.
     */
    public function test_admin_not_blocked_by_maintenance_mode()
    {
        PlatformSetting::updateOrCreate([], ['maintenance_mode' => true]);
        $admin = User::factory()->create(['is_active' => true, 'is_admin' => true]);
        $token = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/auctions')->assertStatus(200);
    }

    public function test_settings_endpoint_stays_reachable_during_maintenance()
    {
        PlatformSetting::updateOrCreate([], ['maintenance_mode' => true]);

        $this->getJson('/api/settings')->assertStatus(200);
    }

    public function test_login_stays_reachable_during_maintenance()
    {
        PlatformSetting::updateOrCreate([], ['maintenance_mode' => true]);
        User::factory()->create(['name' => 'maintlogintest', 'email' => 'maint@example.com', 'password' => bcrypt('Test1234'), 'is_active' => true]);

        $response = $this->postJson('/api/auth/login', ['login' => 'maintlogintest', 'password' => 'Test1234']);

        $response->assertStatus(200);
    }

    public function test_normal_requests_work_when_maintenance_mode_disabled()
    {
        PlatformSetting::updateOrCreate([], ['maintenance_mode' => false]);

        $this->getJson('/api/auctions')->assertStatus(200);
    }
}
