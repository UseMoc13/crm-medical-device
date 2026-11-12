<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Transitional access guard for A3. A complete permission + data-scope
 * authorization layer is required before production deployment.
 */
class EnsureCrmAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        // Login has to remain accessible to guests. Logout verifies session
        // in its controller. Only these exact paths are public in web routes.
        if ($request->is('login')) {
            return $next($request);
        }

        if (! Auth::check()) {
            return redirect()->guest(route('login'));
        }

        if (strtolower((string) $request->user()->status) !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is inactive. Contact an administrator.',
            ]);
        }

        if ($request->is('logout')) {
            return $next($request);
        }

        // Temporary hardening while A4 permissions are not yet implemented.
        // Never grant Super Admin powers based on UI form input alone.
        $name = (string) ($request->route()?->getName() ?? '');
        if ($name !== '') {
            $isAdmin = $request->user()->roles()
                ->whereRaw('LOWER(roles.role_name) IN (?, ?)', ['admin', 'super admin'])
                ->exists();
            $isSuperAdmin = $request->user()->roles()
                ->whereRaw('LOWER(roles.role_name) = ?', ['super admin'])
                ->exists();

            if (str_starts_with($name, 'users.')) {
                if (! $isAdmin) {
                    abort(403, 'Administration access is required.');
                }
                if (! in_array($name, ['users.index', 'users.show'], true) && ! $isSuperAdmin) {
                    abort(403, 'User management changes are restricted until RBAC policies are ready.');
                }
            }
            if (str_starts_with($name, 'roles.') && ! $isSuperAdmin) {
                abort(403, 'Role management changes are restricted until RBAC policies are ready.');
            }
            if (str_starts_with($name, 'announcement-management.') && ! $isAdmin) {
                abort(403, 'Administration access is required.');
            }
        }

        return $next($request);
    }
}
