# A7.5.6 - VS Code PowerShell 5.1+ wrapper (no installer or file replacements)
$ErrorActionPreference = 'Stop'
$phpScript = Join-Path $PSScriptRoot 'a756_historical_evidence.php'
if (-not (Test-Path -LiteralPath $phpScript -PathType Leaf)) {
    Write-Error 'Missing tools/a756_historical_evidence.php. Copy the complete tools folder.'
    exit 2
}
$php = Get-Command 'php' -ErrorAction SilentlyContinue
if ($null -eq $php) {
    Write-Error 'php was not found in PATH. Verify PHP 8.2 in the VS Code terminal.'
    exit 2
}
& $php.Source $phpScript
$code = $LASTEXITCODE
if ($null -eq $code) { $code = 2 }
exit $code
