<?php

namespace App\Services;

/**
 * A7.7.3 - pure route name to permission requirements. No DB access.
 * Unmapped routes keep their existing independent guards; see route inventory.
 */
final class CrmRouteAuthorizationPolicy
{
    /** @return array{managed:bool,super_admin:bool,permissions:list<string>} */
    public static function forRoute(?string $routeName): array
    {
        $unmanaged = ['managed' => false, 'super_admin' => false, 'permissions' => []];
        if (!is_string($routeName) || $routeName === '') {
            return $unmanaged;
        }

        // Roles and Users must not become writable by any authenticated account.
        // They are outside the A6 page catalogue, so authorize explicitly.
        if (preg_match('/^(roles|users)\./D', $routeName) === 1) {
            return ['managed' => true, 'super_admin' => true, 'permissions' => []];
        }

        $module = CrmModuleAccess::permissionForRoute($routeName);
        $page = CrmRoleAccessCatalog::forRoute($routeName);
        $workflow = CrmWorkflowAccess::forRoute($routeName);
        if ($module === null && $page === null && $workflow === null) {
            return $unmanaged;
        }

        // A known module may never be authorized with only its base permission.
        // A workflow also needs a catalogued page action.
        if ($module === null || $page === null) {
            return ['managed' => true, 'super_admin' => false, 'permissions' => []];
        }

        $permissions = [$module, $page];
        if ($workflow !== null) {
            $permissions[] = $workflow;
        }
        return ['managed' => true, 'super_admin' => false, 'permissions' => array_values(array_unique($permissions))];
    }
}
