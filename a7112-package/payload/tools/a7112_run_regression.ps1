# A7.11.2 - Nonproduction regression only. No migrations, reset, seed, or HTTP business writes.
[CmdletBinding()]
param([switch]$Run, [switch]$FullSuite)
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location $root
if (-not $Run) {
    Write-Host '[SAFE] Dry-run. Use -Run to execute source checks and isolated SQLite tests.'
    Write-Host '[SAFE] Use -Run -FullSuite to include the full PHPUnit suite.'
    exit 0
}
$envKeys = @('APP_ENV','DB_CONNECTION','DB_DATABASE','DB_URL','CACHE_STORE','SESSION_DRIVER','QUEUE_CONNECTION')
$old = @{}
foreach ($key in $envKeys) { $old[$key] = [Environment]::GetEnvironmentVariable($key, 'Process') }
# Isolate PHPUnit-compatible defaults and block any user-level DB URL override.
$env:APP_ENV = 'testing'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
$env:DB_URL = ''
$env:CACHE_STORE = 'array'
$env:SESSION_DRIVER = 'array'
$env:QUEUE_CONNECTION = 'sync'
$reportDir = Join-Path $root 'storage/app/private/a7112_reports'
New-Item -ItemType Directory -Path $reportDir -Force | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$report = Join-Path $reportDir "a7112_regression_$stamp.txt"
$results = @()
function Invoke-Step([string]$title, [string]$program, [string[]]$arguments) {
    Write-Host "[RUN] $title"
    $out = & $program @arguments 2>&1 | Out-String
    $code = $LASTEXITCODE
    Add-Content -LiteralPath $report -Value "=== $title exit=$code ===`r`n$out" -Encoding UTF8
    Write-Host $out
    $script:results += [pscustomobject]@{Name=$title; ExitCode=$code}
}
try {
    Invoke-Step 'Source and PHPUnit isolation preflight' 'php' @('tools/a7112_safe_preflight.php')
    if ($results[-1].ExitCode -ne 0) { throw 'Isolation preflight failed; tests blocked.' }
    Invoke-Step 'Static route audit' 'php' @('tools/a7108_full_audit.php','--check')
    Invoke-Step 'Navigation regression' 'php' @('tools/a7108_navigation_fix.php','--check')
    Invoke-Step 'JavaScript dialog regression' 'node' @('tools/a7108_js_regression.cjs')
    Invoke-Step 'UAT manifest source check' 'php' @('tools/a781_quick_check.php')
    Invoke-Step 'Workflow manifest source check' 'php' @('tools/a783_quick_check.php')
    $filters = @('CrmRouteAuthorizationHttpTest','CrmRoleAwareCrudVisibilityTest','CrmDeliveryDispatchPostconditionIsolationTest','CrmReportPrivacyPilotHttpTest','CrmReportAggregatePrivacyIsolationTest','WorkspacePersonalNoteHttpTest','WorkspaceCollaborationHttpTest','WorkspaceNotificationHttpTest','WorkspaceNotificationPreferencesHttpTest','CrmSalesOrderCsvMultiBatchReliabilityTest')
    foreach ($filter in $filters) {
       Invoke-Step "Isolated PHPUnit $filter" 'php' @('artisan','test',"--filter=$filter")
    }
    if ($FullSuite) { Invoke-Step 'Full isolated PHPUnit suite' 'php' @('artisan','test') }
    $bad = @($results | Where-Object { $_.ExitCode -ne 0 })
    $summary = "[RESULT] A7112_REGRESSION_$($(if($bad.Count){'FINDINGS'}else{'PASS'})) steps=$($results.Count) failed=$($bad.Count) manual_uat_not_executed"
    Write-Host $summary
    Add-Content -LiteralPath $report -Value $summary -Encoding UTF8
    Write-Host "[PRIVATE_REPORT] $report"
    if ($bad.Count) { exit 1 }
    exit 0
} finally {
    foreach ($key in $envKeys) { [Environment]::SetEnvironmentVariable($key,$old[$key],'Process') }
}
