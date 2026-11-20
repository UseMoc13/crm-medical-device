$ErrorActionPreference='Stop'
Set-Location (Split-Path -Parent $PSScriptRoot)
function Run-Php([string[]] $parameters) {
 & php @parameters
 if ($LASTEXITCODE -ne 0) { throw "Failed: php $($parameters -join ' ')" }
}
Run-Php -parameters @('tools/a7762_quick_check.php')
Run-Php -parameters @('artisan','test','--filter=CrmSalesOrderPaginationSqliteIsolation')
Run-Php -parameters @('artisan','test','--filter=CrmDashboardPresentationReliability')
Run-Php -parameters @('artisan','test','--filter=CrmReportingReliabilityGuard')
Write-Host '[PASS] A7.7.6.2 checks complete (SQLite memory fixture only).'
