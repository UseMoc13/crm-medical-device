$ErrorActionPreference = 'Stop'
Push-Location (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
try {
    php tools/a7761_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.6.1 static checks failed.' }
    php artisan test --filter=CrmSalesOrderCsvReliability
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.6.1 PHPUnit tests failed.' }
    php artisan test --filter=CrmSalesOrderCsvMultiBatchReliability
    if ($LASTEXITCODE -ne 0) { throw 'A7.7.6.1 multi-batch PHPUnit tests failed.' }
    Write-Host '[PASS] A7.7.6.1 checks completed. SQLite in-memory fixtures only.'
} finally {
    Pop-Location
}
