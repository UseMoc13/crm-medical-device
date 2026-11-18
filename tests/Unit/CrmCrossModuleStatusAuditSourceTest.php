<?php

namespace Tests\Unit;

use App\Services\WorkspaceAuditTrail;
use PHPUnit\Framework\TestCase;

/** Static coverage: no migrations, database writes, seeding or factories. */
final class CrmCrossModuleStatusAuditSourceTest extends TestCase
{
    private function source(string $path): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/' . $path);
        self::assertIsString($source);
        return $source;
    }

    public function test_audit_catalog_explicitly_accepts_three_crm_status_events(): void
    {
        foreach (['delivery', 'installation', 'invoice'] as $subject) {
            self::assertContains($subject, WorkspaceAuditTrail::SUBJECTS);
            self::assertContains($subject . '.status_changed', WorkspaceAuditTrail::EVENTS);
        }
    }

    public function test_eloquent_observer_records_status_only_without_free_text(): void
    {
        $observer = $this->source('app/Observers/CrmCrossModuleStatusAuditObserver.php');
        $provider = $this->source('app/Providers/AppServiceProvider.php');
        self::assertStringContainsString("wasChanged('status')", $observer);
        self::assertStringContainsString("'status_from'", $observer);
        self::assertStringContainsString("'status_to'", $observer);
        self::assertStringNotContainsString("'notes' =>", $observer);
        self::assertStringNotContainsString("'delivery_address' =>", $observer);
        self::assertSame(2, substr_count($provider, 'CrmCrossModuleStatusAuditObserver::class'));
    }

    public function test_invoice_records_audit_within_row_locked_transaction(): void
    {
        $controller = $this->source('app/Http/Controllers/InvoiceController.php');
        $start = strpos($controller, 'public function updateStatus(');
        $end = strpos($controller, 'public function destroy(', $start === false ? 0 : $start);
        self::assertNotFalse($start);
        self::assertNotFalse($end);
        $method = substr($controller, $start, $end - $start);
        self::assertStringContainsString('DB::transaction(', $method);
        self::assertStringContainsString('CrmInvoiceRowGuard::class', $method);
        self::assertSame(1, substr_count($method, "'invoice.status_changed'"));
        self::assertGreaterThan(strpos($method, '->update(['), strpos($method, "'invoice.status_changed'"));
        self::assertLessThan(strpos($method, 'return null;'), strpos($method, "'invoice.status_changed'"));
        self::assertStringNotContainsString("'notes' =>", substr($method, strpos($method, "'invoice.status_changed'")));
    }
}
