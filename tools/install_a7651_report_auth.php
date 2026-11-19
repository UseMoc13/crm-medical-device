<?php
/** A7.6.5.1 guarded, idempotent, surgical Reports auth patch. */
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
$file = $root.'/app/Http/Controllers/ReportController.php';
$tests = [
    'tests/Unit/CrmReportAuthorizationSourceTest.php',
    'tests/Feature/CrmReportAuthorizationHttpTest.php',
];
function a7651_lf(string $x): string { return str_replace(["\r\n", "\r"], "\n", $x); }
function a7651_blob(string $x): string {
    $x = a7651_lf($x);
    return sha1('blob '.strlen($x)."\0".$x);
}
function a7651_replace_once(string $src, string $from, string $to): string {
    if (substr_count($src, $from) !== 1) throw new RuntimeException('Unexpected controller layout; refusing patch: '.$from);
    return str_replace($from, $to, $src);
}
try {
    if (!is_file($file)) throw new RuntimeException('Missing canonical controller ReportController.php (check A7.6.4 rename).');
    $original = file_get_contents($file);
    $norm = a7651_lf($original);
    $already = str_contains($norm, 'private function assertReportsView(): void')
        && substr_count($norm, '$this->assertReportsView();') === 2
        && str_contains($norm, "->canView(Auth::guard('web')->user(), 'reports')");
    if (!$already && a7651_blob($original) !== '12535064b195f29114325df103132b0d9915a768') {
        throw new RuntimeException('ReportController baseline differs from approved Big-Changes version; refusing to overwrite local changes.');
    }
    if ($already) {
        $updatedNorm = $norm;
    } else {
        $updatedNorm = a7651_replace_once($norm,
            "use App\\Services\\CrmReportingDataScope;\n",
            "use App\\Services\\CrmNavigationAccess;\nuse App\\Services\\CrmReportingDataScope;\n");
        $updatedNorm = a7651_replace_once($updatedNorm,
            "use Illuminate\\Support\\Facades\\DB;\n",
            "use Illuminate\\Support\\Facades\\Auth;\nuse Illuminate\\Support\\Facades\\DB;\n");
        foreach ([
            "public function index(Request \$request): View\n    {",
            "public function exportInvoices(Request \$request): StreamedResponse\n    {",
        ] as $needle) {
            $updatedNorm = a7651_replace_once($updatedNorm, $needle, $needle."\n        \$this->assertReportsView();\n");
        }
        $marker = "    /* =====================================================\n       HELPERS\n    ===================================================== */";
        $method = <<<'PHP'
    /** Both web and CSV entrypoints must pass the same reporting permission check. */
    private function assertReportsView(): void
    {
        abort_unless(
            app(CrmNavigationAccess::class)->canView(Auth::guard('web')->user(), 'reports'),
            403
        );
    }

PHP;
        $updatedNorm = a7651_replace_once($updatedNorm, $marker, $method.$marker);
    }
    $writeController = !$already;
    $pendingTests = [];
    foreach ($tests as $rel) {
        $dest = $root.'/'.$rel;
        $payload = __DIR__.'/a7651_payload/'.$rel;
        if (!is_file($payload)) throw new RuntimeException('Missing ZIP payload '.$rel);
        if (is_file($dest)) {
            if (hash_file('sha256', $dest) !== hash_file('sha256', $payload))
                throw new RuntimeException('Conflicting existing local test '.$rel.'; refusing overwrite.');
        } else {
            $pendingTests[] = [$rel, $dest, $payload];
        }
    }
    if (!$writeController && !$pendingTests) { echo "[PASS] A7.6.5.1 already installed; no changes.\n"; exit(0); }
    if ($writeController) echo '[CHECK] surgical patch: app/Http/Controllers/ReportController.php'.PHP_EOL;
    foreach ($pendingTests as [$rel]) echo '[CHECK] add: '.$rel.PHP_EOL;
    if ($dry) { echo "[PASS] Dry run; no files, Git index, or DB changed.\n"; exit(0); }
    $new = str_replace("\n", str_contains($original, "\r\n") ? "\r\n" : "\n", $updatedNorm);
    $backup = null;
    if ($writeController) {
        $backup = $root.'/tools/a7651_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
        if (!mkdir($backup, 0775, true) && !is_dir($backup)) throw new RuntimeException('Cannot create backup folder.');
        if (!copy($file, $backup.'/ReportController.php')) throw new RuntimeException('Cannot backup controller.');
    }
    $created = [];
    try {
        foreach ($pendingTests as [$rel, $dest, $payload]) {
            if (!is_dir(dirname($dest)) && !mkdir(dirname($dest), 0775, true))
                throw new RuntimeException('Cannot create test folder: '.$rel);
            if (!copy($payload, $dest)) throw new RuntimeException('Cannot add test: '.$rel);
            $created[] = $dest;
        }
        if ($writeController && file_put_contents($file, $new) === false)
            throw new RuntimeException('Could not write guarded controller.');
    } catch (Throwable $e) {
        foreach ($created as $dest) @unlink($dest);
        if ($backup !== null) @copy($backup.'/ReportController.php', $file);
        throw $e;
    }
    if ($writeController) echo '[PASS] Guarded Reports + CSV access checks installed'.PHP_EOL;
    foreach ($pendingTests as [$rel]) echo '[PASS] Added: '.$rel.PHP_EOL;
    if ($backup) echo '[PASS] Backup: '.$backup.PHP_EOL;
    echo "[PASS] A7.6.5.1 installed. No migrations, report calculations, CSV format, or DB records changed.\n";
} catch (Throwable $e) { fwrite(STDERR, '[FAIL] '.$e->getMessage().PHP_EOL); exit(1); }
