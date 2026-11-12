<?php

namespace App\Services;

use App\Models\User;

/** A6.7: navigation visibility must match backend page View authorization. */
final class CrmNavigationAccess
{
    public function __construct(private readonly CrmPermissionResolver $resolver) {}

    public function canView(?User $user, string $page): bool
    {
        if (! $user || ! isset(CrmRoleAccessCatalog::PAGES[$page])) {
            return false;
        }

        $config = CrmRoleAccessCatalog::PAGES[$page];
        return $this->resolver->allows($user, $config['base'])
            && $this->resolver->allows($user, CrmRoleAccessCatalog::key($page, 'view'));
    }

    /** @param array<int, string> $pages */
    public function anyView(?User $user, array $pages): bool
    {
        foreach ($pages as $page) {
            if ($this->canView($user, $page)) {
                return true;
            }
        }
        return false;
    }
}
