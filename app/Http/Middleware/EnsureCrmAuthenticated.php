<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deny access to CRM private workspaces until a real authenticated session exists.
 * Does not redirect to a login route that may not yet be registered.
 */
class EnsureCrmAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(auth()->check(), 403, 'Sign in before accessing this CRM workspace.');

        return $next($request);
    }
}
