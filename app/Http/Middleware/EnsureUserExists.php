<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserExists
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $user = User::find($request->user()->id);

            if (! $user) {
                $request->user()->tokens()->delete();

                return response()->json([
                    'message' => 'User not found. Please login again.',
                ], 401);
            }
        }

        return $next($request);
    }
}
