<?php

/** A7.7.1 static verification; no Laravel boot or database access. */
declare(strict_types=1);

$root = dirname(__DIR__);
$files = [
    'bootstrap/app.php',
    'app/Http/Middleware/EnsureCrmAuthenticated.php',
    'app/Http/Middleware/PreventCrmResponseCaching.php',
    'tests/Unit/CrmResponseCachePrivacyTest.php',
];
$passed = 0;
$failed = 0;
$check = static function (bool $ok, string $label) use (&$passed, &$failed): void {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
    $ok ? ++$passed : ++$failed;
};
foreach ($files as $file) {
    $full = $root . '/' . $file;
    $exists = is_file($full);
    $check($exists, 'File ' . $file);
    if (!$exists) continue;
    $out = [];
    $exit = 1;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($full) . ' 2>&1', $out, $exit);
    $check($exit === 0, 'PHP syntax ' . $file);
}
$boot = is_file($root . '/bootstrap/app.php')
    ? (string) file_get_contents($root . '/bootstrap/app.php') : '';
$source = is_file($root . '/app/Http/Middleware/PreventCrmResponseCaching.php')
    ? (string) file_get_contents($root . '/app/Http/Middleware/PreventCrmResponseCaching.php') : '';
$required = [
    'Auth middleware retained' => strpos($boot, 'EnsureCrmAuthenticated::class') !== false,
    'No-store middleware registered' => strpos($boot, 'PreventCrmResponseCaching::class') !== false,
    'Cache middleware wraps auth responses' => strpos($boot, 'EnsureCrmAuthenticated::class') !== false
        && strpos($boot, 'PreventCrmResponseCaching::class') !== false
        && strpos($boot, 'PreventCrmResponseCaching::class') < strpos($boot, 'EnsureCrmAuthenticated::class'),
    'Trust proxy settings unchanged from checked source' => strpos($boot, "trustProxies(at: '*')") !== false,
    'No-store set on outgoing response' => strpos($source, "headers->set('Cache-Control', 'private, no-store") !== false,
    'Pragma no-cache set' => strpos($source, "headers->set('Pragma', 'no-cache')") !== false,
    'Content-type sniffing disabled' => strpos($source, "headers->set('X-Content-Type-Options', 'nosniff')") !== false,
    'Next request handler executed' => strpos($source, '$next($request)') !== false,
];
foreach ($required as $label => $ok) $check($ok, $label);
echo sprintf('A7.7.1 static: %d/%d PASS; %d FAIL. No DB used.%s',
    $passed, $passed + $failed, $failed, PHP_EOL);
exit($failed === 0 ? 0 : 1);
