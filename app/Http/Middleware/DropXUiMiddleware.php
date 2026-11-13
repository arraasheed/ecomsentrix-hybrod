<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DropXUiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if DropX UI is enabled globally
        if (env('DROXP_UI_ENABLED', true) && auth()->check()) {
            // Share layout view with views if requested or override default dashboard layout
            view()->share('ecomsentrix_layout', 'layouts.dropx');
        }

        return $next($request);
    }
}
