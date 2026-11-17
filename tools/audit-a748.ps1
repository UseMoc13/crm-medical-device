# A7.4.8 — Activity & TO-DO Collaboration, non-destructive local audit
# Run from project root: powershell -ExecutionPolicy Bypass -File .\audit-a748.ps1
# This script never calls migrate, seed, test, or endpoints and never changes the CRM database.
[CmdletBinding()]
param(
    [string]$ProjectPath = '.',
    [string]$OutputDirectory = ''
)

$ErrorActionPreference = 'Stop'
$ProjectPath = (Resolve-Path -LiteralPath $ProjectPath).Path
if (-not (Test-Path -LiteralPath (Join-Path $ProjectPath 'artisan'))) {
    throw 'File artisan tidak ditemukan. Jalankan dari root proyek Laravel atau berikan -ProjectPath.'
}
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    throw 'PHP tidak ditemukan di PATH. Jalankan dari terminal proyek yang dapat memakai php artisan.'
}
if ([string]::IsNullOrWhiteSpace($OutputDirectory)) {
    $tempBase = [IO.Path]::GetTempPath()
    $OutputDirectory = Join-Path $tempBase ('crm-a748-audit-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
}
New-Item -ItemType Directory -Force -Path $OutputDirectory | Out-Null
$OutputDirectory = (Resolve-Path -LiteralPath $OutputDirectory).Path
$keyword = 'activit|to[ _-]?do|work.?task|task|collaborat|contribut|viewer|assignment|assignee|submission|comment|progress|team|organization|member|calendar'
$securityPattern = 'authorize\s*\(|Gate::|@can\b|@cannot\b|->can\s*\(|middleware\s*\(|abort\s*\(\s*(403|404)|Storage::(download|url|disk)|response\s*\(\s*\)\s*->\s*(download|file)|/storage/|asset\s*\(\s*["'']/?storage|signedRoute|temporaryUrl'
$scanRoots = @('app/Http/Controllers','app/Policies','app/Services','app/Models','routes','bootstrap','resources/views','database/migrations')
$allFiles = New-Object System.Collections.Generic.List[object]
foreach ($root in $scanRoots) {
    $full = Join-Path $ProjectPath $root
    if (Test-Path -LiteralPath $full) {
        Get-ChildItem -LiteralPath $full -Recurse -File -ErrorAction SilentlyContinue |
            Where-Object { $_.Name -match '\.(php|blade\.php)$' } |
            ForEach-Object { [void]$allFiles.Add($_) }
    }
}
$relevantFiles = @($allFiles | Where-Object { $_.Name -match $keyword -or $_.FullName -match '[\\/](routes|Policies)[\\/]' -or $_.Name -eq 'app.php' })
$inventory = @($relevantFiles | ForEach-Object {
    [PSCustomObject]@{RelativePath=$_.FullName.Substring($ProjectPath.Length).TrimStart('\','/');Bytes=$_.Length}
})
$inventory | Export-Csv -Path (Join-Path $OutputDirectory 'relevant-files.csv') -Encoding UTF8 -NoTypeInformation
$signals = New-Object System.Collections.Generic.List[object]
foreach ($file in $relevantFiles) {
    $matches = @(Select-String -LiteralPath $file.FullName -Pattern $securityPattern -ErrorAction SilentlyContinue)
    foreach ($m in $matches) {
        [void]$signals.Add([PSCustomObject]@{
            RelativePath = $file.FullName.Substring($ProjectPath.Length).TrimStart('\','/')
            Line = $m.LineNumber
            Snippet = $m.Line.Trim().Substring(0,[Math]::Min(240,$m.Line.Trim().Length))
        })
    }
}
$signals | Export-Csv -Path (Join-Path $OutputDirectory 'authorization-storage-signals.csv') -Encoding UTF8 -NoTypeInformation
$routeCount = 0
$routeError = ''
Push-Location -LiteralPath $ProjectPath
try {
    try {
        $raw = (& php artisan route:list --json 2>&1 | Out-String)
        if ($LASTEXITCODE -ne 0) { throw ('php artisan route:list exited with ' + $LASTEXITCODE + ': ' + $raw) }
        $routes = @($raw | ConvertFrom-Json)
        $filtered = @($routes | Where-Object {
            ([string]$_.uri + ' ' + [string]$_.name + ' ' + [string]$_.action) -match $keyword
        })
        $routeCount = $filtered.Count
        $routeRows = @($filtered | ForEach-Object {
            $mw = if ($_.middleware -is [array]) { $_.middleware -join ', ' } else { [string]$_.middleware }
            [PSCustomObject]@{
                Method = [string]$_.method
                URI = [string]$_.uri
                Name = [string]$_.name
                Action = [string]$_.action
                Middleware = $mw
                StaticReview = if ($mw -match 'auth|permission|can:|verified') {'MIDDLEWARE SIGNAL PRESENT'} else {'REVIEW: Check controller/policy/auth inheritance'}
            }
        })
        $routeRows | Export-Csv -Path (Join-Path $OutputDirectory 'collaboration-routes.csv') -NoTypeInformation -Encoding UTF8
        $filtered | ConvertTo-Json -Depth 10 | Set-Content -Path (Join-Path $OutputDirectory 'collaboration-routes.json') -Encoding UTF8
    } catch {
        $routeError = $_.Exception.Message
        Set-Content -Path (Join-Path $OutputDirectory 'route-list-error.txt') -Value $routeError -Encoding UTF8
        Write-Warning ('Tidak bisa mengekspor routes: ' + $routeError)
    }
} finally {
    Pop-Location
}
$summary = @"
# A7.4.8 read-only audit

- Waktu: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss zzz')
- Folder proyek: $ProjectPath
- Folder laporan: $OutputDirectory
- File terkait: $($inventory.Count)
- Indikasi authorization/storage: $($signals.Count)
- Route terkait: $routeCount
- Kesalahan route:list: $routeError

## Cara membaca
- relevant-files.csv adalah inventaris, bukan bukti modul lengkap.
- collaboration-routes.csv adalah route terdaftar, bukan bukti akses sudah benar.
- StaticReview = REVIEW berarti perlu cek policy/controller; bukan otomatis celah keamanan.
- authorization-storage-signals.csv hanya menampilkan lokasi baris yang patut diperiksa; adanya authorize() bukan bukti semua cabang kode aman.
- Matriks pengujian manual dan feature tests ada di TEST_MATRIX_A748.md.

## Batasan
Tidak menjalankan permintaan HTTP sebagai role tertentu, tidak membuka berkas submission,
tidak mengecek tabel database, dan tidak menjamin bebas IDOR/data leakage.
"@
Set-Content -Path (Join-Path $OutputDirectory 'audit-summary.md') -Value $summary -Encoding UTF8
Write-Host ('Audit selesai. Output: ' + $OutputDirectory)
Write-Host ('Files: ' + $inventory.Count + ' | signals: ' + $signals.Count + ' | routes: ' + $routeCount)
