<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $perm)
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        // admin bypass
        if ($user->hasRole('admin')) return $next($request);

        if (!$user->canPerm($perm)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
