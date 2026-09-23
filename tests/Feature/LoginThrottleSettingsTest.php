<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginThrottleSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login-throttle:127.0.0.1');
    }

    public function test_default_throttle_matches_previous_hardcoded_5_per_minute()
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/auth/login', ['login' => 'nieistnieje', 'password' => 'x'])
                ->assertStatus(401);
        }

        $this->postJson('/api/auth/login', ['login' => 'nieistnieje', 'password' => 'x'])
            ->assertStatus(429);
    }

    public function test_custom_max_login_attempts_setting_is_respected()
    {
        PlatformSetting::updateOrCreate([], ['max_login_attempts' => 2, 'lockout_duration_minutes' => 1]);

        $this->postJson('/api/auth/login', ['login' => 'nieistnieje', 'password' => 'x'])->assertStatus(401);
        $this->postJson('/api/auth/login', ['login' => 'nieistnieje', 'password' => 'x'])->assertStatus(401);
        $this->postJson('/api/auth/login', ['login' => 'nieistnieje', 'password' => 'x'])->assertStatus(429);
    }

    public function test_successful_login_still_works_within_the_limit()
    {
        PlatformSetting::updateOrCreate([], ['max_login_attempts' => 3]);
        $user = User::factory()->create(['name' => 'throttletestuser', 'password' => bcrypt('Test1234'), 'is_active' => true]);

        $this->postJson('/api/auth/login', ['login' => 'throttletestuser', 'password' => 'Test1234'])
            ->assertStatus(200);
    }

    public function test_successful_logins_do_not_count_towards_the_limit_and_clear_prior_failures()
    {
        PlatformSetting::updateOrCreate([], ['max_login_attempts' => 2]);
        $user = User::factory()->create(['name' => 'throttlereset', 'password' => bcrypt('Test1234'), 'is_active' => true]);

        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/auth/login', ['login' => 'throttlereset', 'password' => 'Test1234'])
                ->assertStatus(200);
        }

        $this->postJson('/api/auth/login', ['login' => 'throttlereset', 'password' => 'zlehaslo'])
            ->assertStatus(401);
    }

    public function test_successful_login_clears_previously_accumulated_failed_attempts()
    {
        PlatformSetting::updateOrCreate([], ['max_login_attempts' => 2]);
        $user = User::factory()->create(['name' => 'throttlereset2', 'password' => bcrypt('Test1234'), 'is_active' => true]);

        $this->postJson('/api/auth/login', ['login' => 'throttlereset2', 'password' => 'zle'])->assertStatus(401);
        $this->postJson('/api/auth/login', ['login' => 'throttlereset2', 'password' => 'Test1234'])->assertStatus(200);

        // wyczerpac od zera - dwie kolejne nieudane proby jeszcze przechodza,
        $this->postJson('/api/auth/login', ['login' => 'throttlereset2', 'password' => 'zle'])->assertStatus(401);
        $this->postJson('/api/auth/login', ['login' => 'throttlereset2', 'password' => 'zle'])->assertStatus(401);
        $this->postJson('/api/auth/login', ['login' => 'throttlereset2', 'password' => 'zle'])->assertStatus(429);
    }
}
