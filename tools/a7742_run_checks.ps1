# A7.7.4.2 VS Code / Windows PowerShell 5.1+ runner. No installer.
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $root
$steps = @(
    @('tools/a7742_quick_check.php'),
    @('tools/a7742_path_smoke.php')
)
foreach ($step in $steps) {
    & php $step[0]
    if ($LASTEXITCODE -ne 0) { throw "A7.7.4.2 check failed: $($step[0])" }
}
& php artisan test --filter=CrmPrivateFile
if ($LASTEXITCODE -ne 0) { throw 'A7.7.4.2 PHPUnit failed.' }
Write-Host '[PASS] A7.7.4.2 checks completed. No CRM database writes.'
