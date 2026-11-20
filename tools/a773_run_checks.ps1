# A7.7.3 - for Windows PowerShell / VS Code terminal. No installer.
param([switch]$SkipPhpUnit)
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Push-Location $root
try {
    & php tools/a773_quick_check.php
    if ($LASTEXITCODE -ne 0) { throw "A7.7.3 static checks failed ($LASTEXITCODE)" }
    if (-not $SkipPhpUnit) {
        & php artisan test --filter=CrmRouteAuthorization
        if ($LASTEXITCODE -ne 0) { throw "A7.7.3 PHPUnit tests failed ($LASTEXITCODE)" }
    }
    Write-Host '[PASS] A7.7.3 completed; no migrations or application data writes.'
} finally {
    Pop-Location
}
