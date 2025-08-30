<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force JSON response middleware.
 *
 * Forces all requests to accept JSON responses for API-only microservice.
 */
class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force Accept header to application/json
        $request->headers->set('Accept', 'application/json');

        // Ensure Content-Type is application/json for POST/PUT/PATCH requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            if (! $request->headers->has('Content-Type')) {
                $request->headers->set('Content-Type', 'application/json');
            }
        }

        return $next($request);
    }
}
