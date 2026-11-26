<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\CrmPermissionResolver;
use App\Support\CrmViewAction;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

/** No CRM tables are queried; resolver mocked. No DB writes or migrations. */
final class CrmRoleAwareCrudVisibilityTest extends TestCase
{
    private function viewer(array $permissions, bool $admin = false, string $status = 'active'): void
    {
        $user = new User();
        $user->setAttribute('user_id', (string) Str::uuid());
        $user->setAttribute('status', $status);
        $this->actingAs($user, 'web');

        $resolver = Mockery::mock(CrmPermissionResolver::class);
        $resolver->shouldReceive('isSuperAdmin')->andReturn($admin);
        $resolver->shouldReceive('allows')->andReturnUsing(
            static fn (User $actor, string $permission): bool => in_array($permission, $permissions, true)
        );
        app()->instance(CrmPermissionResolver::class, $resolver);
    }

    public function test_view_only_user_sees_details_but_not_create_edit_delete(): void
    {
        $this->viewer(['catalog.access', 'crm_page.products.view']);
        $this->assertTrue(CrmViewAction::allows('products.index'));
        $this->assertTrue(CrmViewAction::allows('products.show'));
        $this->assertFalse(CrmViewAction::allows('products.create'));
        $this->assertFalse(CrmViewAction::allows('products.edit'));
        $this->assertFalse(CrmViewAction::allows('products.destroy'));
    }

    public function test_update_without_delete_hides_only_delete_action(): void
    {
        $this->viewer(['catalog.access', 'catalog.manage', 'crm_page.products.view', 'crm_page.products.update']);
        $this->assertTrue(CrmViewAction::allows('products.edit'));
        $this->assertFalse(CrmViewAction::allows('products.destroy'));
        $this->assertFalse(CrmViewAction::allows('products.create'));
    }

    public function test_status_workflow_requires_extra_permission(): void
    {
        $this->viewer(['delivery.access', 'crm_page.deliveries.view', 'crm_page.deliveries.update']);
        $this->assertTrue(CrmViewAction::allows('deliveries.edit'));
        $this->assertFalse(CrmViewAction::allows('deliveries.change-status'));
    }

    public function test_super_admin_only_links_do_not_expose_to_regular_user(): void
    {
        $this->viewer(['catalog.access', 'crm_page.products.view']);
        $this->assertFalse(CrmViewAction::allows('users.index'));
        $this->assertFalse(CrmViewAction::allows('roles.create'));
        $this->assertFalse(CrmViewAction::allows('teams.index'));
    }

    public function test_unauthenticated_and_inactive_are_denied(): void
    {
        $this->assertFalse(CrmViewAction::allows('products.index'));
        $this->viewer(['catalog.access', 'crm_page.products.view'], false, 'inactive');
        $this->assertFalse(CrmViewAction::allows('products.index'));
    }

    public function test_unknown_route_fails_closed(): void
    {
        $this->viewer([]);
        $this->assertFalse(CrmViewAction::allows('unknown.destroy'));
        $this->assertFalse(CrmViewAction::allows(''));
    }
}
