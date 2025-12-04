<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission = null): Response
    {
        $user = auth()->user();

        // Check if logged in
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        // Check if active
        if (!$user->isActive()) {
            auth()->logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account is inactive'], 403);
            }
            return redirect()->route('login')->with('error', 'Your account is inactive.');
        }

        // Super Admin bypasses all permission checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check specific permission if provided
        if ($permission && !$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Permission denied'], 403);
            }
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}