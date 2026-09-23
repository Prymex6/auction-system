<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2ECommandsBlockedInProductionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->detectEnvironment(fn () => 'production');
    }

    protected function tearDown(): void
    {
        app()->detectEnvironment(fn () => 'testing');
        parent::tearDown();
    }

    public function test_e2e_seed_refuses_to_run_in_production()
    {
        $this->artisan('e2e:seed')
            ->expectsOutputToContain('zablokowana na produkcji')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['email' => 'e2e_admin@example.com']);
    }

    public function test_e2e_reset_settings_refuses_to_run_in_production()
    {
        PlatformSetting::create(['require_2fa' => true]);

        $this->artisan('e2e:reset-settings')
            ->expectsOutputToContain('zablokowana na produkcji')
            ->assertExitCode(0);

        $this->assertTrue((bool) PlatformSetting::first()->require_2fa);
    }

    public function test_e2e_totp_code_refuses_to_run_in_production()
    {
        $this->artisan('e2e:totp-code', ['secret' => 'ABCDEFGHIJKLMNOP'])
            ->expectsOutputToContain('zablokowana na produkcji')
            ->assertExitCode(0);
    }

    public function test_e2e_verify_link_refuses_to_run_in_production()
    {
        $user = User::factory()->create(['email' => 'realny@example.com']);

        $this->artisan('e2e:verify-link', ['email' => $user->email])
            ->expectsOutputToContain('zablokowana na produkcji')
            ->assertExitCode(1);
    }
}
