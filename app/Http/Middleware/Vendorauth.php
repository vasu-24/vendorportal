<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VendorAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if logged in as vendor
        if (!Auth::guard('vendor')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('vendor.login');
        }

        $vendor = Auth::guard('vendor')->user();

        // Check if vendor is approved
        if (!$vendor->isApproved()) {
            Auth::guard('vendor')->logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account not approved'], 403);
            }
            return redirect()->route('vendor.login')->with('error', 'Your account is not approved yet.');
        }

        return $next($request);
    }
}