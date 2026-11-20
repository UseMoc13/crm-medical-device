# A7.6.5.9 Evidence Collector — for Windows PowerShell 5.1+ / PowerShell 7+
# This script does not modify CRM business records or permission grants.
# Audit commands may READ data; PHPUnit tests are opt-in and require explicit isolated-test confirmation.
[CmdletBinding()]
param(
    [string]$OutputFile = (Join-Path ([System.IO.Path]::GetTempPath()) 'crm_a7659_evidence.txt'),
    [switch]$RunTests,
    [switch]$ConfirmIsolatedTestDatabase
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
if (-not (Test-Path (Join-Path $projectRoot 'artisan') -PathType Leaf)) {
    throw 'Jalankan collector setelah dipasang ke folder tools pada root Laravel CRM.'
}
if (-not (Test-Path (Join-Path $projectRoot 'tools/a7658_quick_check.php') -PathType Leaf)) {
    throw 'A7.6.5.8 quick check tidak ditemukan. Pastikan branch Big-Changes terbaru.'
}
if ($RunTests) {
    if (-not $ConfirmIsolatedTestDatabase) {
        throw 'Menjalankan PHPUnit membutuhkan -ConfirmIsolatedTestDatabase setelah memastikan database testing terisolasi.'
    }
    $phpunitConfig = Join-Path $projectRoot 'phpunit.xml'
    if (-not (Test-Path $phpunitConfig -PathType Leaf)) {
        throw 'phpunit.xml tidak ditemukan.'
    }
    $config = Get-Content -LiteralPath $phpunitConfig -Raw
    if ($config -notmatch '<env\s+name="DB_CONNECTION"\s+value="sqlite"\s*/>' -or
        $config -notmatch '<env\s+name="DB_DATABASE"\s+value=":memory:"\s*/>') {
        throw 'PHPUnit bukan sqlite :memory:. Periksa isolasi lingkungan dan jalankan tes manual; collector menghentikan RunTests.'
    }
}

$target = [System.IO.Path]::GetFullPath($OutputFile)
$targetDir = Split-Path -Parent $target
if (-not (Test-Path $targetDir -PathType Container)) {
    New-Item -Path $targetDir -ItemType Directory -Force | Out-Null
}
$tasks = @(
    @{Label='A7658 static'; Args=@('tools/a7658_quick_check.php')},
    @{Label='A7658 runtime'; Args=@('tools/a7658_runtime_preflight.php')}
)
if ($RunTests) {
    foreach ($name in @(
        'CrmReportAggregatePrivacyPolicyTest',
        'CrmReportAggregatePrivacySourceTest',
        'CrmReportAggregatePrivacyIsolationTest',
        'CrmReportPrivacyPilotHttpTest',
        'CrmInvoiceCsvPilotHttpTest',
        'CrmMultiRoleReportsHttpIsolationTest',
        'CrmReportAuthorizationHttpTest'
    )) {
        $tasks += @{Label="Test $name"; Args=@('artisan','test',"--filter=$name")}
    }
}
$tasks += @(
    @{Label='Role entitlements'; Args=@('artisan','crm:reporting:role-entitlements')},
    @{Label='Financial parity'; Args=@('artisan','crm:reporting:parity-baseline')},
    @{Label='KPI readiness'; Args=@('artisan','crm:reporting:baseline')},
    @{Label='Integration regression'; Args=@('artisan','crm:integration:regression')}
)

$errorsFound = 0
Push-Location $projectRoot
try {
    Set-Content -LiteralPath $target -Value "A7.6.5.9 captured CRM checks`r`n" -Encoding UTF8
    foreach ($task in $tasks) {
        $argv = [string[]]$task.Args
        $command = 'php ' + ($argv -join ' ')
        Add-Content -LiteralPath $target -Value ("PS " + $projectRoot + '> ' + $command) -Encoding UTF8
        Write-Host ('[RUN] ' + $task.Label)
        # Suppress raw business details in console; preserve local transcript for gate parser.
        $result = (& php @argv 2>&1 | Out-String -Width 800)
        $code = $LASTEXITCODE
        Add-Content -LiteralPath $target -Value $result -Encoding UTF8
        Add-Content -LiteralPath $target -Value ("A7659_EXIT_CODE=" + [string]$code) -Encoding UTF8
        if ($code -ne 0) {
            $errorsFound++
            Write-Host ("[FAIL] " + $task.Label + " (exit " + $code + ")")
        } else {
            Write-Host ('[OK] ' + $task.Label)
        }
    }
} finally {
    Pop-Location
}
Write-Host ('[INFO] Bukti tersimpan lokal: ' + $target)
Write-Host '[INFO] Periksa/redaksi log sebelum membagikan; dapat berisi informasi operasional.'
if (-not $RunTests) {
    Write-Host '[INFO] PHPUnit tidak dijalankan (default aman). Gate akan menandai EVIDENCE_INCOMPLETE sampai bukti tes ada.'
}
if ($errorsFound -gt 0) { exit 1 }
exit 0
