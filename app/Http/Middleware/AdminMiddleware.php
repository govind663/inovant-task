<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ❌ Not logged in
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // ❌ Not admin
        if (!Auth::user()->is_admin) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized (Admin only)'
            ], 403);
        }

        return $next($request);
    }
}