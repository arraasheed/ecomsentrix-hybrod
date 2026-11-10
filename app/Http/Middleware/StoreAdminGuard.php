<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StoreAdminGuard
{
    /**
     * Handle an incoming request.
     * Enforce store-level admin access for tenant management routes.
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

        $user = auth()->user();

        // Check if impersonating
        $isImpersonating = false;
        try {
            if (app()->bound('impersonate')) {
                $isImpersonating = app('impersonate')->isImpersonating();
            }
        } catch (\Throwable $e) {
            $isImpersonating = false;
        }

        // If user is super admin and not currently impersonating a store admin
        if ($user->type === 'super admin' && !$isImpersonating) {
            return redirect()->route('dashboard')->with('error', __('This section is for Store Admins. Please select or log in to a specific store from the Users panel.'));
        }

        return $next($request);
    }
}
