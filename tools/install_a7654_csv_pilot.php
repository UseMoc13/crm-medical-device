<?php
/** A7.6.5.4 guarded, idempotent opt-in CSV policy installer (no DB writes). */
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
function a7654_lf(string $s): string { return str_replace(["\r\n", "\r"], "\n", $s); }
function a7654_sha(string $s): string { return hash('sha256', a7654_lf($s)); }
function a7654_git_blob(string $s): string {
    $s = a7654_lf($s);
    return sha1('blob '.strlen($s)."\0".$s);
}
function a7654_replace_once(string $s, string $needle, string $replacement): string {
    if (substr_count($s, $needle) !== 1) throw new RuntimeException('Unexpected controller layout; declining to patch.');
    return str_replace($needle, $replacement, $s);
}
try {
    $new = [
        'config/crm_reporting.php',
        'app/Services/CrmInvoiceCsvPolicy.php',
        'tests/Unit/CrmInvoiceCsvPilotPolicyTest.php',
        'tests/Unit/CrmInvoiceCsvPilotSourceTest.php',
        'tests/Feature/CrmInvoiceCsvPilotHttpTest.php',
    ];
    $patches = [
        'app/Http/Controllers/ReportController.php' => ['git', 'fd51e2b820eff05791481ce365d33a16429bb164'],
        'app/Services/CrmFinancialRoleEntitlementAudit.php' => ['sha', '4e29369b0b3cb6a36c11e2856d51db1e770db04d4990dc47ed95a01d2ecc4c29'],
        'app/Console/Commands/AuditCrmFinancialRoleEntitlements.php' => ['sha', '2d8027267dc1c03aa9073f8103e77cdf7dc32510f04adbd95565df09ed370c49'],
    ];
    $stateFile = $root.'/tools/a7654_install_state.json';
    $state = is_file($stateFile) ? json_decode(file_get_contents($stateFile), true) : null;
    if (is_file($stateFile) && !is_array($state)) throw new RuntimeException('Invalid existing state manifest.');
    if ($state !== null) {
        foreach ($patches as $rel => $config) {
            $full = $root.'/'.$rel;
            if (!is_file($full) || !is_string($state['patched'][$rel] ?? null)
                || a7654_sha(file_get_contents($full)) !== $state['patched'][$rel]) {
                throw new RuntimeException('Installed file was modified after A7.6.5.4: '.$rel);
            }
        }
        foreach ($new as $rel) {
            $full = $root.'/'.$rel;
            $src = __DIR__.'/a7654_payload/'.$rel;
            if (!is_file($full) || !is_file($src) || hash_file('sha256', $full) !== hash_file('sha256', $src)) {
                throw new RuntimeException('Installed additive file changed: '.$rel);
            }
        }
        echo "[PASS] A7.6.5.4 already installed, all files match; no changes.\n";
        exit(0);
    }
    $originals = [];
    $updates = [];
    $existingNew = [];
    foreach ($patches as $rel => [$mode,$fingerprint]) {
        $full=$root.'/'.$rel;
        if (!is_file($full)) throw new RuntimeException('Missing required source: '.$rel);
        $raw=file_get_contents($full);
        $actual=$mode === 'git' ? a7654_git_blob($raw) : a7654_sha($raw);
        if ($actual !== $fingerprint) throw new RuntimeException('Unexpected local source; refusing overwrite: '.$rel);
        $originals[$rel]=$raw;
        if ($mode === 'git') {
            $needle = "    public function exportInvoices(Request \$request): StreamedResponse\n    {\n        \$this->assertReportsView();\n";
            $guard = <<<'GUARD'

        // A7.6.5.4: optional least-privilege pilot. OFF by default.
        if ((bool) config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false)) {
            abort_unless(app(\App\Services\CrmInvoiceCsvPolicy::class)
                ->canExport(Auth::guard('web')->user()), 403);
        }
GUARD;
            $modified = a7654_replace_once(a7654_lf($raw), $needle, $needle.$guard."\n");
        } else {
            $payload = __DIR__.'/a7654_payload/'.$rel;
            if (!is_file($payload)) throw new RuntimeException('Missing guarded role-patch payload '.$rel);
            $modified=file_get_contents($payload);
        }
        $updates[$rel]=str_replace("\n", str_contains($raw, "\r\n") ? "\r\n" : "\n", a7654_lf($modified));
    }
    foreach ($new as $rel) {
        $payload=__DIR__.'/a7654_payload/'.$rel;
        if (!is_file($payload)) throw new RuntimeException('Missing new payload '.$rel);
        if (is_file($root.'/'.$rel)) {
            if (hash_file('sha256', $root.'/'.$rel) !== hash_file('sha256', $payload))
                throw new RuntimeException('Conflicting existing new file '.$rel);
            $existingNew[$rel]=true;
        }
    }
    foreach ($patches as $rel => $unused) echo '[CHECK] patch: '.$rel.PHP_EOL;
    foreach ($new as $rel) echo '[CHECK] '.(isset($existingNew[$rel])?'retain':'add').': '.$rel.PHP_EOL;
    if ($dry) { echo "[PASS] Dry run. No files, Git index, permissions or database changed.\n"; exit(0); }
    $backup=$root.'/tools/a7654_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
    if (!mkdir($backup, 0775, true) && !is_dir($backup)) throw new RuntimeException('Cannot create backup directory.');
    foreach ($originals as $rel => $raw) {
        $dest=$backup.'/'.$rel;
        if (!is_dir(dirname($dest)) && !mkdir(dirname($dest), 0775, true)) throw new RuntimeException('Cannot create backup subfolder.');
        if (file_put_contents($dest,$raw) === false) throw new RuntimeException('Could not backup '.$rel);
    }
    $created=[];
    try {
        foreach ($new as $rel) {
            if (isset($existingNew[$rel])) continue;
            $dest=$root.'/'.$rel;
            if (!is_dir(dirname($dest)) && !mkdir(dirname($dest), 0775, true)) throw new RuntimeException('Cannot create directory for '.$rel);
            if (!copy(__DIR__.'/a7654_payload/'.$rel,$dest)) throw new RuntimeException('Failed creating '.$rel);
            $created[]=$dest;
        }
        foreach ($updates as $rel => $content) {
            if (file_put_contents($root.'/'.$rel,$content) === false) throw new RuntimeException('Failed writing '.$rel);
        }
        $manifest=['stage'=>'A7.6.5.4','patched'=>[]];
        foreach ($updates as $rel => $content) $manifest['patched'][$rel]=a7654_sha($content);
        if (file_put_contents($stateFile,json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n") === false) {
            throw new RuntimeException('Failed writing installer state.');
        }
    } catch (Throwable $e) {
        foreach ($originals as $rel => $raw) @file_put_contents($root.'/'.$rel,$raw);
        foreach ($created as $path) @unlink($path);
        @unlink($stateFile);
        throw $e;
    }
    echo "[PASS] A7.6.5.4 installed. All modified sources backed up: {$backup}\n";
    echo "[INFO] CSV gate defaults OFF. No permissions, records, migrations or CSV output changed.\n";
    echo "[INFO] Pilot requires explicit staging opt-in and review before production.\n";
} catch (Throwable $e) { fwrite(STDERR, '[FAIL] '.$e->getMessage().PHP_EOL); exit(1); }
