<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CrmPermissionResolver;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

/** No DB fixtures: simulate an authenticated active user and permission grants. */
final class CrmReportAuthorizationHttpTest extends TestCase
{
    private function signedInWithGrants(array $permissions): void
    {
        $user = new User();
        $user->setAttribute('user_id', (string) Str::uuid());
        $user->setAttribute('status', 'active');
        $this->actingAs($user, 'web');

        $resolver = Mockery::mock(CrmPermissionResolver::class);
        $resolver->shouldReceive('allows')->andReturnUsing(
            static fn (User $viewer, string $permission): bool => in_array($permission, $permissions, true)
        );
        app()->instance(CrmPermissionResolver::class, $resolver);
    }

    public function test_logged_in_user_without_report_permission_cannot_read_page_or_export_csv(): void
    {
        $this->signedInWithGrants([]);
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('reports.export.invoices'))->assertForbidden();
    }

    public function test_base_reporting_permission_alone_does_not_allow_csv_or_page(): void
    {
        $this->signedInWithGrants(['reporting.access']);
        $this->get(route('reports.index', ['date_from' => 'invalid-date']))->assertForbidden();
        $this->get(route('reports.export.invoices'))->assertForbidden();
    }

    public function test_reports_view_permission_without_base_permission_is_denied(): void
    {
        $this->signedInWithGrants(['crm_page.reports.view']);
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('reports.export.invoices'))->assertForbidden();
    }
}
