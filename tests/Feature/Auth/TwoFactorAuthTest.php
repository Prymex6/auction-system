<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Auth\TwoFactorAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    private TwoFactorAuthService $twoFactorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->twoFactorService = app(TwoFactorAuthService::class);
    }

    /** @test */
    public function user_can_setup_2fa()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/auth/2fa/setup');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'secret',
                'backup_codes',
                'qr_code',
            ]);
    }

    /** @test */
    public function user_can_confirm_2fa_with_valid_token()
    {
        $user = User::factory()->create();

        // Setup 2FA
        $setup = $this->twoFactorService->setupTwoFactor($user);

        // Get valid OTP token
        $google2fa = app('pragmarx.google2fa');
        $validToken = $google2fa->getCurrentOtp($setup['secret']);

        $response = $this->actingAs($user)
            ->postJson('/api/auth/2fa/confirm', [
                'code' => $validToken,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($user->fresh()->two_factor_enabled);
    }

    /** @test */
    public function user_cannot_confirm_2fa_with_invalid_token()
    {
        $user = User::factory()->create();
        $this->twoFactorService->setupTwoFactor($user);

        $response = $this->actingAs($user)
            ->postJson('/api/auth/2fa/confirm', [
                'code' => '000000',
            ]);

        $response->assertStatus(422);
        $this->assertFalse($user->fresh()->two_factor_enabled);
    }

    /** @test */
    public function login_requires_2fa_verification()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Enable 2FA
        $this->twoFactorService->setupTwoFactor($user);
        $user->update(['two_factor_enabled' => true]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'requires_2fa' => true,
            ]);
    }

    /** @test */
    public function user_can_verify_2fa_with_backup_code()
    {
        $user = User::factory()->create();
        $setup = $this->twoFactorService->setupTwoFactor($user);
        $user->update(['two_factor_enabled' => true]);

        $backupCode = $setup['backup_codes'][0];

        $response = $this->postJson('/api/auth/verify-2fa', [
            'user_id' => $user->id,
            'code' => $backupCode,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    /** @test */
    public function user_can_disable_2fa()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->twoFactorService->setupTwoFactor($user);
        $user->update(['two_factor_enabled' => true]);

        $response = $this->actingAs($user)
            ->postJson('/api/auth/2fa/disable', [
                'password' => 'password123',
            ]);

        $response->assertStatus(200);
        $this->assertFalse($user->fresh()->two_factor_enabled);
    }
}
