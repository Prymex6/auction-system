<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIfRequired
{
    public function handle(Request $request, Closure $next): Response
    {
        $requireTwoFactor = PlatformSetting::first()?->require_2fa ?? false;

        if ($requireTwoFactor && $request->user() && ! $request->user()->two_factor_enabled) {
            return response()->json([
                'message' => 'Administrator wymaga włączenia weryfikacji dwuetapowej (2FA), aby licytować lub wystawiać aukcje. Włącz 2FA w swoim profilu.',
                'code' => 'TWO_FACTOR_REQUIRED',
            ], 403);
        }

        return $next($request);
    }
}
