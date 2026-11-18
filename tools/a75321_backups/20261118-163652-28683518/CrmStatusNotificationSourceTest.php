<?php

namespace Tests\Unit;

use App\Services\CrmStatusNotificationAccess;
use App\Services\WorkspaceNotificationPreferences;
use PHPUnit\Framework\TestCase;

/** Static contract: no migration/DB writes during tests. */
final class CrmStatusNotificationSourceTest extends TestCase
{
    public function test_crm_status_notifications_are_opt_in_and_targeted(): void
    {
        $defaults = (new WorkspaceNotificationPreferences())->defaults();
        $this->assertFalse($defaults['crm_status_enabled']);
        $this->assertContains('crm_status_enabled', WorkspaceNotificationPreferences::FLAGS);
        $this->assertSame(['delivery' => 'deliveries', 'installation' => 'installations', 'invoice' => 'invoices'], CrmStatusNotificationAccess::TARGETS);
        $publisher = file_get_contents(__DIR__.'/../../app/Services/CrmStatusNotificationPublisher.php');
        $this->assertStringContainsString("->where('status', 'active')", $publisher);
        $this->assertStringContainsString('->canOpen($recipient, $type, $id)', $publisher);
        $this->assertStringContainsString("->allows($userId, 'crm_status', $type)", $publisher);
        $this->assertStringContainsString("'crm_status_enabled'", $publisher);
        $this->assertStringContainsString('array_slice(array_values($candidates), 0, 2)', $publisher);
        $this->assertStringNotContainsString('customer_name', $publisher);
        $this->assertStringNotContainsString('email', $publisher);
    }

    public function test_open_requires_page_permission_and_strict_row_access(): void
    {
        $access = file_get_contents(__DIR__.'/../../app/Services/CrmStatusNotificationAccess.php');
        $this->assertStringContainsString('CrmNavigationAccess::class', $access);
        $this->assertStringContainsString('CrmPermissionResolver::class', $access);
        $this->assertStringContainsString('CrmDataScope::class', $access);
        $this->assertStringContainsString("'crm_page.installations.view'", $access);
        $this->assertStringContainsString('salesScopeEnabled()', $access);
        $center = file_get_contents(__DIR__.'/../../app/Services/WorkspaceNotificationCenter.php');
        foreach (['deliveries.show', 'installations.show', 'invoices.show'] as $route) {
            $this->assertStringContainsString($route, $center);
        }
        $this->assertStringContainsString('CrmStatusNotificationAccess::class', $center);
    }

    public function test_status_notification_is_inside_existing_transaction(): void
    {
        $invoice = file_get_contents(__DIR__.'/../../app/Http/Controllers/InvoiceController.php');
        $method = substr($invoice, strpos($invoice, 'public function updateStatus('));
        $method = substr($method, 0, strpos($method, 'public function destroy('));
        $this->assertStringContainsString('DB::transaction(', $method);
        $this->assertStringContainsString('CrmInvoiceRowGuard::class', $method);
        $this->assertSame(1, substr_count($method, 'CrmStatusNotificationPublisher::class'));
        $this->assertLessThan(strpos($method, 'return null;'), strpos($method, 'CrmStatusNotificationPublisher::class'));
        $provider = file_get_contents(__DIR__.'/../../app/Providers/AppServiceProvider.php');
        $this->assertSame(2, substr_count($provider, 'CrmCrossModuleStatusNotificationObserver::class'));
        $this->assertSame(2, substr_count($provider, 'CrmCrossModuleStatusAuditObserver::class'));
    }
}
