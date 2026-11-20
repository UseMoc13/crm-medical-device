# A7.5.7 PowerShell 5.1 / 7 wrapper for VS Code integrated terminal.
# Runs the PHP tool without installation, replacement, or policy changes.
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)] [string] $Delivery,
    [Parameter(Mandatory = $true)] [string] $Item,
    [Parameter(Mandatory = $true)] [string] $Warehouse,
    [switch] $Execute,
    [string] $ApprovalFile = '',
    [string] $Confirm = ''
)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent (Split-Path -Parent $PSCommandPath)
$phpTool = Join-Path $projectRoot 'tools\a757_delivery_warehouse.php'
if (-not (Test-Path -LiteralPath $phpTool -PathType Leaf)) {
    Write-Error 'Missing tools/a757_delivery_warehouse.php. Extract ZIP into CRM project root.'
    exit 2
}
$phpArgs = @($phpTool, "--delivery=$Delivery", "--item=$Item", "--warehouse=$Warehouse")
if ($Execute) {
    $phpArgs += '--execute'
    if ($ApprovalFile -ne '') { $phpArgs += "--approval-file=$ApprovalFile" }
    if ($Confirm -ne '') { $phpArgs += "--confirm=$Confirm" }
}
& php @phpArgs
$code = $LASTEXITCODE
if ($null -eq $code) { $code = 1 }
exit ([int]$code)
