<?php
namespace Tests\Unit;

use App\Support\CrmIntegrationUatManifest as Manifest;
use PHPUnit\Framework\TestCase;

/** Pure inventory test, no framework boot, fixtures, or DB. */
final class CrmIntegrationUatManifestTest extends TestCase
{
    public function test_expected_workflow_routes_are_named_and_methods_are_explicit(): void
    {
        $routes=Manifest::routeGroups();
        self::assertContains(['deliveries.change-status','PATCH'],$routes['Delivery & Installation']);
        self::assertContains(['installations.change-status','PATCH'],$routes['Delivery & Installation']);
        self::assertContains(['service-tickets.change-status','PATCH'],$routes['Warranty & Service']);
        self::assertContains(['maintenance.change-status','PATCH'],$routes['Warranty & Service']);
        self::assertContains(['quotations.change-status','POST'],$routes['Sales CRM']);
        self::assertContains(['invoices.status','PATCH'],$routes['Billing & Reporting']);
        self::assertContains(['sales_orders.from_quotation','POST'],$routes['Sales CRM']);
    }

    public function test_route_manifest_is_unique_and_schema_references_exist(): void
    {
        $names=[];
        foreach(Manifest::routeGroups() as $entries){foreach($entries as [$name,$method]){
            $names[]=$name;
            self::assertContains($method,['GET','POST','PUT','PATCH','DELETE']);
        }}
        self::assertSame(count($names),count(array_unique($names)));
        $tables=[];
        foreach(Manifest::schemaGroups() as $entries) foreach($entries as $t) $tables[$t]=true;
        foreach(Manifest::relationshipHints() as [$source,$column,$target]){
            self::assertArrayHasKey($source,$tables);
            self::assertArrayHasKey($target,$tables);
            self::assertNotSame('',$column);
        }
    }
}
