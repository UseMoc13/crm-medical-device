# A7.7.2: Run from the VS Code PowerShell terminal (Windows PowerShell 5.1 or PS 7).
# No installers, migrations, seeders, env changes, or production database writes.
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $projectRoot
try {
    if ((git branch --show-current) -ne 'Big-Changes') {
        Write-Host '[STOP] Expected branch Big-Changes.'
        exit 2
    }
    & php tools/a772_auth_quick_check.php
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    & php tools/a772_policy_smoke.php
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    & php artisan test --filter=CrmLoginThrottle
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    Write-Host '[PASS] A7.7.2 static and unit/isolated HTTP tests completed.'
}
finally {
    Pop-Location
}
