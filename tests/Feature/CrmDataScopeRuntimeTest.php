<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Opt-in, read-only probes against a dedicated PostgreSQL TEST database.
 * No RefreshDatabase, seeders, migrations, or write HTTP methods.
 */
class CrmDataScopeRuntimeTest extends TestCase
{
    private const CASES = [
        'customers' => ['route' => 'customers.show', 'flag' => 'customers_enabled', 'table' => 'customers', 'key' => 'customer_id'],
        'leads' => ['route' => 'leads.show', 'flag' => 'leads_enabled', 'table' => 'leads', 'key' => 'lead_id'],
        'opportunities' => ['route' => 'opportunities.show', 'flag' => 'opportunities_enabled', 'table' => 'opportunities', 'key' => 'opportunity_id'],
        'service_tickets' => ['route' => 'service-tickets.show', 'flag' => 'service_tickets_enabled', 'table' => 'service_tickets', 'key' => 'service_ticket_id'],
        'maintenance' => ['route' => 'maintenance.show', 'flag' => 'maintenance_schedules_enabled', 'table' => 'maintenance_schedules', 'key' => 'maintenance_schedule_id'],
        'installations' => ['route' => 'installations.show', 'flag' => 'installations_enabled', 'table' => 'installations', 'key' => 'installation_id'],
    ];

    public function test_scope_get_authorization_matrix(): void
    {
        if (getenv('CRM_SCOPE_TEST_RUN') !== '1') {
            $this->markTestSkipped('Set CRM_SCOPE_TEST_RUN=1 to opt in.');
        }

        // Explicit safety gates: never silently fall back to the usual SQLite test DB
        // or any production database. Set an isolated dedicated PostgreSQL database.
        $connection = (string) Config::get('database.default');
        $dbName = (string) DB::connection()->getDatabaseName();
        $expectedDb = trim((string) getenv('CRM_SCOPE_TEST_DB_NAME'));
        $actualEnv = (string) app()->environment();
        $this->assertSame('testing', $actualEnv, 'APP_ENV must be testing.');
        $this->assertSame('pgsql', $connection, 'Use PostgreSQL for CRM schema.');
        $this->assertNotSame('', $expectedDb, 'CRM_SCOPE_TEST_DB_NAME is required.');
        $this->assertSame($expectedDb, $dbName, 'Database name does not match the explicit test database.');
        $this->assertMatchesRegularExpression('/(^|[_-])(test|testing)([_-]|$)/i', $dbName, 'Use a dedicated database containing test/testing in its name.');

        $tests = 0;
        foreach (self::CASES as $subject => $definition) {
            $prefix = 'CRM_SCOPE_TEST_'.strtoupper($subject);
            $allowedUser = trim((string) getenv($prefix.'_ALLOWED_USER_ID'));
            $allowedRecord = trim((string) getenv($prefix.'_ALLOWED_RECORD_ID'));
            $deniedUser = trim((string) getenv($prefix.'_DENIED_USER_ID'));
            $deniedRecord = trim((string) getenv($prefix.'_DENIED_RECORD_ID'));

            // All four values required for one full positive + negative subject pair.
            $values = [$allowedUser, $allowedRecord, $deniedUser, $deniedRecord];
            if (count(array_filter($values, static fn ($value) => $value !== '')) === 0) {
                continue;
            }
            $this->assertNotContains('', $values, "Incomplete test pair for {$subject}; supply all 4 IDs.");
            $this->assertTrue(Route::has($definition['route']), "Missing route {$definition['route']}.");
            $this->assertTrue((bool) Config::get('crm_data_scope.'.$definition['flag'], false), "Scope flag for {$subject} is OFF; set it in dedicated TEST environment.");
            $this->assertTrue($this->isUuid($allowedUser) && $this->isUuid($allowedRecord) && $this->isUuid($deniedUser) && $this->isUuid($deniedRecord), "IDs must be UUIDs for {$subject}.");
            $this->assertNotSame($allowedUser, $deniedUser, "Use different users for positive and negative {$subject} tests.");
            $this->assertNotSame($allowedRecord, $deniedRecord, "Use different records for positive and negative {$subject} tests.");

            $this->assertTrue(DB::table($definition['table'])->where($definition['key'], $allowedRecord)->exists(), "Allowed record does not exist for {$subject}.");
            $this->assertTrue(DB::table($definition['table'])->where($definition['key'], $deniedRecord)->exists(), "Denied record does not exist for {$subject}; missing-record 404 must not be mistaken for security.");

            $allowedPrincipal = User::query()->findOrFail($allowedUser);
            $deniedPrincipal = User::query()->findOrFail($deniedUser);

            // The operator must choose a record that really belongs to each test category.
            // If role/scope is misconfigured, the test should FAIL rather than silently PASS.
            $allowed = $this->actingAs($allowedPrincipal)->get(route($definition['route'], [$allowedRecord]));
            $this->assertSame(200, $allowed->getStatusCode(), "Expected HTTP 200 for allowed {$subject}; got {$allowed->getStatusCode()}.");
            $tests++;

            $denied = $this->actingAs($deniedPrincipal)->get(route($definition['route'], [$deniedRecord]));
            $this->assertContains($denied->getStatusCode(), [403, 404], "Expected HTTP 403/404 for denied {$subject}; got {$denied->getStatusCode()}.");
            $tests++;
        }

        $this->assertGreaterThan(0, $tests, 'No subject pairs configured. Set CRM_SCOPE_TEST_CUSTOMERS_* etc.');
        fwrite(STDOUT, "\nCRM Scope HTTP GET assertions completed: {$tests} (positive/negative); no write methods used.\n");
    }

    private function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }
}
