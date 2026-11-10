<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AdminRoleSeparationMiddleware
{
    /**
     * Handle an incoming request.
     * Enforce strict role boundaries between Super Admin and Store Admin.
     * Kill-switch: ADMIN_ROLE_SEPARATION_ENABLED=false in .env.
     */
    public function handle(Request $request, Closure $next)
    {
        if (env('ADMIN_ROLE_SEPARATION_ENABLED', true) === false) {
            return $next($request);
        }

        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Check if impersonating (Super Admin viewing a store as admin)
        $isImpersonating = false;
        try {
            if (app()->bound('impersonate')) {
                $isImpersonating = app('impersonate')->isImpersonating();
            }
        } catch (\Throwable $e) {
            $isImpersonating = false;
        }

        $currentRouteName = Route::currentRouteName();

        // 1. If Super Admin (not impersonating) tries to access Store Admin specific routes
        if ($user->type === 'super admin' && !$isImpersonating) {
            $storeOnlyRoutes = [
                'product.',
                'products.',
                'category.',
                'product-brand.',
                'product-label.',
                'product-attributes.',
                'attribute-option.',
                'order.',
                'orders.',
                'refund-request.',
                'deliveryboy.',
                'theme-customize.',
                'app-setting.',
                'shipping.',
                'shipping-zone.',
                'shipping-method.',
                'pos.',
                'taxes.',
                'taxes-method.',
                'flash-sale.',
                'customer.',
                'support_ticket.',
                'menus.',
            ];

            if ($currentRouteName) {
                foreach ($storeOnlyRoutes as $prefix) {
                    if (str_starts_with($currentRouteName, $prefix)) {
                        return redirect()->route('dashboard')->with('error', __('This section is for Store Admins. Please select "Login As Admin" from the Users menu to manage a specific store.'));
                    }
                }
            }
        }

        // 2. If Store Admin (type != 'super admin') tries to access Super Admin specific routes
        if ($user->type !== 'super admin') {
            $superAdminOnlyRoutes = [
                'stores.',
                'store.subdomain',
                'store.customdomain',
                'store.list',
                'store.user.create',
                'plan-coupon.',
                'plan-request.',
                'module.',
                'addon.',
                'countries.',
                'state.',
                'city.',
                'email_template',
                'manage.email.language',
            ];

            if ($currentRouteName) {
                foreach ($superAdminOnlyRoutes as $prefix) {
                    if (str_starts_with($currentRouteName, $prefix) || $currentRouteName === $prefix) {
                        return redirect()->route('dashboard')->with('error', __('Permission denied: Super Admin access required.'));
                    }
                }
            }
        }

        return $next($request);
    }
}
