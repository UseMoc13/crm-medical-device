$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
$projectRoot = Split-Path -Parent $PSScriptRoot
Push-Location $projectRoot
try {
    Write-Host '[A7.7.7.1] Test-only compatibility fix checks'
    php tools/a7771_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.7.1 quick checks failed.' }
    php artisan test --filter='CrmCsvExportSourceTest|CrmReportAuthorizationSourceTest'
    if ($LASTEXITCODE -ne 0) { throw 'The two repaired PHPUnit suites failed.' }
    Write-Host '[A7.7.7.1] Run full A7.7.7 regression'
    powershell -NoProfile -ExecutionPolicy Bypass -File tools/a777_run_security_regression.ps1
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.7 full regression did not pass. Review failing suite before proceeding.' }
    Write-Host '[PASS] A7.7.7.1 and full security regression passed.'
} finally {
    Pop-Location
}
