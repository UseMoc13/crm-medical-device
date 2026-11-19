<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CrmPermissionResolver;
use App\Services\CrmReportPrivacyPilot;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

/** No business queries: invalid date stops after the real authorization guard. */
final class CrmReportPrivacyPilotHttpTest extends TestCase
{
    private function viewer(array $grants, array $scopes = []): User
    {
        $user = new User();
        $user->setAttribute('user_id', (string) Str::uuid());
        $user->setAttribute('status', 'active');
        $this->actingAs($user, 'web');
        $resolver = Mockery::mock(CrmPermissionResolver::class);
        $resolver->shouldReceive('allows')->andReturnUsing(
            static fn (User $viewer, string $permission): bool => in_array($permission, $grants, true)
        );
        $resolver->shouldReceive('scopes')->andReturnUsing(
            static fn (User $viewer, string $permission): array => $permission === 'crm_page.invoices.view' ? $scopes : []
        );
        app()->instance(CrmPermissionResolver::class, $resolver);
        return $user;
    }

    public function test_detail_pilot_denies_direct_csv_for_reports_only_role_but_reports_page_remains_reachable(): void
    {
        config()->set('crm_reporting.report_customer_product_privacy_pilot_enabled', true);
        config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
        $viewer = $this->viewer(['reporting.access', 'crm_page.reports.view']);
        self::assertFalse(app(CrmReportPrivacyPilot::class)->forViewer($viewer)['customer_product_details']);
        $this->get(route('reports.export.invoices'))->assertForbidden();
        $this->get(route('reports.index', ['date_from'=>'not-a-date']))->assertSessionHasErrors('date_from');
    }

    public function test_detail_pilot_allows_invoice_all_and_reaches_validation_without_business_queries(): void
    {
        config()->set('crm_reporting.report_customer_product_privacy_pilot_enabled', true);
        config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
        $viewer = $this->viewer([
            'reporting.access', 'crm_page.reports.view', 'billing.access', 'crm_page.invoices.view',
        ], ['all']);
        self::assertTrue(app(CrmReportPrivacyPilot::class)->forViewer($viewer)['customer_product_details']);
        $this->get(route('reports.export.invoices', ['date_from'=>'not-a-date']))
            ->assertSessionHasErrors('date_from');
    }

    public function test_detail_flag_off_preserves_previous_csv_entrypoint(): void
    {
        config()->set('crm_reporting.report_customer_product_privacy_pilot_enabled', false);
        config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
        $viewer = $this->viewer(['reporting.access', 'crm_page.reports.view']);
        self::assertTrue(app(CrmReportPrivacyPilot::class)->forViewer($viewer)['customer_product_details']);
        $this->get(route('reports.export.invoices', ['date_from'=>'not-a-date']))
            ->assertSessionHasErrors('date_from');
    }
}
