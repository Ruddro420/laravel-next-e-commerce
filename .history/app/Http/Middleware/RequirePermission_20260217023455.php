<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $perm)
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Admin bypass - check if user has admin role
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Check if user has the required permission
        if (!$user->canPerm($perm)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. You do not have the required permission.',
                    'permission_required' => $perm
                ], 403);
            }
            
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}