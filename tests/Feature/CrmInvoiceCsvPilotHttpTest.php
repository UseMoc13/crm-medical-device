<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CrmPermissionResolver;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

/** HTTP route checks use a fake active user and mocked grants; no invoice queries. */
final class CrmInvoiceCsvPilotHttpTest extends TestCase
{
    private function signIn(array $permissions, array $scopes = []): void
    {
        $user = new User();
        $user->setAttribute('user_id', (string) Str::uuid());
        $user->setAttribute('status', 'active');
        $this->actingAs($user, 'web');

        $resolver = Mockery::mock(CrmPermissionResolver::class);
        $resolver->shouldReceive('allows')->andReturnUsing(
            static fn (User $viewer, string $permission): bool => in_array($permission, $permissions, true)
        );
        $resolver->shouldReceive('scopes')->andReturnUsing(
            static fn (User $viewer, string $permission): array => $permission === 'crm_page.invoices.view' ? $scopes : []
        );
        app()->instance(CrmPermissionResolver::class, $resolver);
    }

    public function test_strict_csv_pilot_denies_reports_only_without_breaking_reports_page(): void
    {
        config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', true);
        $this->signIn(['reporting.access', 'crm_page.reports.view']);
        $this->get(route('reports.export.invoices'))->assertForbidden();
        // Invalid date stops BEFORE business SQL; Reports page authorization still works.
        $this->get(route('reports.index', ['date_from' => 'not-a-date']))->assertSessionHasErrors('date_from');
    }

    public function test_strict_csv_pilot_denies_null_and_partial_invoice_scopes(): void
    {
        config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', true);
        $grants = ['reporting.access', 'crm_page.reports.view', 'billing.access', 'crm_page.invoices.view'];
        $this->signIn($grants, ['own']);
        $this->get(route('reports.export.invoices'))->assertForbidden();
    }

    public function test_strict_csv_all_grant_can_reach_date_validation_without_invoice_query(): void
    {
        config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', true);
        $grants = ['reporting.access', 'crm_page.reports.view', 'billing.access', 'crm_page.invoices.view'];
        $this->signIn($grants, ['all']);
        $this->get(route('reports.export.invoices', ['date_from' => 'not-a-date']))
            ->assertSessionHasErrors('date_from');
    }

    public function test_flag_off_preserves_reports_only_csv_entrypoint(): void
    {
        config()->set('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
        $this->signIn(['reporting.access', 'crm_page.reports.view']);
        $this->get(route('reports.export.invoices', ['date_from' => 'not-a-date']))
            ->assertSessionHasErrors('date_from');
    }
}
