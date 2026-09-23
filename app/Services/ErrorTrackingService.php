<?php

namespace App\Services;

use App\Mail\ErrorAlertMail;
use App\Models\ErrorLog;
use App\Models\PlatformSetting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorTrackingService
{
    /**
     * Wyjatki bedace normalna czescia ruchu aplikacji (walidacja, 404, brak
     */
    protected function shouldIgnore(Throwable $e): bool
    {
        if ($e instanceof ValidationException
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException
            || $e instanceof HttpExceptionInterface) {
            return true;
        }

        return false;
    }

    public function track(Throwable $e): void
    {
        if ($this->shouldIgnore($e)) {
            return;
        }

        $fingerprint = hash('sha256', get_class($e).'|'.$e->getFile().'|'.$e->getLine());
        $request = request();

        $existing = ErrorLog::where('fingerprint', $fingerprint)->first();

        if ($existing) {
            $existing->increment('occurrences');
            $existing->update(['last_seen_at' => now()]);

            return;
        }

        $log = ErrorLog::create([
            'fingerprint' => $fingerprint,
            'exception_class' => get_class($e),
            'message' => mb_substr($e->getMessage(), 0, 2000),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => mb_substr($e->getTraceAsString(), 0, 20000),
            'url' => $request?->fullUrl(),
            'method' => $request?->method(),
            'user_id' => $request?->user()?->id,
            'ip_address' => $request?->ip(),
            'occurrences' => 1,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $this->notifyAdmin($log);
    }

    protected function notifyAdmin(ErrorLog $log): void
    {
        app(PushNotificationService::class)->sendToAdmins(
            'Błąd aplikacji',
            $log->exception_class.': '.mb_substr($log->message, 0, 100),
            '/admin'
        );

        $adminEmail = PlatformSetting::first()?->platform_email;

        if (! $adminEmail) {
            return;
        }

        try {
            Mail::to($adminEmail)->send(new ErrorAlertMail($adminEmail, $log));
        } catch (Throwable $mailError) {
            Log::error('ErrorTrackingService: nie udało się wysłać alertu mailowego', [
                'error' => $mailError->getMessage(),
            ]);
        }
    }
}
