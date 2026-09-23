<?php

namespace Tests\Feature;

use App\Mail\ErrorAlertMail;
use App\Models\ErrorLog;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\ErrorTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ErrorTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_exception_creates_an_error_log_entry()
    {
        Mail::fake();
        PlatformSetting::create(['platform_email' => 'admin@example.com']);

        $exception = new \RuntimeException('Coś się zepsuło');
        app(ErrorTrackingService::class)->track($exception);

        $this->assertDatabaseCount('error_logs', 1);
        $log = ErrorLog::first();
        $this->assertSame('Coś się zepsuło', $log->message);
        $this->assertSame(\RuntimeException::class, $log->exception_class);
        $this->assertSame(1, $log->occurrences);
        $this->assertFalse($log->resolved);
    }

    public function test_repeated_same_exception_increments_occurrences_instead_of_duplicating()
    {
        Mail::fake();
        PlatformSetting::create(['platform_email' => 'admin@example.com']);

        $makeException = fn () => new \RuntimeException('Powtarzajacy sie blad');

        $service = app(ErrorTrackingService::class);
        $service->track($makeException());
        $service->track($makeException());
        $service->track($makeException());

        $this->assertDatabaseCount('error_logs', 1);
        $log = ErrorLog::first();
        $this->assertSame(3, $log->occurrences);
    }

    public function test_ignores_routine_exceptions_not_worth_alerting()
    {
        $service = app(ErrorTrackingService::class);

        $service->track(ValidationException::withMessages(['field' => ['błąd']]));
        $service->track(new NotFoundHttpException('nie znaleziono'));

        $this->assertDatabaseCount('error_logs', 0);
    }

    public function test_sends_admin_alert_only_on_first_occurrence()
    {
        Mail::fake();
        PlatformSetting::create(['platform_email' => 'admin@example.com']);

        $makeException = fn () => new \RuntimeException('Alert raz');
        $service = app(ErrorTrackingService::class);

        $service->track($makeException());
        $service->track($makeException());
        $service->track($makeException());

        Mail::assertQueuedCount(1);
        Mail::assertQueued(ErrorAlertMail::class);
    }

    public function test_admin_can_list_and_resolve_error_logs()
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $log = ErrorLog::create([
            'fingerprint' => hash('sha256', 'test'),
            'exception_class' => \RuntimeException::class,
            'message' => 'Test error',
            'occurrences' => 1,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/error-logs')
            ->assertStatus(200)
            ->assertJsonPath('data.data.0.id', $log->id);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/error-logs/{$log->id}/resolve")
            ->assertStatus(200);

        $this->assertTrue($log->fresh()->resolved);
    }

    public function test_non_admin_cannot_access_error_logs()
    {
        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/error-logs')
            ->assertStatus(403);
    }

    public function test_guest_cannot_access_error_logs()
    {
        $this->getJson('/api/admin/error-logs')->assertStatus(401);
    }
}
