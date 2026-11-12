<?php

namespace App\Http\Middleware;

use App\Services\CrmPermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Transitional A4 guard: authentication + protected administration routes. */
class EnsureCrmAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
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

        $permissions = app(CrmPermissionResolver::class);
        $user = $request->user();
        $name = (string) ($request->route()?->getName() ?? '');

        // Reserved role configuration remains exclusive to the protected
        // Super Admin, even when an ordinary role receives a similarly named
        // permission later. Does not grant access to other CRM modules.
        if (str_starts_with($name, 'roles.') && ! $permissions->isSuperAdmin($user)) {
            abort(403, 'Role management requires Super Admin.');
        }

        if (str_starts_with($name, 'users.')) {
            $isAdministrator = $permissions->isSuperAdmin($user)
                || $permissions->hasRole($user, 'Admin');
            if (! $isAdministrator) {
                abort(403, 'Administration access is required.');
            }
            // Keep ordinary Admin read-only until protected user-granting
            // policies and explicit permission matrix are completed.
            if (! in_array($name, ['users.index', 'users.show'], true)
                && ! $permissions->isSuperAdmin($user)) {
                abort(403, 'User management changes require Super Admin during A4.');
            }
        }

        if (str_starts_with($name, 'announcement-management.')
            && ! $permissions->isSuperAdmin($user)
            && ! $permissions->hasRole($user, 'Admin')) {
            abort(403, 'Administration access is required.');
        }

        // WARNING: operational modules remain authentication-only for now.
        // Data-scoped authorization is introduced after permission seeding.
        return $next($request);
    }
}
