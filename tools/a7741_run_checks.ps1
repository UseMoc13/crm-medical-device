# A7.7.4.1 — compatible with VS Code Windows PowerShell 5.1 and PowerShell 7.
# Run from anywhere: powershell -NoProfile -ExecutionPolicy Bypass -File tools/a7741_run_checks.ps1
# This script only runs tests. No application or database changes.
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $root
try {
    & php tools/a7741_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.4.1 static checks failed' }

    & php tools/a7741_csv_policy_smoke.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.4.1 policy smoke checks failed' }

    & php artisan test --filter=CrmCsv
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.4.1 PHPUnit CSV tests failed' }

    Write-Host '[PASS] A7.7.4.1 static, policy smoke and PHPUnit checks completed.'
} finally {
    Pop-Location
}
