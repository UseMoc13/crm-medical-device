<?php
/**
 * A7.10.4 — repair Blade directive adjacency caused by A7103 guard injector.
 * Read-only --check; --apply requires all guarded views to compile and PHP-lint
 * BEFORE any file is modified. --rollback uses checksum verified backups.
 * No business SQL, migrations, role changes or route changes.
 */
declare(strict_types=1);

$root = dirname(__DIR__);

function a7104_repair(string $source, int &$n): string
{
    // A7103 used "@endif{{-- /A7103 --}}" with no trailing newline. When an
    // existing @endunless/@endif/@else shares the same line, the Blade compiler
    // may swallow it as trailing text of the comment: a PHP parse error results.
    $updated = preg_replace('/(\{\{-- \/A7103 --\}\})(?!\R)/', "$1\n", $source, -1, $n);
    if (!is_string($updated)) throw new RuntimeException('Guard regex failed.');
    return $updated;
}

function a7104_lint(string $compiled, string $path): ?string
{
    $temp = tempnam(sys_get_temp_dir(), 'crm_a7104_');
    if ($temp === false) return 'Cannot allocate PHP lint file';
    try {
        if (file_put_contents($temp, $compiled, LOCK_EX) === false) return 'Cannot write PHP lint file';
        $output = []; $status = 0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1', $output, $status);
        return $status === 0 ? null : implode(' ', $output);
    } finally {
        @unlink($temp);
    }
}

function a7104_prepare(string $root): array
{
    $dir = $root.'/resources/views';
    if (!is_dir($dir)) throw new RuntimeException('Run from CRM root (missing resources/views).');
    if (!is_file($root.'/vendor/autoload.php')) throw new RuntimeException('Missing vendor/autoload.php.');
    require_once $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $blade = app('blade.compiler');

    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    $updates = [];
    $guarded = $validated = $repairs = 0;
    $errors = [];
    foreach ($it as $file) {
        if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
        $absolute = $file->getPathname();
        $relative = str_replace('\\', '/', substr($absolute, strlen($root) + 1));
        $original = file_get_contents($absolute);
        if ($original === false) throw new RuntimeException('Cannot read '.$relative);
        if (!str_contains($original, '{{-- A7103:')) continue;
        $guarded++;
        $n = 0;
        $updated = a7104_repair($original, $n);
        $openingCount = preg_match_all('/\{\{-- A7103:[^\r\n]*?--\}\}/', $updated);
        $closingCount = substr_count($updated, '{{-- /A7103 --}}');
        if ($openingCount !== $closingCount) {
            $errors[] = "$relative: guard markers unbalanced ($openingCount/$closingCount)";
            continue;
        }
        try {
            $compiled = $blade->compileString($updated);
        } catch (Throwable $e) {
            $errors[] = $relative.': Blade compiler: '.$e->getMessage();
            continue;
        }
        $lint = a7104_lint($compiled, $relative);
        if ($lint !== null) {
            $errors[] = $relative.': '.$lint;
            continue;
        }
        $validated++;
        if ($original !== $updated) {
            $updates[$relative] = ['before' => $original, 'after' => $updated];
            $repairs += $n;
        }
    }
    ksort($updates);
    return ['updates' => $updates, 'guarded' => $guarded, 'validated' => $validated, 'repairs' => $repairs, 'errors' => $errors];
}

