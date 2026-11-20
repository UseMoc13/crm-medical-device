$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

php tools/a7752_quick_check.php
if ($LASTEXITCODE -ne 0) { throw 'A7.7.5.2 static checks failed.' }

php artisan test --filter=CrmSalesOrderCsv
if ($LASTEXITCODE -ne 0) { throw 'A7.7.5.2 PHPUnit tests failed.' }

Write-Host '[PASS] A7.7.5.2 checks completed. SQLite memory test only.'
