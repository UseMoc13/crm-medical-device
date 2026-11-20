$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$previousAppEnv = [Environment]::GetEnvironmentVariable('APP_ENV', 'Process')
$previousDbConnection = [Environment]::GetEnvironmentVariable('DB_CONNECTION', 'Process')
$previousDbDatabase = [Environment]::GetEnvironmentVariable('DB_DATABASE', 'Process')
Push-Location $root
try {
    & php tools/a784_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.8.4 static checks failed.' }
    & php tools/a784_ledger_smoke.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.8.4 ledger smoke failed.' }
    & php tools/a784_validate_uat_ledger.php --ledger=docs/uat/a784_uat_execution_template.json
    if ($LASTEXITCODE -ne 0) { throw 'A7.8.4 template validation failed.' }

    $env:APP_ENV = 'testing'
    $env:DB_CONNECTION = 'sqlite'
    $env:DB_DATABASE = ':memory:'
    & php artisan test --filter=CrmUatExecutionLedgerTest
    if ($LASTEXITCODE -ne 0) { throw 'A7.8.4 PHPUnit failed.' }
    Write-Host '[PASS] A7.8.4 tests complete. Manual UAT remains NOT_EXECUTED.'
}
finally {
    [Environment]::SetEnvironmentVariable('APP_ENV', $previousAppEnv, 'Process')
    [Environment]::SetEnvironmentVariable('DB_CONNECTION', $previousDbConnection, 'Process')
    [Environment]::SetEnvironmentVariable('DB_DATABASE', $previousDbDatabase, 'Process')
    Pop-Location
}
