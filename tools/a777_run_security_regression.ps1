# A7.7.7 — named tests only, with test environment overrides restored afterwards.
# Does NOT run all tests; excludes opt-in PostgreSQL runtime tests and migrations.
param([switch]$QuickOnly)
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $root
$variables = @('APP_ENV','DB_CONNECTION','DB_DATABASE','DB_URL','CACHE_STORE','SESSION_DRIVER','QUEUE_CONNECTION')
$previous = @{}
foreach ($key in $variables) { $previous[$key] = [Environment]::GetEnvironmentVariable($key,'Process') }
$ok = $true
try {
    $env:APP_ENV = 'testing'
    $env:DB_CONNECTION = 'sqlite'
    $env:DB_DATABASE = ':memory:'
    $env:DB_URL = ''
    $env:CACHE_STORE = 'array'
    $env:SESSION_DRIVER = 'array'
    $env:QUEUE_CONNECTION = 'sync'

    Write-Host '[A7.7.7] Safe environment preflight (no PostgreSQL)'
    & php tools/a777_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'Quick check failed. Do not run test matrix until fixed.' }
    & php tools/a771_privacy_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.1 compatibility regression.' }
    & php tools/a773_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.3 compatibility regression.' }

    if (-not $QuickOnly) {
        $groups = [ordered]@{
            'A7.7.7 AUTH + RBAC + RESPONSE' = @(
                'CrmResponseCachePrivacyTest','CrmLoginThrottlePolicyTest','CrmLoginThrottleSourceTest',
                'CrmLoginThrottleHttpIsolationTest','CrmRouteAuthorizationPolicyTest',
                'CrmRouteAuthorizationHttpTest','CrmSecurityResponseBoundaryHttpTest'
            )
            'A7.7.7 REPORT + FINANCE PRIVACY + CSV' = @(
                'CrmReportAuthorizationHttpTest','CrmReportAuthorizationSourceTest',
                'CrmInvoiceCsvPilotHttpTest','CrmReportAggregatePrivacyIsolationTest',
                'CrmMultiRoleReportsHttpIsolationTest','CrmReportingReliabilityGuardTest',
                'CrmCsvCellSanitizerTest','CrmCsvExportSourceTest'
            )
            'A7.7.7 PRIVATE FILE + DASHBOARD PRIVACY' = @(
                'CrmPrivateFilePathTest','CrmPrivateFileAccessSourceTest',
                'CrmAnnouncementExposurePolicyTest','CrmDashboardPresentationReliabilityTest',
                'CrmDashboardViewerIsolationTest'
            )
            'A7.7.7 PAGINATION + MULTIBATCH CSV' = @(
                'CrmSalesOrderCsvSqliteIsolationTest','CrmSalesOrderCsvMultiBatchReliabilityTest',
                'CrmSalesOrderPaginationSqliteIsolationTest','CrmSalesOrderCsvReliabilitySourceTest'
            )
        }
        foreach ($group in $groups.Keys) {
            $pattern = '(' + (($groups[$group] | ForEach-Object { [regex]::Escape($_) }) -join '|') + ')'
            Write-Host "`n[RUN] $group"
            & php artisan test ('--filter=' + $pattern)
            if ($LASTEXITCODE -ne 0) { throw "Regression failed in: $group" }
        }
    }
    Write-Host "`n[PASS] A7.7.7 selected checks complete. No CRM PostgreSQL tests selected."
} catch {
    $ok = $false
    Write-Host ('[FAIL] ' + $_.Exception.Message)
} finally {
    foreach ($key in $variables) { [Environment]::SetEnvironmentVariable($key,$previous[$key],'Process') }
    Pop-Location
}
if (-not $ok) { exit 1 }
