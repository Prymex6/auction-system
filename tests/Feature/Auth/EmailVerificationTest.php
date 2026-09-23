<?php

namespace Tests\Feature\Auth;

use App\Mail\VerifyEmailMail;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_verification_email()
    {
        Mail::fake();

        $this->postJson('/api/auth/register', [
            'name' => 'nowy_user',
            'first_name' => 'Nowy',
            'last_name' => 'User',
            'email' => 'nowy@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ])->assertStatus(201);

        $user = User::where('email', 'nowy@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Mail::assertQueued(VerifyEmailMail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }

    public function test_valid_signed_link_marks_email_as_verified()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($url);

        $response->assertRedirect();
        $this->assertStringContainsString('verified=1', $response->headers->get('Location'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_link_with_wrong_hash_does_not_verify()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('inny@adres.pl')]
        );

        $response = $this->get($url);

        $response->assertRedirect();
        $this->assertStringContainsString('verified=invalid', $response->headers->get('Location'));
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_link_does_not_verify()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(5),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($url);

        $response->assertRedirect();
        $this->assertStringContainsString('verified=expired', $response->headers->get('Location'));
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_visiting_link_twice_does_not_error()
    {
        $user = User::factory()->create(['email_verified_at' => now()->subDay()]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->get($url)->assertRedirect();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_admin_cannot_activate_user_with_unverified_email()
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $user = User::factory()->create(['is_active' => false, 'email_verified_at' => null]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/activate");

        $response->assertStatus(422);
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_admin_can_activate_user_with_verified_email()
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $user = User::factory()->create(['is_active' => false, 'email_verified_at' => now()]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/activate");

        $response->assertStatus(200);
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_admin_can_activate_user_with_unverified_email_when_verification_not_required()
    {
        $settings = PlatformSetting::first() ?? PlatformSetting::create([]);
        $settings->update(['require_email_verification' => false]);

        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $user = User::factory()->create(['is_active' => false, 'email_verified_at' => null]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/activate");

        $response->assertStatus(200);
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_user_can_resend_verification_email()
    {
        Mail::fake();
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/email/verification-notification');

        $response->assertStatus(200);
        Mail::assertQueued(VerifyEmailMail::class);
    }

    public function test_resend_is_noop_when_already_verified()
    {
        Mail::fake();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/email/verification-notification');

        $response->assertStatus(200);
        Mail::assertNotQueued(VerifyEmailMail::class);
    }
}
