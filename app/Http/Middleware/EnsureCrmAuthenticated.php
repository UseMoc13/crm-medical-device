<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCrmAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        // The login page must remain reachable even when this middleware is
        // appended globally to Laravel's "web" middleware group.
        if ($request->is('login')) {
            return $next($request);
        }

        if (auth('web')->check()) {
            $user = auth('web')->user();
            if (strtolower(trim((string) ($user->status ?? ''))) === 'active') {
                return $next($request);
            }

            auth('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Avoid HTML redirects for JSON/API consumers.
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Use the named login route. This needs routes/crm_auth.php registered.
        // Redirect::guest stores the intended URL for safe GET requests.
        if ($request->isMethod('GET')) {
            return redirect()->guest(route('login'))
                ->with('warning', 'Your session has expired. Please sign in again.');
        }

        // Do not resume an unsafe POST/PATCH/DELETE after reauthentication.
        return redirect()->route('login')
            ->with('warning', 'Your session has expired. Please sign in and retry the action.');
    }
}
