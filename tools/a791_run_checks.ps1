$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Push-Location $root
$oldApp = [Environment]::GetEnvironmentVariable('APP_ENV', 'Process')
$oldDb = [Environment]::GetEnvironmentVariable('DB_CONNECTION', 'Process')
$oldName = [Environment]::GetEnvironmentVariable('DB_DATABASE', 'Process')
try {
    Write-Host '[A7.9.1] STATIC + SYNTHETIC SCHEMA SMOKE (NO DATABASE)'
    & php tools/a791_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'Static check failed.' }
    & php tools/a791_snapshot_smoke.php
    if ($LASTEXITCODE -ne 0) { throw 'Synthetic smoke check failed.' }
    $env:APP_ENV = 'testing'
    $env:DB_CONNECTION = 'sqlite'
    $env:DB_DATABASE = ':memory:'
    & php artisan test --filter=CrmUatSchemaSnapshotTest
    if ($LASTEXITCODE -ne 0) { throw 'PHPUnit failed.' }
    Write-Host '[PASS] A7.9.1 offline checks passed. No PostgreSQL accessed.'
} finally {
    [Environment]::SetEnvironmentVariable('APP_ENV', $oldApp, 'Process')
    [Environment]::SetEnvironmentVariable('DB_CONNECTION', $oldDb, 'Process')
    [Environment]::SetEnvironmentVariable('DB_DATABASE', $oldName, 'Process')
    Pop-Location
}