function a7104_rollback(string $root, string $id): int
{
    if (!preg_match('/\A\d{8}-\d{6}-[a-f0-9]{8}\z/D', $id)) {
        throw new RuntimeException('Invalid backup ID.');
    }
    $dir = $root.'/storage/app/private/a7104_guard_repair_backups/'.$id;
    $meta = json_decode((string) @file_get_contents($dir.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $restore = [];
    foreach (($meta['files'] ?? []) as $relative => $entry) {
        if (!str_starts_with($relative, 'resources/views/') || str_contains($relative, '..')) {
            throw new RuntimeException('Invalid backup path.');
        }
        $current = $root.'/'.$relative;
        $backup = $dir.'/'.$relative;
        if (!is_file($current) || !is_file($backup)
            || hash_file('sha256', $current) !== $entry['after']
            || hash_file('sha256', $backup) !== $entry['before']) {
            throw new RuntimeException('Checksum mismatch; refusing rollback for '.$relative);
        }
        $restore[$relative] = (string)file_get_contents($backup);
    }
    foreach ($restore as $relative => $contents) {
        if (file_put_contents($root.'/'.$relative, $contents, LOCK_EX) === false) {
            throw new RuntimeException('Restore write failed: '.$relative);
        }
        echo '[RESTORED] '.$relative.PHP_EOL;
    }
    echo '[RESULT] A7104_REPAIR_ROLLED_BACK; files='.count($restore).PHP_EOL;
    return 0;
}

function a7104_main(array $argv, string $root): int
{
    $mode = $argv[1] ?? '--check';
    if (count($argv) !== 2 || (!in_array($mode, ['--check', '--apply'], true) && !str_starts_with($mode, '--rollback='))) {
        fwrite(STDERR, "Usage: php tools/a7104_repair_guard_syntax.php --check|--apply|--rollback=BACKUP_ID\n");
        return 2;
    }
    try {
        if (str_starts_with($mode, '--rollback=')) return a7104_rollback($root, substr($mode, 11));
        $r = a7104_prepare($root);
        foreach ($r['errors'] as $e) fwrite(STDERR, '[FAIL] '.$e.PHP_EOL);
        if ($r['errors'] !== []) {
            fwrite(STDERR, '[RESULT] A7104_VALIDATION_FAILED; no files modified; use A7103 original backup if necessary'.PHP_EOL);
            return 1;
        }
        foreach ($r['updates'] as $relative => $_) echo '[READY] '.$relative.PHP_EOL;
        echo sprintf('[CHECK] guarded_views=%d compiled_and_linted=%d files_to_repair=%d separators=%d',
            $r['guarded'], $r['validated'], count($r['updates']), $r['repairs']).PHP_EOL;
        if ($mode === '--check') {
            echo '[RESULT] A7104_COMPATIBLE; no files modified'.PHP_EOL;
            return 0;
        }
        if ($r['updates'] === []) {
            echo '[RESULT] A7104_ALREADY_REPAIRED; no files modified'.PHP_EOL;
            return 0;
        }
        $id = date('Ymd-His').'-'.bin2hex(random_bytes(4));
        $backupDir = $root.'/storage/app/private/a7104_guard_repair_backups/'.$id;
        $manifest = ['id'=>$id, 'files'=>[]];
        // ALL backups first; ALL repaired views compiled and PHP-linted above.
        foreach ($r['updates'] as $relative => $row) {
            $dest = $backupDir.'/'.$relative;
            if (!is_dir(dirname($dest)) && !mkdir(dirname($dest), 0770, true) && !is_dir(dirname($dest))) {
                throw new RuntimeException('Cannot create backup folder. No views modified.');
            }
            if (file_put_contents($dest, $row['before'], LOCK_EX) === false) {
                throw new RuntimeException('Cannot backup '.$relative.'; no views modified.');
            }
            $manifest['files'][$relative] = [
                'before'=>hash('sha256',$row['before']),
                'after'=>hash('sha256',$row['after']),
            ];
        }
        if (file_put_contents($backupDir.'/manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT), LOCK_EX) === false) {
            throw new RuntimeException('Cannot save backup manifest. No views modified.');
        }
        $written = [];
        try {
            foreach ($r['updates'] as $relative => $row) {
                $path = $root.'/'.$relative;
                if (file_get_contents($path) !== $row['before']) throw new RuntimeException('Concurrent change: '.$relative);
                if (file_put_contents($path, $row['after'], LOCK_EX) === false) throw new RuntimeException('Cannot patch '.$relative);
                $written[] = $relative;
                echo '[PATCH] '.$relative.PHP_EOL;
            }
        } catch (Throwable $e) {
            foreach ($written as $relative) {
                file_put_contents($root.'/'.$relative, $r['updates'][$relative]['before'], LOCK_EX);
            }
            throw $e;
        }
        echo '[BACKUP_ID] '.$id.PHP_EOL;
        echo '[BACKUP] '.$backupDir.PHP_EOL;
        echo '[RESULT] A7104_BLADE_SYNTAX_REPAIRED; files='.count($written).' separators='.$r['repairs'].PHP_EOL;
        return 0;
    } catch (Throwable $e) {
        fwrite(STDERR, '[FAIL] '.$e->getMessage().PHP_EOL);
        return 1;
    }
}
exit(a7104_main($argv, $root));
