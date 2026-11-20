param(
    [Parameter(Mandatory=$true)][string]$Installation,
    [Parameter(Mandatory=$true)][string]$Invoice
)
$ErrorActionPreference = 'Stop'
$tool = Join-Path $PSScriptRoot 'a758_incident_review.php'
if (-not (Test-Path -LiteralPath $tool -PathType Leaf)) {
    Write-Error 'a758_incident_review.php not found next to this PowerShell script.'
    exit 2
}
$php = Get-Command php -ErrorAction SilentlyContinue
if ($null -eq $php) {
    Write-Error 'PHP CLI not found in VS Code PowerShell PATH.'
    exit 2
}
& $php.Source $tool "--installation=$Installation" "--invoice=$Invoice"
exit $LASTEXITCODE
