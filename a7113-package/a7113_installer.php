<?php

declare(strict_types=1);

/** A7.11.3 Sidebar Heading Visibility. No production DB operations. */
$root = getcwd();
$base = __DIR__;
if (!is_file($root . '/artisan') || !is_file($root . '/resources/views/components/sidebar.blade.php')) {
    fwrite(STDERR, "[ERROR] Run from the Laravel project root.\n");
    exit(2);
}
$files = [
    'resources/views/components/sidebar.blade.php' => [
        'payload' => 'payload/sidebar.blade.php',
        'before' => 'dac1815ac8fda103c99db01d83956222fadcbd39c1bd829d0f2b979197534dcf',
        'after'  => '1de56e28aacf7d4239ca7e90b710aee63ac409e6df571047f9a6b417cc1e4b0d',
    ],
    'tools/a7113_sidebar_check.php' => [
        'payload' => 'payload/a7113_sidebar_check.php',
        'before' => null,
        'after' => '5889769f45fdfcaa76cf28682537f83fb0f22bde297c1b7bc6f1478fe38c4ad8',
    ],
];
foreach ($files as $rel => $spec) {
    $input = $base . '/' . $spec['payload'];
    if (!is_file($input) || hash_file('sha256', $input) !== $spec['after']) {
        fwrite(STDERR, "[ERROR] Package payload hash mismatch: {$rel}\n");
        exit(2);
    }
}
$argv = $_SERVER['argv'];
$mode = $argv[1] ?? '--check';
$backupRoot = $root . '/.a7113_backups';

function inspectState(string $root, array $files): array
{
    $states = [];
    foreach ($files as $rel => $spec) {
        $target = $root . '/' . $rel;
        if (is_link($target)) {
            throw new RuntimeException("Refusing symlink target {$rel}");
        }
        $hash = is_file($target) ? hash_file('sha256', $target) : null;
        if ($hash === $spec['after']) {
            $state = 'ALREADY';
        } elseif ($hash === $spec['before']) {
            $state = 'PENDING';
        } else {
            throw new RuntimeException("Unexpected file state {$rel}; hash=" . ($hash ?? 'missing'));
        }
        $states[$rel] = $state;
    }
    return $states;
}

