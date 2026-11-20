<?php

namespace Tests\Feature;

use App\Http\Middleware\EnforceCrmRouteAuthorization;
use App\Models\User;
use App\Services\CrmPermissionResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * A7.7.3 real HTTP denial and middleware passage, without any CRM business data.
 * No migrations, seeders, PostgreSQL connections, or business-data writes.
 */
final class CrmRouteAuthorizationHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::assertSame('testing', app()->environment());
        self::assertSame('sqlite', config('database.default'));
        self::assertSame(':memory:', (string) config('database.connections.sqlite.database'));
    }

    private function viewer(array $grants = [], bool $superAdmin = false): void
    {
        $user = new User();
        $user->setAttribute('user_id', (string) Str::uuid());
        $user->setAttribute('status', 'active');
        $this->actingAs($user, 'web');

        $resolver = Mockery::mock(CrmPermissionResolver::class);
        $resolver->shouldReceive('allows')->andReturnUsing(
            static fn (User $actor, string $permission): bool => in_array($permission, $grants, true)
        );
        $resolver->shouldReceive('isSuperAdmin')->andReturn($superAdmin);
        app()->instance(CrmPermissionResolver::class, $resolver);
    }

    public function test_direct_product_url_requires_page_view_not_just_module(): void
    {
        $this->viewer(['catalog.access']);
        $this->get(route('products.index'))->assertForbidden();
    }

    public function test_direct_post_cannot_reach_controller_with_view_only_grant(): void
    {
        $this->viewer(['catalog.manage', 'crm_page.products.view']);
        $this->post(route('products.store'), [])->assertForbidden();
    }

    public function test_reports_page_and_csv_require_both_grants(): void
    {
        $this->viewer(['reporting.access']);
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('reports.export.invoices'))->assertForbidden();
    }

    public function test_roles_and_users_are_not_open_to_regular_logged_in_users(): void
    {
        $this->viewer([]);
        $this->get(route('roles.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
        $this->post(route('roles.store'), [])->assertForbidden();
    }

    public function test_workflow_does_not_pass_without_specific_workflow_permission(): void
    {
        $this->viewer(['delivery.access', 'crm_page.deliveries.update']);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('');
        $this->probe('deliveries.change-status');
    }

    public function test_workflow_passes_with_all_three_grants_without_business_sql(): void
    {
        $this->viewer(['delivery.access', 'crm_page.deliveries.update', 'crm_workflow.deliveries.status']);
        self::assertSame(204, $this->probe('deliveries.change-status')->getStatusCode());
    }

    public function test_admin_route_passes_only_for_protected_super_admin(): void
    {
        $this->viewer([], true);
        self::assertSame(204, $this->probe('roles.index')->getStatusCode());
    }

    public function test_unmapped_workspace_route_is_not_reclassified_by_this_guard(): void
    {
        $this->viewer([]);
        self::assertSame(204, $this->probe('notes.index')->getStatusCode());
    }

    private function probe(string $name): Response
    {
        $request = Request::create('/a773-isolated-probe', 'GET');
        $route = new Route(['GET'], '/a773-isolated-probe', static fn (): Response => new Response('', 204));
        $route->name($name);
        $request->setRouteResolver(static fn (): Route => $route);
        $request->setUserResolver(static fn (?string $guard = null) => auth($guard ?: 'web')->user());
        return (new EnforceCrmRouteAuthorization())->handle(
            $request,
            static fn (Request $request): Response => new Response('', 204)
        );
    }
}
