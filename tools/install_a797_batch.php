<?php
/**
 * A7.9.7 multi-module assignment installer.
 * Run from project root: php tools/install_a797_batch.php --check / --apply
 * Preflight is all-or-nothing. Existing files are backed up in storage/app/private.
 * This script never connects to PostgreSQL or runs migrations.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$mode = $argv[1] ?? '--check';
if (!in_array($mode, ['--check', '--apply'], true)) {
    fwrite(STDERR, "Usage: php tools/install_a797_batch.php --check|--apply\n");
    exit(2);
}
$spec = json_decode((string) file_get_contents(__DIR__ . '/a797_patches.json'), true, 512, JSON_THROW_ON_ERROR);
if (($spec['version'] ?? '') !== 'A797_MULTI_MODULE_ASSIGNMENT') {
    fwrite(STDERR, "[FAIL] Unexpected patch manifest.\n");
    exit(2);
}
$staged = [];
$originals = [];
$styles = [];
$stats = ['pending' => 0, 'already' => 0];
$errors = [];
foreach ($spec['patches'] as $entry) {
    $relative = (string) $entry['path'];
    $file = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!array_key_exists($relative, $staged)) {
        if (!is_file($file)) {
            $errors[] = "$relative: file not found";
            continue;
        }
        $raw = (string) file_get_contents($file);
        $originals[$relative] = $raw;
        $styles[$relative] = str_contains($raw, "\r\n") ? "\r\n" : "\n";
        $staged[$relative] = str_replace(["\r\n", "\r"], "\n", $raw);
    }
    $source = $staged[$relative];
    $old = (string) $entry['old'];
    $new = (string) $entry['new'];
    $name = (string) $entry['name'];
    $expected = (int) $entry['count'];
    $marker = 'A797:' . $name;
    $markerCount = substr_count($source, $marker);
    if ($entry['kind'] === 'literal') {
        $actual = substr_count($source, $old);
        if ($markerCount === $expected) {
            ++$stats['already'];
            echo "[INSTALLED] $relative :: $name\n";
        } elseif ($actual === $expected && $markerCount === 0) {
            $staged[$relative] = str_replace($old, $new, $source);
            ++$stats['pending'];
            echo "[READY] $relative :: $name ($actual match)\n";
        } else {
            $errors[] = "$relative :: $name expected $expected old, 0 installed; found $actual old, $markerCount installed";
        }
        continue;
    }
    $pattern = '~' . str_replace('~', '\\~', $old) . '~ms';
    $actual = preg_match_all($pattern, $source);
    if ($actual === false) {
        $errors[] = "$relative :: $name invalid regex: " . preg_last_error_msg();
        continue;
    }
    if ($markerCount === $expected) {
        ++$stats['already'];
        echo "[INSTALLED] $relative :: $name\n";
    } elseif ($actual === $expected && $markerCount === 0) {
        $replaced = preg_replace_callback($pattern, static function (array $match) use ($new): string {
            return preg_replace_callback('/\{\{([a-zA-Z0-9_]+)\}\}/', static function (array $token) use ($match): string {
                return (string) ($match[$token[1]] ?? '');
            }, $new);
        }, $source, -1, $count);
        if ($replaced === null || $count !== $expected) {
            $errors[] = "$relative :: $name replacement failed";
            continue;
        }
        $staged[$relative] = $replaced;
        ++$stats['pending'];
        echo "[READY] $relative :: $name ($count match)\n";
    } else {
        $errors[] = "$relative :: $name expected $expected old, 0 installed; found $actual old, $markerCount installed";
    }
}
if ($errors !== []) {
    foreach ($errors as $error) fwrite(STDERR, "[FAIL] $error\n");
    fwrite(STDERR, "[RESULT] A797_PREFLIGHT_FAILED. No files changed.\n");
    exit(1);
}
$changed = [];
foreach ($staged as $rel => $normalized) {
    $output = str_replace("\n", $styles[$rel], $normalized);
    if ($output !== $originals[$rel]) $changed[$rel] = $output;
}
if ($mode === '--check') {
    echo "[RESULT] A797_PATCH_COMPATIBLE; changes=" . count($changed)
        . "; ready_steps={$stats['pending']}; installed_steps={$stats['already']}; no files modified\n";
    exit(0);
}
if ($changed === []) {
    echo "[RESULT] A797_ALREADY_INSTALLED (no files changed)\n";
    exit(0);
}
// Validate staged PHP syntax BEFORE touching the source tree.
foreach ($changed as $rel => $contents) {
    if (!str_ends_with($rel, '.php') || str_ends_with($rel, '.blade.php')) continue;
    $temp = tempnam(sys_get_temp_dir(), 'a797_php_');
    file_put_contents($temp, $contents);
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($temp) . ' 2>&1';
    exec($command, $lines, $exit);
    @unlink($temp);
    if ($exit !== 0) {
        fwrite(STDERR, "[FAIL] PHP syntax $rel: " . implode(' ', $lines) . "\n[RESULT] No files changed.\n");
        exit(1);
    }
}
$backupDir = $root . '/storage/app/private/a797_assignment_install_backups/'
    . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
foreach ($changed as $rel => $contents) {
    $destination = $backupDir . '/' . $rel;
    if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0770, true) && !is_dir(dirname($destination))) {
        fwrite(STDERR, "[FAIL] Cannot create backup directory. No files changed.\n");
        exit(1);
    }
    if (file_put_contents($destination, $originals[$rel]) === false) {
        fwrite(STDERR, "[FAIL] Cannot back up $rel. No files changed.\n");
        exit(1);
    }
}
$written = [];
try {
    foreach ($changed as $rel => $contents) {
        $target = $root . '/' . $rel;
        $temp = $target . '.a797-temp-' . bin2hex(random_bytes(4));
        if (file_put_contents($temp, $contents) === false) {
            throw new RuntimeException('Cannot stage ' . $rel);
        }
        if (!rename($temp, $target)) {
            @unlink($temp);
            throw new RuntimeException('Cannot replace ' . $rel);
        }
        $written[] = $rel;
        echo "[PATCH] $rel\n";
    }
} catch (Throwable $e) {
    foreach ($written as $rel) file_put_contents($root . '/' . $rel, $originals[$rel]);
    fwrite(STDERR, "[FAIL] " . $e->getMessage() . ". Previous files restored.\n");
    exit(1);
}
echo "[BACKUP] $backupDir\n[RESULT] A797_MULTI_MODULE_INTEGRATED\n";