try {
    if ($mode === '--rollback') {
        $id = $argv[2] ?? '';
        if (!preg_match('/^a7113_[0-9]{8}_[0-9]{6}_[a-f0-9]{8}$/D', $id)) {
            throw new RuntimeException('Provide the exact BACKUP_ID shown by --apply');
        }
        $dir = $backupRoot . '/' . $id;
        $manifest = $dir . '/manifest.json';
        if (!is_file($manifest)) {
            throw new RuntimeException('Backup manifest missing');
        }
        $entries = json_decode((string)file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
        if (($entries['version'] ?? '') !== 'A7113' || !is_array($entries['files'] ?? null) || count($entries['files']) < 1 || count($entries['files']) > count($files)) {
            throw new RuntimeException('Invalid backup manifest');
        }
        foreach ($entries['files'] as $rel => $info) {
            if (!isset($files[$rel]) || ($info['after'] ?? '') !== $files[$rel]['after']) {
                throw new RuntimeException('Backup target manifest mismatch');
            }
            $target = $root . '/' . $rel;
            if (!is_file($target) || is_link($target) || hash_file('sha256', $target) !== $files[$rel]['after']) {
                throw new RuntimeException("Refusing to overwrite modified file during rollback {$rel}");
            }
            if (($info['existed'] ?? false)) {
                $saved = $dir . '/' . $rel;
                if (!is_file($saved) || hash_file('sha256', $saved) !== $files[$rel]['before']) {
                    throw new RuntimeException("Backup invalid {$rel}");
                }
            }
        }
        foreach ($entries['files'] as $rel => $info) {
            $target = $root . '/' . $rel;
            if ($info['existed']) {
                if (!copy($dir . '/' . $rel, $target)) {
                    throw new RuntimeException("Cannot restore {$rel}");
                }
            } else {
                if (!unlink($target)) {
                    throw new RuntimeException("Cannot remove {$rel}");
                }
            }
            echo "[RESTORED] {$rel}\n";
        }
        echo "[RESULT] A7113_ROLLBACK_COMPLETE backup={$id}\n";
        exit(0);
    }
    if ($mode !== '--check' && $mode !== '--apply') {
        throw new RuntimeException('Usage: php a7113_installer.php --check | --apply | --rollback BACKUP_ID');
    }
    $states = inspectState($root, $files);
    $pending = array_keys(array_filter($states, static fn ($s) => $s === 'PENDING'));
    foreach ($states as $rel => $state) {
        echo "[{$state}] {$rel}\n";
    }
    if ($mode === '--check') {
        echo '[RESULT] ' . (count($pending) ? 'A7113_CHECK_READY' : 'A7113_ALREADY_APPLIED') . ' pending=' . count($pending) . " (read-only)\n";
        exit(0);
    }
    if (!$pending) {
        echo "[RESULT] A7113_ALREADY_APPLIED changed=0\n";
        exit(0);
    }
    // Run read-only lint on the supplied standalone regression script.
    $lint = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($base . '/payload/a7113_sidebar_check.php');
    exec($lint, $lintLines, $lintCode);
    if ($lintCode !== 0) {
        throw new RuntimeException('Regression script PHP syntax invalid');
    }
    // Reinspect just before modifications to fail closed on changed source.
    if (inspectState($root, $files) !== $states) {
        throw new RuntimeException('Source changed during preflight');
    }
    $id = 'a7113_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    $dir = $backupRoot . '/' . $id;
    if (!mkdir($dir, 0700, true)) {
        throw new RuntimeException('Cannot create backup directory');
    }
    $manifest = ['version' => 'A7113', 'files' => []];
    foreach ($pending as $rel) {
        $target = $root . '/' . $rel;
        $existed = is_file($target);
        $manifest['files'][$rel] = ['existed' => $existed, 'after' => $files[$rel]['after']];
        if ($existed) {
            $dest = $dir . '/' . $rel;
            if (!is_dir(dirname($dest)) && !mkdir(dirname($dest), 0700, true)) {
                throw new RuntimeException('Cannot create backup subfolder');
            }
            if (!copy($target, $dest) || hash_file('sha256', $dest) !== $files[$rel]['before']) {
                throw new RuntimeException("Cannot preserve original {$rel}");
            }
        }
    }
    if (file_put_contents($dir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)) === false) {
        throw new RuntimeException('Cannot save backup manifest');
    }
    $applied = [];
    try {
        foreach ($pending as $rel) {
            $target = $root . '/' . $rel;
            if ((is_file($target) ? hash_file('sha256', $target) : null) !== $files[$rel]['before']) {
                throw new RuntimeException("File changed before write {$rel}");
            }
            $temporary = $target . '.a7113-' . bin2hex(random_bytes(4));
            if (!copy($base . '/' . $files[$rel]['payload'], $temporary) || hash_file('sha256', $temporary) !== $files[$rel]['after']) {
                @unlink($temporary);
                throw new RuntimeException("Cannot stage {$rel}");
            }
            if (!rename($temporary, $target)) {
                @unlink($temporary);
                throw new RuntimeException("Cannot install {$rel}");
            }
            $applied[] = $rel;
            echo "[APPLIED] {$rel}\n";
        }
    } catch (Throwable $e) {
        foreach (array_reverse($applied) as $rel) {
            if ($manifest['files'][$rel]['existed']) {
                copy($dir . '/' . $rel, $root . '/' . $rel);
            } else {
                @unlink($root . '/' . $rel);
            }
        }
        throw $e;
    }
    echo "[BACKUP_ID] {$id}\n";
    echo '[RESULT] A7113_APPLIED changed=' . count($applied) . " backup={$id}\n";
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . "\n");
    exit(2);
}
