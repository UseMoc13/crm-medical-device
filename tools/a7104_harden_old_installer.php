<?php
/** A7104 optional safety hardening for the legacy A7103 Blade injector. */
declare(strict_types=1);
$root = dirname(__DIR__);
$path = $root.'/tools/a7103_crud_ui_installer.php';
$mode = $argv[1] ?? '--check';
if ($argc !== 2 || !in_array($mode, ['--check', '--apply'], true)) {
    fwrite(STDERR, "Usage: php tools/a7104_harden_old_installer.php --check|--apply\n");
    exit(2);
}
if (!is_file($path)) { fwrite(STDERR, "[FAIL] Original A7103 installer not found.\n"); exit(1); }
$source = (string)file_get_contents($path);
$before = '$inserts[] = [$end, "\n@endif{{-- /A7103 --}}"];';
$after = '$inserts[] = [$end, "\n@endif{{-- /A7103 --}}\n"];';
$anchor = '        // Save every original before touching any view.';
$marker = 'A7104: compiled Blade PHP preflight';
$code = <<<'PHP'
        // A7104: compiled Blade PHP preflight BEFORE touching any files.
        // Prevent the silent templating parse errors the first A7103 injector allowed.
        $app = require $root.'/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $blade = app('blade.compiler');
        foreach ($changed as $relative => $candidate) {
            $compiled = $blade->compileString($candidate['new']);
            $temp = tempnam(sys_get_temp_dir(), 'crm_a7104_installer_');
            if ($temp === false) throw new RuntimeException('Cannot create temp lint file.');
            try {
                if (file_put_contents($temp, $compiled, LOCK_EX) === false) {
                    throw new RuntimeException('Cannot write temp lint file.');
                }
                $out = []; $rc = 0;
                exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1', $out, $rc);
                if ($rc !== 0) {
                    throw new RuntimeException('Blade compile PHP invalid; no views modified: '.$relative.' | '.implode(' ', $out));
                }
            } finally {
                @unlink($temp);
            }
        }
PHP;
if (str_contains($source, $marker)) {
    echo '[RESULT] A7104_OLD_INSTALLER_ALREADY_HARDENED; no files modified'.PHP_EOL;
    exit(0);
}
if (substr_count($source, $before) !== 1 || substr_count($source, $anchor) !== 1) {
    fwrite(STDERR, '[FAIL] A7103 installer version differs; no file touched. Do not rerun its --apply.'.PHP_EOL);
    exit(1);
}
$updated = str_replace($before, $after, $source);
$updated = str_replace($anchor, $code."\n".$anchor, $updated);
$temp = tempnam(sys_get_temp_dir(), 'crm_a7104_harden_');
if (!$temp) { fwrite(STDERR, "[FAIL] Cannot allocate temp file.\n"); exit(1); }
file_put_contents($temp, $updated);
$out = []; $rc = 0;
exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1', $out, $rc);
@unlink($temp);
if ($rc !== 0) { fwrite(STDERR, '[FAIL] Installer source syntax: '.implode(' ',$out).PHP_EOL); exit(1); }
if ($mode === '--check') {
    echo '[RESULT] A7104_OLD_INSTALLER_HARDENING_COMPATIBLE; no files modified'.PHP_EOL;
    exit(0);
}
$dir = $root.'/storage/app/private/a7104_installer_safety_backups';
if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
    fwrite(STDERR, "[FAIL] Cannot create backup directory.\n"); exit(1);
}
$backup = $dir.'/a7103_crud_ui_installer.'.date('Ymd-His').'-'.bin2hex(random_bytes(4)).'.php.bak';
if (file_put_contents($backup, $source, LOCK_EX) === false) {
    fwrite(STDERR, "[FAIL] Cannot backup original installer.\n"); exit(1);
}
if (file_get_contents($path) !== $source || file_put_contents($path, $updated, LOCK_EX) === false) {
    fwrite(STDERR, "[FAIL] Original changed concurrently or installer write failed. Check backup.\n"); exit(1);
}
echo '[BACKUP] '.$backup.PHP_EOL;
echo '[RESULT] A7104_OLD_INSTALLER_HARDENED; no Blade views modified'.PHP_EOL;
