<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminGuard
{
    /**
     * Handle an incoming request.
     * Enforce super admin only access for platform-level routes.
     * Kill-switch: ADMIN_ROLE_SEPARATION_ENABLED=false in .env.
     */
    public function handle(Request $request, Closure $next)
    {
        if (env('ADMIN_ROLE_SEPARATION_ENABLED', true) === false) {
            return $next($request);
        }

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->type !== 'super admin') {
            return redirect()->route('dashboard')->with('error', __('Permission denied: Super Admin access required.'));
        }

        return $next($request);
    }
}
