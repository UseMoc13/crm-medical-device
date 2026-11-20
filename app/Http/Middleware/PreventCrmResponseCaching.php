<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A7.7.1 — prevent shared caches and browsers from retaining private CRM
 * HTML, JSON and streamed CSV responses handled by the web middleware group.
 * This does not change authentication, authorization, business data or routes.
 */
final class PreventCrmResponseCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Override any earlier cacheable policy for the CRM web group.
        $response->headers->set('Cache-Control', 'private, no-store, no-cache, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
