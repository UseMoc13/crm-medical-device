$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Push-Location $root
try {
    & php tools/a7751_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'Static checks failed' }
    & php tools/a7751_plan_smoke.php
    if ($LASTEXITCODE -ne 0) { throw 'Smoke tests failed' }
    & php artisan test --filter=CrmQueryPlanDigestTest
    if ($LASTEXITCODE -ne 0) { throw 'PHPUnit digest tests failed' }
    & php artisan test --filter=CrmPerformanceBaselineSourceTest
    if ($LASTEXITCODE -ne 0) { throw 'PHPUnit source tests failed' }
    Write-Host '[PASS] A7.7.5.1 checks complete; no PostgreSQL connection used.'
} finally {
    Pop-Location
}
