# A7.8.1 -- Safe local static/isolated tests; no database audit unless opted in separately.
$ErrorActionPreference = 'Stop'
function Run-PHP([string]$File) {
    & php $File
    if ($LASTEXITCODE -ne 0) { throw "Failed: php $File" }
}
Run-PHP 'tools/a781_quick_check.php'
Run-PHP 'tools/a781_manifest_smoke.php'
& php artisan test --filter=CrmIntegrationUatManifestTest
if ($LASTEXITCODE -ne 0) { throw 'PHPUnit manifest test failed' }
Write-Host '[PASS] A7.8.1 static + manifest + unit tests. Route audit and DB audit require explicit commands.'
