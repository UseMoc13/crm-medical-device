<?php
/** A7.5.2.2a additive installer. Run from CRM project root: php tools/install_a7522a_delivery_guard.php */
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
$payload = __DIR__ . '/a7522a_payload/';
$add = [
    'app/Services/CrmDeliveryCompletionGuard.php',
    'tests/Unit/CrmDeliveryCompletionGuardTest.php',
];
$controllerPath = 'app/Http/Controllers/DeliveryController.php';
$controller = $root . '/' . $controllerPath;
$marker = 'CrmDeliveryCompletionGuard::class';
$fail = static function (string $why): never {
    fwrite(STDERR, '[FAIL] ' . $why . PHP_EOL . 'No database changes were performed.' . PHP_EOL);
    exit(1);
};
if (! is_file($controller)) $fail('DeliveryController.php is missing.');
$original = file_get_contents($controller);
if ($original === false) $fail('Cannot read DeliveryController.php.');
$start = strpos($original, 'public function changeStatus(');
$end = $start === false ? false : strpos($original, 'private function dispatchAllocatedUnits(', $start);
if ($start === false || $end === false || $end <= $start) $fail('Unknown DeliveryController status workflow: no changes applied.');
$section = substr($original, $start, $end - $start);
$requires = [
    'DB::transaction(function () use ($delivery, $validated)',
    'lockForUpdate()->firstOrFail()',
    '$this->dispatchAllocatedUnits($locked)',
    '$locked->save()',
];
foreach ($requires as $needle) {
    if (! str_contains($section, $needle)) $fail('Delivery status workflow differs at ' . $needle . '; refusing unsafe modification.');
}
$already = str_contains($section, $marker);
$modified = $original;
if (! $already) {
    $rx = <<<'REGEX'
/(?m)^([ \t]*)if \(\$validated\['status'\] === 'delivered' && empty\(\$locked->delivery_date\)\) \{/
REGEX;
    if (preg_match_all($rx, $section, $matches, PREG_SET_ORDER) !== 1) {
        $fail('Expected exactly one status=delivered date branch, got a different local version.');
    }
    $indent = $matches[0][1];
    $newline = str_contains($original, "\r\n") ? "\r\n" : "\n";
    $guard = $indent . '// A7.5.2.2a: verify dispatch provenance before finalizing Delivery.' . $newline
        . $indent . "if (\$validated['status'] === 'delivered') {" . $newline
        . $indent . '    app(\\App\\Services\\CrmDeliveryCompletionGuard::class)->assertCanComplete($locked);' . $newline
        . $indent . '}' . $newline;
    $needle = $matches[0][0];
    $pos = strpos($section, $needle);
    $section = substr_replace($section, $guard, $pos, 0);
    $modified = substr_replace($original, $section, $start, $end - $start);
}
if (substr_count($modified, $marker) !== 1) $fail('Expected guard to appear exactly once.');
$pending = [];
foreach ($add as $path) {
    $source = $payload . $path;
    $target = $root . '/' . $path;
    if (! is_file($source)) $fail('Payload missing: ' . $path);
    if (is_file($target) && hash_file('sha256', $source) !== hash_file('sha256', $target)) {
        $fail('Existing custom file would be overwritten: ' . $path);
    }
    if (! is_file($target)) $pending[$path] = file_get_contents($source);
}
if ($modified !== $original) $pending[$controllerPath] = $modified;
if ($pending === []) {
    echo '[PASS] A7.5.2.2a already installed. Nothing changed.' . PHP_EOL;
    exit(0);
}
foreach (array_keys($pending) as $path) {
    echo ($dry ? '[CHECK] ' : '[INFO] ') . ( $path === $controllerPath ? 'patch' : 'add') . ': ' . $path . PHP_EOL;
}
if ($dry) { echo '[PASS] Dry run; no changes made.' . PHP_EOL; exit(0); }
$backup = $root . '/tools/a7522a_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
if (! mkdir($backup, 0775, true) && ! is_dir($backup)) $fail('Could not create backup directory.');
$written = [];
try {
    // Backup all existing files before modifying even one target.
    foreach (array_keys($pending) as $path) {
        $target = $root . '/' . $path;
        if (is_file($target)) {
            $save = $backup . '/' . $path;
            if (! is_dir(dirname($save)) && ! mkdir(dirname($save), 0775, true) && ! is_dir(dirname($save))) throw new RuntimeException('Cannot create backup subfolder');
            if (! copy($target, $save)) throw new RuntimeException('Cannot back up ' . $path);
        }
    }
    foreach ($pending as $path => $body) {
        $target = $root . '/' . $path;
        if (! is_dir(dirname($target)) && ! mkdir(dirname($target), 0775, true) && ! is_dir(dirname($target))) throw new RuntimeException('Cannot create directory for ' . $path);
        $tmp = tempnam(dirname($target), '.a7522a-');
        if ($tmp === false) throw new RuntimeException('Cannot stage ' . $path);
        if (file_put_contents($tmp, $body) !== strlen($body)) { @unlink($tmp); throw new RuntimeException('Cannot write ' . $path); }
        // Windows can reject rename() over an existing file. Use staged copy as fallback.
        if (! @rename($tmp, $target)) {
            if (! @copy($tmp, $target)) { @unlink($tmp); throw new RuntimeException('Cannot replace ' . $path); }
            @unlink($tmp);
        }
        $written[] = $path;
        echo '[PASS] ' . ($path === $controllerPath ? 'Patched' : 'Added') . ': ' . $path . PHP_EOL;
    }
} catch (Throwable $e) {
    foreach (array_reverse($written) as $path) {
        $save = $backup . '/' . $path;
        if (is_file($save)) copy($save, $root . '/' . $path);
        else @unlink($root . '/' . $path);
    }
    $fail('Installer rolled back: ' . $e->getMessage());
}
echo '[PASS] A7.5.2.2a installed; backup: ' . $backup . PHP_EOL;
echo 'No migrations, database writes or historical stock corrections performed.' . PHP_EOL;
