# A7.5.5 -- Windows PowerShell 5.1 and VS Code PowerShell terminal.
# Dry run: powershell -NoProfile -ExecutionPolicy Bypass -File tools/a755_apply_dispatch_fix.ps1 -DryRun
# Apply:   powershell -NoProfile -ExecutionPolicy Bypass -File tools/a755_apply_dispatch_fix.ps1
param([switch]$DryRun)

$ErrorActionPreference = 'Stop'
$phpTool = Join-Path $PSScriptRoot 'a755_apply_dispatch_fix.php'
if (-not (Test-Path -LiteralPath $phpTool -PathType Leaf)) {
    Write-Host '[STOP] Missing companion PHP tool in tools folder.' -ForegroundColor Red
    exit 1
}

try {
    if ($DryRun) {
        & php $phpTool --dry-run
    } else {
        & php $phpTool
    }
    $code = $LASTEXITCODE
    if ($null -eq $code) { exit 1 }
    exit [int]$code
} catch {
    Write-Host ('[STOP] ' + $_.Exception.Message) -ForegroundColor Red
    exit 1
}
