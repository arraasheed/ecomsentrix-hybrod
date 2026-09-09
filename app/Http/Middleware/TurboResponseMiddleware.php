<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TurboResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if this is a Turbo request
        if ($this->isTurboRequest($request)) {
            // If response is a redirect (301-309) and request method is not safe (GET, HEAD, OPTIONS)
            if ($response->isRedirect() && !in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
                // Convert to 303 See Other for Turbo compatibility
                // This prevents re-submission of form data and ensures Turbo follows the redirect correctly
                $response->setStatusCode(303);
            }
        }

        return $response;
    }

    private function isTurboRequest(Request $request): bool
    {
        // Check for Turbo-specific headers
        return $request->hasHeader('Accept') && 
               (str_contains($request->header('Accept'), 'text/vnd.turbo-stream.html') ||
                str_contains($request->header('Accept'), 'text/html')) ||
               $request->hasHeader('Turbo-Frame') ||
               $request->hasHeader('X-Requested-With');
    }
}
