# A7.8.2 only static / pure unit tests. No business DB or PostgreSQL action.
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Push-Location $root
try {
    & php tools/a782_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.8.2 quick checks failed.' }
    & php tools/a782_readiness_smoke.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.8.2 smoke checks failed.' }
    & php artisan test --filter=CrmUatDataReadinessTest
    if ($LASTEXITCODE -ne 0) { throw 'A7.8.2 unit tests failed.' }
    Write-Host '[PASS] A7.8.2 checks complete. No CRM database used.'
} finally { Pop-Location }
