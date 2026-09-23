<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->is_active) {
            return response()->json([
                'message' => 'Konto nie zostało aktywowane przez administratora. Skontaktuj się z supportem.',
                'code' => 'USER_NOT_ACTIVE',
            ], 403);
        }

        if ($request->user() && $request->user()->isBanned()) {
            return response()->json([
                'message' => 'Użytkownik jest zbanowany do '.$request->user()->ban_until?->format('Y-m-d H:i:s'),
                'code' => 'USER_BANNED',
            ], 422);
        }

        return $next($request);
    }
}
