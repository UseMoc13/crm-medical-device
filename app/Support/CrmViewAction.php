<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use App\Services\CrmPermissionResolver;
use App\Services\CrmRouteAuthorizationPolicy;
use Illuminate\Support\Facades\Auth;

/**
 * A7.10.3 UI affordances. Mirrors the route middleware permission requirements.
 * This is NOT an authorization boundary; EnforceCrmRouteAuthorization and all
 * business/data-scoping guards must remain enabled for the actual HTTP request.
 */
final class CrmViewAction
{
    /** Whether the named route is governed by the existing CRM permission catalogue. */
    public static function guarded(string $routeName): bool
    {
        if ($routeName === '') {
            return false;
        }
        if (preg_match('/^(?:organization|teams)\./D', $routeName)) {
            return true; // These modules have independent Super Admin middleware.
        }
        return CrmRouteAuthorizationPolicy::forRoute($routeName)['managed'];
    }

    public static function allows(string $routeName): bool
    {
        if (!self::guarded($routeName)) {
            return false; // Never infer permission from an unregistered route.
        }
        $actor = Auth::guard('web')->user();
        if (!$actor instanceof User || mb_strtolower(trim((string) $actor->status)) !== 'active') {
            return false;
        }

        // Cache ONLY for this HTTP request; do not cache between users/requests.
        $request = request();
        $key = (string) $actor->getAuthIdentifier().'|'.$routeName;
        $cache = $request->attributes->get('a7103_role_actions', []);
        if (array_key_exists($key, $cache)) {
            return (bool) $cache[$key];
        }

        $resolver = app(CrmPermissionResolver::class);
        if (preg_match('/^(?:organization|teams)\./D', $routeName)) {
            $allowed = $resolver->isSuperAdmin($actor);
        } else {
            $policy = CrmRouteAuthorizationPolicy::forRoute($routeName);
            if ($policy['super_admin']) {
                $allowed = $resolver->isSuperAdmin($actor);
            } else {
                $allowed = $policy['permissions'] !== [];
                foreach ($policy['permissions'] as $permission) {
                    if (!$resolver->allows($actor, $permission)) {
                        $allowed = false;
                        break;
                    }
                }
            }
        }
        $cache[$key] = $allowed;
        $request->attributes->set('a7103_role_actions', $cache);
        return $allowed;
    }
}
