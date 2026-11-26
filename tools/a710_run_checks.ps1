# A7.10.1/2 — Non-destructive, selected regression suites and read-only checks.
# Usage: powershell -ExecutionPolicy Bypass -File tools/a710_run_checks.ps1 [-QuickOnly] [-WithDatabase]
param([switch]$QuickOnly, [switch]$WithDatabase)
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$envKeys = @('APP_ENV','APP_DEBUG','DB_CONNECTION','DB_DATABASE','DB_URL','CACHE_STORE','SESSION_DRIVER','QUEUE_CONNECTION','MAIL_MAILER')
$previous = @{}
foreach ($key in $envKeys) { $previous[$key] = [Environment]::GetEnvironmentVariable($key, 'Process') }
$ok = $true
Push-Location $root
try {
    if (Test-Path 'bootstrap/cache/config.php') {
        throw 'Config cache exists; run php artisan optimize:clear BEFORE this audit to avoid testing against wrong DB.'
    }
    Write-Host '[A7.10] Read-only source & route contracts'
    & php tools/a710_source_check.php
    if ($LASTEXITCODE -ne 0) { throw 'A710 source check failed.' }
    & php tools/a710_route_security_gate.php --routes
    if ($LASTEXITCODE -ne 0) { throw 'A710 route security gate failed.' }
    & php tools/a783_workflow_preflight.php --routes
    if ($LASTEXITCODE -ne 0) { throw 'Existing UAT workflow preflight failed.' }
    if (-not $QuickOnly) {
        # Override only PROCESS env. These tests must never connect to CRM PostgreSQL.
        $env:APP_ENV = 'testing'
        $env:DB_CONNECTION = 'sqlite'
        $env:DB_DATABASE = ':memory:'
        $env:DB_URL = ''
        $env:CACHE_STORE = 'array'
        $env:SESSION_DRIVER = 'array'
        $env:QUEUE_CONNECTION = 'sync'
        $env:MAIL_MAILER = 'array'
        $suites = [ordered]@{
            'AUTH + RBAC + assignment' = @('CrmFinalRouteSecurityCatalogTest','CrmRouteAuthorizationPolicyTest','CrmRouteAuthorizationHttpTest','CrmSecurityResponseBoundaryHttpTest','CrmLoginThrottleHttpIsolationTest','CrmUserAssignmentEligibilityConfigTest','CrmWorkLogAuthorPolicyTest','CrmOperationalAssignmentPolicyTest')
            'Workflow + transaction' = @('CrmDeliveryCompletionGuardTest','CrmDeliveryDispatchPostconditionIsolationTest','CrmIntegrationAtomicityIsolatedTest','CrmStatusNotificationPrivacyTest','CrmUatWorkflowCoverageTest','CrmIntegrationUatManifestTest')
            'Privacy + private files' = @('CrmReportAuthorizationHttpTest','CrmMultiRoleReportsHttpIsolationTest','CrmReportAggregatePrivacyIsolationTest','CrmPrivateFilePathTest','CrmPrivateFileAccessSourceTest','CrmCsvCellSanitizerTest')
            'Billing + scalability + UAT register' = @('CrmStagingInvoiceParitySqliteTest','CrmSalesOrderCsvMultiBatchReliabilityTest','CrmSalesOrderPaginationSqliteIsolationTest','CrmUatExecutionLedgerTest')
        }
        foreach ($label in $suites.Keys) {
            $pattern = '(' + (($suites[$label] | ForEach-Object { [regex]::Escape($_) }) -join '|') + ')'
            Write-Host ('[RUN] ' + $label)
            & php artisan test ('--filter=' + $pattern)
            if ($LASTEXITCODE -ne 0) { throw ('Regression suite failed: ' + $label) }
        }
    }
    # RESTORE env BEFORE the opt-in real PostgreSQL review.
    foreach ($key in $envKeys) { [Environment]::SetEnvironmentVariable($key, $previous[$key], 'Process') }
    if ($WithDatabase) {
        Write-Host '[A7.10] Local PostgreSQL READ ONLY audit (opt-in)'
        & php tools/a710_rbac_db_preflight.php --local-readonly
        if ($LASTEXITCODE -ne 0) { throw 'RBAC database integrity review failed.' }
        & php tools/a799_integration_gate.php --limit=0 --strict
        if ($LASTEXITCODE -ne 0) { throw 'A799 operational assignment regression detected.' }
    }
    Write-Host '[RESULT] A710_AUTOMATED_BASELINE_READY; manual UAT and policy signoff are NOT complete.'
} catch {
    $ok = $false
    Write-Host ('[FAIL] ' + $_.Exception.Message)
} finally {
    foreach ($key in $envKeys) { [Environment]::SetEnvironmentVariable($key, $previous[$key], 'Process') }
    Pop-Location
}
if (-not $ok) { exit 1 }
