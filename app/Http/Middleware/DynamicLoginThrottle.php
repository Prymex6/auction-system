<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class DynamicLoginThrottle
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = PlatformSetting::first();
        $maxAttempts = $settings?->max_login_attempts ?? 5;
        $lockoutMinutes = $settings?->lockout_duration_minutes ?? 15;

        $key = 'login-throttle:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Zbyt wiele prób logowania. Spróbuj ponownie za {$seconds} sekund.",
            ], 429);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 401) {
            RateLimiter::hit($key, $lockoutMinutes * 60);
        } else {
            RateLimiter::clear($key);
        }

        return $response;
    }
}
