<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTravelAccess
{
    public function handle(Request $request, Closure $next)
    {
        $vendor = auth('vendor')->user();
        
        if (!$vendor || !$vendor->has_travel_access) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to Travel Module'
            ], 403);
        }
        
        return $next($request);
    }
}