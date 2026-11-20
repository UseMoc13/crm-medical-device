# A7.8.3: selected safe tests; NEVER run whole suite or PostgreSQL UAT.
param([switch]$QuickOnly)
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $root
$keys = @('APP_ENV','DB_CONNECTION','DB_DATABASE','DB_URL','CACHE_STORE','SESSION_DRIVER','QUEUE_CONNECTION')
$old = @{}
foreach ($k in $keys) { $old[$k] = [Environment]::GetEnvironmentVariable($k,'Process') }
$ok = $true
try {
    & php tools/a783_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.8.3 quick check failed.' }
    & php tools/a783_workflow_smoke.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.8.3 smoke failed.' }
    if (Test-Path 'bootstrap/cache/config.php') { throw 'Cached config found; run php artisan optimize:clear before isolated tests.' }
    if (-not $QuickOnly) {
        $env:APP_ENV = 'testing'
        $env:DB_CONNECTION = 'sqlite'
        $env:DB_DATABASE = ':memory:'
        $env:DB_URL = ''
        $env:CACHE_STORE = 'array'
        $env:SESSION_DRIVER = 'array'
        $env:QUEUE_CONNECTION = 'sync'
        $groups = [ordered]@{
            'A7.8.3 WORKFLOW MANIFEST + SECURITY' = @(
                'CrmUatWorkflowCoverageTest','CrmIntegrationUatManifestTest',
                'CrmUatDataReadinessTest','CrmSecurityResponseBoundaryHttpTest',
                'CrmRouteAuthorizationHttpTest'
            )
            'A7.8.3 DISPATCH + TRANSACTION ATOMICITY' = @(
                'CrmDeliveryCompletionGuardTest','CrmDeliveryDispatchPostconditionIsolationTest',
                'CrmIntegrationAtomicityIsolatedTest','CrmStatusNotificationPrivacyTest'
            )
            'A7.8.3 BILLING + ANNOUNCEMENTS + CSV' = @(
                'CrmStagingInvoiceParitySqliteTest','CrmAnnouncementScheduleIsolatedTest',
                'CrmSalesOrderCsvMultiBatchReliabilityTest',
                'CrmSalesOrderPaginationSqliteIsolationTest'
            )
        }
        foreach ($label in $groups.Keys) {
            $pattern = '(' + (($groups[$label] | ForEach-Object { [regex]::Escape($_) }) -join '|') + ')'
            Write-Host "`n[RUN] $label"
            & php artisan test ('--filter=' + $pattern)
            if ($LASTEXITCODE -ne 0) { throw "Isolated regression failed: $label" }
        }
    }
    Write-Host '[PASS] A7.8.3 selected pre-UAT checks complete. Manual UAT still NOT EXECUTED.'
} catch {
    $ok = $false
    Write-Host ('[FAIL] ' + $_.Exception.Message)
} finally {
    foreach ($k in $keys) { [Environment]::SetEnvironmentVariable($k,$old[$k],'Process') }
    Pop-Location
}
if (-not $ok) { exit 1 }
