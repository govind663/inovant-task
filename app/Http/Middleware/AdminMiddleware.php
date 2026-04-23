<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // Sanctum user

        // Not authenticated
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        /**
         * Clean Admin Check (Single Source of Truth)
         */
        if (!$user->isAdmin()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized (Admin only)'
            ], 403);
        }

        return $next($request);
    }
}