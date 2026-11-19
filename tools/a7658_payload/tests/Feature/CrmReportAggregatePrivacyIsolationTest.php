<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\User;
use App\Services\CrmPermissionResolver;
use App\Services\CrmReportAggregatePrivacyPilot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

/** Mocks role-grant metadata. No production business queries or test DB mutations. */
final class CrmReportAggregatePrivacyIsolationTest extends TestCase
{
    private function viewer(array $perms, array $scopes = []): User
    {
        $user = new User();
        $user->setAttribute('user_id', (string) Str::uuid());
        $user->setAttribute('status', 'active');
        $this->actingAs($user, 'web');

        $resolver = Mockery::mock(CrmPermissionResolver::class);
        $resolver->shouldReceive('allows')->andReturnUsing(
            static fn (User $actor, string $key): bool => in_array($key, $perms, true)
        );
        $resolver->shouldReceive('scopes')->andReturnUsing(
            static fn (User $actor, string $key): array => $scopes[$key] ?? []
        );
        app()->instance(CrmPermissionResolver::class, $resolver);
        return $user;
    }

    public function test_reports_only_user_cannot_trigger_any_business_query_with_pilot_on(): void
    {
        self::assertSame('testing', app()->environment());
        config()->set('crm_reporting.report_aggregate_privacy_pilot_enabled', true);
        config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
        config()->set('crm_reporting.report_customer_product_privacy_pilot_enabled', false);
        $this->viewer(['reporting.access', 'crm_page.reports.view']);
        $businessQueries = [];
        DB::listen(static function ($query) use (&$businessQueries): void {
            if (preg_match('/\\b(invoices|invoice_items|customers|opportunities|service_tickets|maintenance_schedules|maintenance_records|warranties)\\b/i', $query->sql)) {
                $businessQueries[] = $query->sql;
            }
        });
        $request = Request::create('/reports', 'GET', ['date_from' => '2026-11-01', 'date_to' => '2026-11-19']);
        $response = app(ReportController::class)->index($request);
        $data = $response->getData();
        foreach (['canViewFinancial','canViewOperations','canViewPipeline','canViewWarranty','canViewReportDetails','canExportInvoiceCsv'] as $flag) {
            self::assertFalse($data[$flag]);
        }
        self::assertNull($data['kpi']);
        self::assertSame([], $data['monthly']);
        self::assertCount(0, $data['topCustomers']);
        self::assertCount(0, $data['topProducts']);
        self::assertSame([], $businessQueries, 'A denied Reports group executed business SQL.');
    }

    public function test_explicit_full_grants_are_evaluated_by_module_and_operations_need_both(): void
    {
        self::assertSame('testing', app()->environment());
        config()->set('crm_reporting.report_aggregate_privacy_pilot_enabled', true);
        $base = ['reporting.access','crm_page.reports.view', 'billing.access','crm_page.invoices.view',
            'service.access','crm_page.service_tickets.view','crm_page.maintenance.view'];
        $viewer = $this->viewer($base, [
            'crm_page.invoices.view'=>['all'], 'crm_page.service_tickets.view'=>['all'],
            'crm_page.maintenance.view'=>['own'],
        ]);
        $policy = app(CrmReportAggregatePrivacyPilot::class)->forViewer($viewer);
        self::assertTrue($policy['financial']);
        self::assertFalse($policy['operations']);
        self::assertFalse($policy['pipeline']);
        self::assertFalse($policy['warranty']);
    }

    public function test_direct_csv_url_rejected_by_aggregate_pilot_even_when_old_pilots_off(): void
    {
        config()->set('crm_reporting.report_aggregate_privacy_pilot_enabled', true);
        config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
        config()->set('crm_reporting.report_customer_product_privacy_pilot_enabled', false);
        $this->viewer(['reporting.access','crm_page.reports.view']);
        $this->get(route('reports.export.invoices'))->assertForbidden();
        $this->get(route('reports.index', ['date_from' => 'not-a-date']))->assertSessionHasErrors('date_from');
    }
}
