<?php
/**
 * A7.10.3 — conservative route-driven Blade UI permission guards.
 * --check is read-only. --apply writes guarded Blade only, with full backups.
 * --rollback=BACKUP_ID restores only files whose checksum still matches the
 * installer output, preventing overwriting newer developer changes.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

use App\Support\CrmViewAction;

const A7103_OPEN = '~<(a|form|button)\b(?:(?:"[^"]*"|\'[^\']*\'|[^\'">])*)>~is';

function a7103_supported(string $name): bool
{
    return CrmViewAction::guarded($name);
}

/** @return array{source:string,added:int,already:int,byRoute:array<string,int>} */
function a7103_transform(string $source): array
{
    $ranges = [];
    if (preg_match_all('~<script\b[^>]*>[\s\S]*?</script\s*>|<style\b[^>]*>[\s\S]*?</style\s*>|\{\{--[\s\S]*?--\}\}~i', $source, $skip, PREG_OFFSET_CAPTURE)) {
        foreach ($skip[0] as $hit) {
            $ranges[] = [$hit[1], $hit[1] + strlen($hit[0])];
        }
    }

    $inserts = [];
    $added = $already = 0;
    $byRoute = [];
    preg_match_all(A7103_OPEN, $source, $openings, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    foreach ($openings as $match) {
        $tag = strtolower($match[1][0]);
        $opening = $match[0][0];
        $start = $match[0][1];
        $skipTag = false;
        foreach ($ranges as [$from, $to]) {
            if ($start >= $from && $start < $to) {
                $skipTag = true;
                break;
            }
        }
        if ($skipTag) continue;

        $route = null;
        if (preg_match('~\broute\s*\(\s*([\'\"])([A-Za-z0-9_.-]+)\1~', $opening, $r)) {
            $route = $r[2];
        } elseif ($tag === 'button') {
            // These buttons start JS-driven workflow actions, not HTML form posts.
            $triggers = [
                'data-delivery-action' => 'deliveries.change-status',
                'data-installation-next' => 'installations.change-status',
                'data-ticket-workflow-action' => 'service-tickets.change-status',
                'data-mt-next' => 'maintenance.change-status',
                'checklist-toggle' => 'installations.checklists.update',
                'ticket-part-open' => 'service-tickets.parts.store',
            ];
            foreach ($triggers as $needle => $name) {
                if (str_contains($opening, $needle)) {
                    $route = $name;
                    break;
                }
            }
        }
        if ($route === null || !a7103_supported($route)) continue;
        $endTag = '</'.$tag.'>';
        $end = stripos($source, $endTag, $start + strlen($opening));
        if ($end === false) {
            throw new RuntimeException("Unclosed <{$tag}> action for {$route} near offset {$start}");
        }
        $end += strlen($endTag);

        // Guard must be immediately before this tag, not before a previous tag.
        $preceding = rtrim(substr($source, max(0, $start - 320), min(320, $start)));
        $predicate = "@if(\\App\\Support\\CrmViewAction::allows('{$route}'))";
        if (str_ends_with($preceding, $predicate)) {
            $already++;
            continue;
        }
        $inserts[] = [$start, "{{-- A7103:{$route} --}}{$predicate}\n"];
        $inserts[] = [$end, "\n@endif{{-- /A7103 --}}"];
        $added++;
        $byRoute[$route] = ($byRoute[$route] ?? 0) + 1;
    }
    usort($inserts, static fn(array $a, array $b): int => $b[0] <=> $a[0]);
    foreach ($inserts as [$position, $text]) {
        $source = substr_replace($source, $text, $position, 0);
    }
    return ['source' => $source, 'added' => $added, 'already' => $already, 'byRoute' => $byRoute];
}

/** @return array<string,array{old:string,new:string,added:int,already:int,byRoute:array<string,int>}> */
function a7103_collect(string $root): array
{
    $dir = $root.'/resources/views';
    if (!is_dir($dir)) throw new RuntimeException('Missing resources/views. Extract ZIP into CRM project root.');
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    $result = [];
    foreach ($it as $item) {
        if (!$item->isFile() || !str_ends_with($item->getFilename(), '.blade.php')) continue;
        $file = $item->getPathname();
        $relative = str_replace('\\', '/', substr($file, strlen($root) + 1));
        $old = file_get_contents($file);
        if ($old === false) throw new RuntimeException('Cannot read '.$relative);
        $r = a7103_transform($old);
        if ($r['added'] || $r['already']) {
            $result[$relative] = ['old' => $old, 'new' => $r['source'], 'added' => $r['added'], 'already' => $r['already'], 'byRoute' => $r['byRoute']];
        }
    }
    ksort($result);
    return $result;
}

function a7103_rollback(string $root, string $id): void
{
    if (!preg_match('/\A[0-9]{8}-[0-9]{6}-[0-9a-f]{8}\z/D', $id)) {
        throw new RuntimeException('Invalid backup ID; expected YYYYMMDD-HHMMSS-xxxxxxxx.');
    }
    $dir = $root.'/storage/app/private/a7103_crud_ui_backups/'.$id;
    $manifest = $dir.'/manifest.json';
    if (!is_file($manifest)) throw new RuntimeException('Backup manifest not found.');
    $data = json_decode((string)file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
    $restore = [];
    foreach ($data['files'] ?? [] as $relative => $hashes) {
        if (!str_starts_with($relative, 'resources/views/') || str_contains($relative, '..')) {
            throw new RuntimeException('Invalid manifest path.');
        }
        $current = $root.'/'.$relative;
        $backup = $dir.'/'.$relative;
        if (!is_file($current) || !is_file($backup)) throw new RuntimeException('Missing restore file '.$relative);
        if (hash_file('sha256', $current) !== $hashes['after'] || hash_file('sha256', $backup) !== $hashes['before']) {
            throw new RuntimeException('A file has changed since installation; refusing rollback: '.$relative);
        }
        $restore[$relative] = (string)file_get_contents($backup);
    }
    foreach ($restore as $relative => $contents) {
        if (file_put_contents($root.'/'.$relative, $contents, LOCK_EX) === false) {
            throw new RuntimeException('Rollback write failed: '.$relative);
        }
        echo '[RESTORED] '.$relative.PHP_EOL;
    }
    echo '[RESULT] A7103_ROLLBACK_RESTORED; files='.count($restore).PHP_EOL;
}

function a7103_main(array $argv, string $root): int
{
    $arg = $argv[1] ?? '--check';
    if (count($argv) !== 2 || (!in_array($arg, ['--check', '--apply'], true) && !str_starts_with($arg, '--rollback='))) {
        fwrite(STDERR, "Usage: php tools/a7103_crud_ui_installer.php --check|--apply|--rollback=BACKUP_ID\n");
        return 2;
    }
    try {
        if (str_starts_with($arg, '--rollback=')) {
            a7103_rollback($root, substr($arg, 11));
            return 0;
        }
        $all = a7103_collect($root);
        $changed = array_filter($all, static fn(array $r): bool => $r['old'] !== $r['new']);
        $counts = array_reduce($all, static function (array $acc, array $r): array {
            $acc[0] += $r['added'];
            $acc[1] += $r['already'];
            return $acc;
        }, [0, 0]);
        foreach ($changed as $relative => $r) {
            echo '[READY] '.$relative.' new_guards='.$r['added'].PHP_EOL;
        }
        if ($arg === '--check') {
            echo sprintf('[RESULT] A7103_PATCH_COMPATIBLE; files_to_change=%d; new_guards=%d; existing_guards=%d; no files modified', count($changed), ...$counts).PHP_EOL;
            return 0;
        }
        if (!$changed) {
            echo '[RESULT] A7103_ALREADY_INSTALLED; no files modified'.PHP_EOL;
            return 0;
        }
        if (!is_file($root.'/app/Support/CrmViewAction.php')) {
            throw new RuntimeException('Missing app/Support/CrmViewAction.php; extract full ZIP first.');
        }
        // Save every original before touching any view.
        $id = date('Ymd-His').'-'.bin2hex(random_bytes(4));
        $backupDir = $root.'/storage/app/private/a7103_crud_ui_backups/'.$id;
        $manifest = ['id'=>$id, 'files'=>[]];
        foreach ($changed as $relative => $r) {
            $backup = $backupDir.'/'.$relative;
            if (!is_dir(dirname($backup)) && !mkdir(dirname($backup), 0770, true) && !is_dir(dirname($backup))) {
                throw new RuntimeException('Cannot create backup dir. No views changed.');
            }
            if (file_put_contents($backup, $r['old'], LOCK_EX) === false) {
                throw new RuntimeException('Cannot backup '.$relative.'; no views changed.');
            }
            $manifest['files'][$relative] = ['before'=>hash('sha256',$r['old']), 'after'=>hash('sha256',$r['new'])];
        }
        if (file_put_contents($backupDir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX) === false) {
            throw new RuntimeException('Cannot write manifest. No views changed.');
        }
        $written = [];
        try {
            foreach ($changed as $relative => $r) {
                $path = $root.'/'.$relative;
                if (file_get_contents($path) !== $r['old']) {
                    throw new RuntimeException('File changed since --check: '.$relative);
                }
                if (file_put_contents($path, $r['new'], LOCK_EX) === false) {
                    throw new RuntimeException('Cannot write '.$relative);
                }
                $written[] = $relative;
                echo '[PATCH] '.$relative.PHP_EOL;
            }
        } catch (Throwable $e) {
            foreach ($written as $relative) {
                file_put_contents($root.'/'.$relative, $changed[$relative]['old'], LOCK_EX);
            }
            throw $e;
        }
        echo '[BACKUP_ID] '.$id.PHP_EOL;
        echo '[BACKUP] '.$backupDir.PHP_EOL;
        echo sprintf('[RESULT] A7103_ROLE_AWARE_UI_APPLIED; files=%d; guards=%d', count($changed), $counts[0]).PHP_EOL;
        return 0;
    } catch (Throwable $e) {
        fwrite(STDERR, '[FAIL] '.$e->getMessage().PHP_EOL);
        return 1;
    }
}
exit(a7103_main($argv, $root));
