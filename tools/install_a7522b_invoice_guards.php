<?php
/**
 * A7.5.2.2b - guarded, additive Invoice row-lock patch.
 * Replaces only verified InvoiceController methods; preserves all other edits.
 * No database changes and no migrations.
 */
$root = dirname(__DIR__);
$controller = $root . '/app/Http/Controllers/InvoiceController.php';
$payload = __DIR__ . '/a7522b_payload';
$dryRun = in_array('--dry-run', $argv, true);
$oldHashes = json_decode('{"update":"a121531da3f1cddd36975e4aac64c8714c523374cf8a6bcb0231f37784cb2223","updateStatus":"787a03a4f97d4b04996dcdcd2e8886858f03fde6b946e0a9bc1854927b6d3d87","destroy":"69777577967308c90a90fb087e9d02e39732b1b1a2a45d221491a65c73ebdba6"}', true, 512, JSON_THROW_ON_ERROR);
$newHashes = json_decode('{"update":"7a7d29c803bb1ef06917b3934518b2956be7850077884d72b92e198b10b4b9f0","updateStatus":"fce1c5626aa15e71ad60533ab1e961eb93d1dc72c54a3b3277663bc3acf6a396","destroy":"a0925300b9383b44a898b0c58f8d6dec11950642f7955edcea57b23a364894b7"}', true, 512, JSON_THROW_ON_ERROR);
$additions = [
    'app/Services/CrmInvoiceRowGuard.php',
    'tests/Unit/CrmInvoiceRowGuardTest.php',
];
function a7522bMethod(string $source, string $method): ?string
{
    $start = strpos($source, '    public function ' . $method . '(');
    if ($start === false) return null;
    $end = strpos($source, "\n    }\n", $start);
    if ($end === false) return null;
    return substr($source, $start, $end + strlen("\n    }\n") - $start);
}
function a7522bFail(string $reason): never
{
    fwrite(STDERR, '[FAIL] ' . $reason . PHP_EOL);
    exit(1);
}
if (!is_file($controller)) a7522bFail('Missing InvoiceController.php. Run from CRM project root.');
$original = file_get_contents($controller);
if ($original === false) a7522bFail('Cannot read InvoiceController.php.');
$crlf = str_contains($original, "\r\n");
$source = str_replace(["\r\n", "\r"], "\n", $original);
$updated = $source;
$todo = [];
foreach ($oldHashes as $method => $expectedHash) {
    $actual = a7522bMethod($updated, $method);
    $snippetFile = $payload . '/snippets/' . $method . '.txt';
    $snippet = is_file($snippetFile) ? file_get_contents($snippetFile) : false;
    if ($actual === null || $snippet === false || !hash_equals($newHashes[$method], hash('sha256', $snippet))) {
        a7522bFail('Missing or corrupt method/payload: ' . $method);
    }
    $hash = hash('sha256', $actual);
    if (hash_equals($newHashes[$method], $hash)) {
        echo '[SKIP] Already patched: InvoiceController::' . $method . PHP_EOL;
        continue;
    }
    if (!hash_equals($expectedHash, $hash)) {
        a7522bFail('Unrecognized local InvoiceController::' . $method . '(). No files changed. Review local changes before patching.');
    }
    $updated = str_replace($actual, $snippet, $updated, $count);
    if ($count !== 1) a7522bFail('Ambiguous method match: ' . $method);
    $todo[] = 'controller:' . $method;
    echo '[CHECK] patch: InvoiceController::' . $method . PHP_EOL;
}
foreach ($additions as $relative) {
    $file = $root . '/' . $relative;
    $expected = $payload . '/' . $relative;
    if (!is_file($expected)) a7522bFail('Missing payload: ' . $relative);
    if (is_file($file)) {
        if (!hash_equals(hash_file('sha256', $expected), hash_file('sha256', $file))) {
            a7522bFail('Unrecognized existing file: ' . $relative . '. No files changed.');
        }
        echo '[SKIP] Already installed: ' . $relative . PHP_EOL;
    } else {
        echo '[CHECK] add: ' . $relative . PHP_EOL;
        $todo[] = $relative;
    }
}
if ($dryRun) {
    echo '[PASS] Dry run; no files or database changed.' . PHP_EOL;
    exit(0);
}
if (!$todo) {
    echo '[PASS] Already installed. No changes.' . PHP_EOL;
    exit(0);
}
$backup = $root . '/tools/a7522b_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
if (!is_dir($backup) && !mkdir($backup, 0775, true)) a7522bFail('Cannot create backup directory.');
$modified = [];
try {
    if (in_array('controller:update', $todo, true) || in_array('controller:updateStatus', $todo, true) || in_array('controller:destroy', $todo, true)) {
        $backupPath = $backup . '/InvoiceController.php';
        if (file_put_contents($backupPath, $original, LOCK_EX) === false) throw new RuntimeException('Could not back up InvoiceController.php.');
        $write = $crlf ? str_replace("\n", "\r\n", $updated) : $updated;
        if (file_put_contents($controller, $write, LOCK_EX) === false) throw new RuntimeException('Could not update InvoiceController.php.');
        $modified[] = $controller;
        echo '[PASS] Patched: app/Http/Controllers/InvoiceController.php' . PHP_EOL;
    }
    foreach ($additions as $relative) {
        if (!in_array($relative, $todo, true)) continue;
        $path = $root . '/' . $relative;
        if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true)) throw new RuntimeException('Cannot create directory: ' . dirname($path));
        if (!copy($payload . '/' . $relative, $path)) throw new RuntimeException('Cannot install: ' . $relative);
        $modified[] = $path;
        echo '[PASS] Added: ' . $relative . PHP_EOL;
    }
} catch (Throwable $e) {
    // Remove new files and restore the exact original controller bytes.
    foreach ($modified as $path) {
        if ($path !== $controller && is_file($path)) unlink($path);
    }
    if (is_file($backup . '/InvoiceController.php')) {
        file_put_contents($controller, file_get_contents($backup . '/InvoiceController.php'), LOCK_EX);
    }
    a7522bFail('Install interrupted and original controller restored: ' . $e->getMessage());
}
echo '[PASS] A7.5.2.2b installed. Backup: ' . $backup . PHP_EOL;
echo 'No migrations, historical status conversions, data writes or UI changes were performed.' . PHP_EOL;
