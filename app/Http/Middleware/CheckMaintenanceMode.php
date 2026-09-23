<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    private const EXEMPT_PREFIXES = [
        'api/settings',
        'api/auth/login',
        'api/auth/me',
        'api/admin',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $settings = PlatformSetting::first();

        if (! ($settings?->maintenance_mode ?? false)) {
            return $next($request);
        }

        if (auth('sanctum')->user()?->isAdmin()) {
            return $next($request);
        }

        foreach (self::EXEMPT_PREFIXES as $prefix) {
            if ($request->is($prefix) || $request->is($prefix.'/*')) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => $settings->maintenance_message ?: 'Serwis jest obecnie w trybie konserwacji. Zapraszamy wkrótce.',
            'code' => 'MAINTENANCE_MODE',
        ], 503);
    }
}
