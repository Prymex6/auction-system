<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has admin role
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Brak dostępu. Wymagane uprawnienia administratora.',
            ], 403);
        }

        // Bind the resolved user to the request so controllers can use $request->user()
        return $next($request);
    }
}
