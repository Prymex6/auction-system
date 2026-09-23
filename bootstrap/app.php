<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\DynamicLoginThrottle;
use App\Http\Middleware\EnsureTwoFactorIfRequired;
use App\Http\Middleware\EnsureUserExists;
use App\Http\Middleware\IsAdmin;
use App\Services\ErrorTrackingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        attributes: ['middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API middleware
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->api(append: [
            CheckMaintenanceMode::class,
        ]);

        // Register alias for admin middleware
        $middleware->alias([
            'is-admin' => IsAdmin::class,
            'user-active' => CheckUserActive::class,
            'ensure-user-exists' => EnsureUserExists::class,
            'dynamic-login-throttle' => DynamicLoginThrottle::class,
            'require-2fa' => EnsureTwoFactorIfRequired::class,
        ]);

        $middleware->trustProxies(at: [
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
            '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
            '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
            '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
            '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
        ]);

        // Use custom CSRF verification that excludes auth routes
        $middleware->validateCsrfTokens(except: [
            'api/auth/*',
        ]);

        // tu null wylaczamy ta probe przekierowania u zrodla - AuthenticationException
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Nie znaleziono zasobu'], 404);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json(['message' => 'Musisz być zalogowany'], 401);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            $passthrough = $e instanceof ValidationException
                || $e instanceof HttpExceptionInterface
                || $e instanceof AuthenticationException
                || $e instanceof AuthorizationException;

            if (($request->is('api/*') || $request->expectsJson()) && ! config('app.debug') && ! $passthrough) {
                return response()->json(['message' => 'Wystąpił błąd serwera. Spróbuj ponownie za chwilę.'], 500);
            }
        });

        $exceptions->reportable(function (Throwable $e) {
            if (app()->environment('production')) {
                app(ErrorTrackingService::class)->track($e);
            }
        });
    })->create();
