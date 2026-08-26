<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminToken
{
    /**
     * Ensure the Sanctum token belongs to an Admin, not a customer User.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Admin) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
