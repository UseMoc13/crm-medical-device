<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\CrmPermissionResolver;
use App\Services\CrmRouteAuthorizationPolicy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A7.7.3 - enforce existing A6 module/page/workflow permissions at the HTTP edge.
 * Deny BEFORE controller code, validation or business queries run.
 * Unknown routes continue to use their existing guards, not this catalogue.
 */
final class EnforceCrmRouteAuthorization
{
    public function handle(Request $request, Closure $next): Response
    {
        $policy = CrmRouteAuthorizationPolicy::forRoute($request->route()?->getName());
        if (!$policy['managed']) {
            return $next($request);
        }

        $actor = $request->user('web');
        abort_unless($actor instanceof User && strtolower(trim((string) ($actor->status ?? ''))) === 'active', 403);

        $resolver = app(CrmPermissionResolver::class);
        if ($policy['super_admin']) {
            abort_unless($resolver->isSuperAdmin($actor), 403);
        } else {
            // An incomplete catalogue match must never become an allowed route.
            abort_unless($policy['permissions'] !== [], 403);
            foreach ($policy['permissions'] as $permission) {
                abort_unless($resolver->allows($actor, $permission), 403);
            }
        }

        return $next($request);
    }
}
