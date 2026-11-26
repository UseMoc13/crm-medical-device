<?php
/** A7.10.4 local source completeness and syntax only; NO CRM DB writes. */
declare(strict_types=1);
$root = dirname(__DIR__);
$paths = [
    'tools/a7104_repair_guard_syntax.php',
    'tools/a7104_compiler_smoke.php',
    'tools/a7104_harden_old_installer.php',
    'docs/A7104_BLADE_PARSE_FIX.md',
];
$n = 0;
foreach ($paths as $relative) {
    $path = $root.'/'.$relative;
    if (!is_file($path) || filesize($path) < 200) {
        fwrite(STDERR, '[FAIL] Missing/incomplete '.$relative.PHP_EOL);exit(1);
    }
    $n++;
    if (str_ends_with($relative, '.php')) {
        $out=[];$status=0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1', $out, $status);
        if ($status !== 0) { fwrite(STDERR, '[FAIL] PHP lint '.$relative.' '.implode(' ', $out).PHP_EOL);exit(1); }
        $n++;
    }
    echo '[PASS] '.$relative.PHP_EOL;
}
echo '[RESULT] A7104_SOURCE_READY checks='.$n.' (Blade compiler and database not executed)'.PHP_EOL;
