<?php
/**
 * A7.10.5: rebuild A7103 role-aware Blade guards from checksum-verified
 * original installer backups. Never edits the database, middleware, or roles.
 * PHP 8.2+. Standalone transform for predictable, testable boundaries.
 */
declare(strict_types=1);

const A7105_ORIGINAL_BACKUP_ID = '20261126-050227-d28dcbfc';

function a7105_route_managed(string $name): bool
{
    // Include only existing CRM page/role routes, not workspace Notes/To-Do/Calendar.
    $prefixes = [
        'customers.', 'contacts.', 'leads.', 'activities.', 'opportunities.',
        'quotations.', 'sales-orders.', 'sales_orders.', 'contracts.',
        'products.', 'product-categories.', 'brands.', 'warehouses.',
        'inventories.', 'stock-movements.', 'serial-numbers.',
        'deliveries.', 'installations.', 'warranties.', 'service-tickets.',
        'service-history.', 'maintenance.', 'invoices.', 'reports.',
        'announcement-management.', 'users.', 'roles.', 'branches.',
        'organization.', 'teams.',
    ];
    foreach ($prefixes as $prefix) {
        if (str_starts_with($name, $prefix)) return true;
    }
    return false;
}

/** @return list<array{start:int,end:int,route:string,tag:string}> */
function a7105_spans(string $source): array
{
    // A7103 original backups contain no A7103 tags. Do not reconstruct from
    // the possibly-malformed currently guarded file.
    if (str_contains($source, '{{-- A7103:') || str_contains($source, '{{-- A7105:')) {
        throw new RuntimeException('Original backup already contains generated guards.');
    }
    $skip = [];
    preg_match_all('~<script\b[^>]*>[\s\S]*?</script\s*>|<style\b[^>]*>[\s\S]*?</style\s*>|<!--(?:(?!-->)[\s\S])*-->|\{\{--[\s\S]*?--\}\}~i',
        $source, $zones, PREG_OFFSET_CAPTURE);
    foreach ($zones[0] as $zone) $skip[] = [$zone[1], $zone[1] + strlen($zone[0])];

    // Match opening attributes using the same quotation rules as old installer.
    $openRegex = <<<'REGEX'
~<(a|form|button)\b(?:(?:"[^"]*"|'[^']*'|[^'">])*)>~is
REGEX;
    preg_match_all($openRegex, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $spans = [];
    foreach ($matches as $match) {
        $tag = strtolower($match[1][0]);
        $opening = $match[0][0];
        $start = (int)$match[0][1];
        foreach ($skip as [$a,$b]) if ($start >= $a && $start < $b) continue 2;

        $route = null;
        if (preg_match('~\broute\s*\(\s*([\'\"])([A-Za-z0-9_.-]+)\1~', $opening, $m)) {
            $route = $m[2];
        } elseif ($tag === 'button') {
            foreach ([
                'data-delivery-action' => 'deliveries.change-status',
                'data-installation-next' => 'installations.change-status',
                'data-ticket-workflow-action' => 'service-tickets.change-status',
                'data-mt-next' => 'maintenance.change-status',
                'checklist-toggle' => 'installations.checklists.update',
                'ticket-part-open' => 'service-tickets.parts.store',
                'ticket-part-edit' => 'service-tickets.parts.update',
                'ticket-part-remove' => 'service-tickets.parts.destroy',
            ] as $needle => $candidate) {
                if (str_contains($opening, $needle)) { $route = $candidate; break; }
            }
        }
        if (!is_string($route) || !a7105_route_managed($route)) continue;
        // Filtering and search are not CRUD mutations. Avoid wrapping large GET
        // forms (often containing nested reset links and Blade @else blocks).
        if ($tag === 'form' && preg_match('/\bmethod\s*=\s*[\'\"]?get\b/i', $opening)) continue;
        // Do not guard search/reset/index navigation when it appears in a form.
        $closer = '</'.$tag.'>';
        $endPos = stripos($source, $closer, $start + strlen($opening));
        if ($endPos === false) throw new RuntimeException("Missing closing </$tag> for $route");
        $end = $endPos + strlen($closer);
        $spans[] = ['start'=>$start, 'end'=>$end, 'route'=>$route, 'tag'=>$tag];
    }
    usort($spans, static fn(array $a, array $b): int => ($a['start'] <=> $b['start']) ?: ($b['end'] <=> $a['end']));
    // A wrapping rule may be nested but may never CROSS another one.
    $stack = [];
    foreach ($spans as $span) {
        while ($stack && $span['start'] >= $stack[count($stack)-1]['end']) array_pop($stack);
        if ($stack && $span['end'] > $stack[count($stack)-1]['end']) {
            throw new RuntimeException('Crossing action tags around '.$span['route'].' at byte offset '.$span['start']);
        }
        $stack[] = $span;
    }
    return $spans;
}

/** @return array{source:string,guards:int} */
function a7105_transform(string $source): array
{
    $spans = a7105_spans($source);
    $events = [];
    foreach ($spans as $span) {
        $s = $span['start']; $e = $span['end']; $route = $span['route'];
        $events[$s][] = ['open'=>true, 'span'=>$span];
        $events[$e][] = ['open'=>false, 'span'=>$span];
    }
    ksort($events, SORT_NUMERIC);
    $output=''; $cursor=0;
    foreach ($events as $position => $changes) {
        $output .= substr($source, $cursor, $position-$cursor);
        // First close a preceding guard, then open the next. Nested closures
        // happen innermost-first; nested openings happen outermost-first.
        usort($changes, static function(array $x, array $y): int {
            if ($x['open'] !== $y['open']) return $x['open'] ? 1 : -1;
            if ($x['open']) return $y['span']['end'] <=> $x['span']['end'];
            return $y['span']['start'] <=> $x['span']['start'];
        });
        foreach ($changes as $change) {
            if ($change['open']) {
                $route = $change['span']['route'];
                $output .= "\n{{-- A7105:$route --}}\n@if(\\App\\Support\\CrmViewAction::allows('$route'))\n";
            } else {
                $output .= "\n@endif\n{{-- /A7105 --}}\n";
            }
        }
        $cursor = $position;
    }
    $output .= substr($source, $cursor);
    return ['source'=>$output,'guards'=>count($spans)];
}

function a7105_php_lint(string $php): ?string
{
    $temp = tempnam(sys_get_temp_dir(), 'a7105_');
    if ($temp === false) return 'Cannot create temp file';
    try {
        if (file_put_contents($temp, $php, LOCK_EX) === false) return 'Cannot write temp file';
        $o=[];$rc=0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1',$o,$rc);
        return $rc === 0 ? null : implode(' ', $o);
    } finally { @unlink($temp); }
}

/** @return array<string,mixed> */
function a7105_plan(string $root): array
{
    $dir = $root.'/storage/app/private/a7103_crud_ui_backups/'.A7105_ORIGINAL_BACKUP_ID;
    $manifestFile = $dir.'/manifest.json';
    if (!is_file($manifestFile)) throw new RuntimeException('A7103 original backup missing. No files modified.');
    $manifest = json_decode((string)file_get_contents($manifestFile), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($manifest['files'] ?? null) || $manifest['files'] === []) throw new RuntimeException('A7103 backup manifest empty.');
    if (!is_file($root.'/app/Support/CrmViewAction.php')) throw new RuntimeException('Missing A7103 permission helper.');
    if (!is_file($root.'/vendor/autoload.php')) throw new RuntimeException('Composer dependencies missing.');
    require_once $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $compiler = app('blade.compiler');

    $rows=[]; $errors=[]; $currentState=[]; $guards=0;
    foreach ($manifest['files'] as $path => $hashes) {
        if (!is_string($path) || !preg_match('~\Aresources/views/[a-zA-Z0-9_./-]+\.blade\.php\z~D', $path) || str_contains($path, '..')) {
            throw new RuntimeException('Unsafe path in manifest.');
        }
        $currentPath = $root.'/'.$path; $originalPath = $dir.'/'.$path;
        if (!is_file($currentPath) || !is_file($originalPath)) {
            $errors[] = "$path: missing current or backup source"; continue;
        }
        $current = file_get_contents($currentPath);
        $original = file_get_contents($originalPath);
        if ($current === false || $original === false || hash('sha256',$original) !== ($hashes['before'] ?? null)) {
            $errors[] = "$path: original backup checksum invalid"; continue;
        }
        try {
            $new=a7105_transform($original);
            $php=$compiler->compileString($new['source']);
            $lint=a7105_php_lint($php);
            if ($lint !== null) throw new RuntimeException($lint);
        } catch(Throwable $e) {
            $errors[] = "$path: ".$e->getMessage(); continue;
        }
        $guards += $new['guards'];
        $shaCurrent=hash('sha256',$current);
        $oldHash=$hashes['after'] ?? null;
        $newHash=hash('sha256',$new['source']);
        if ($shaCurrent === $newHash) $state='new';
        elseif ($shaCurrent === $oldHash) $state='old';
        else {
            $errors[] = "$path: local file changed since A7103 backup (SHA-256 mismatch); refusing overwrite";
            continue;
        }
        $currentState[$state] = ($currentState[$state] ?? 0) + 1;
        $rows[$path]=['current'=>$current,'original'=>$original,'rebuilt'=>$new['source'],'new_hash'=>$newHash,'state'=>$state];
    }
    if (count($currentState)>1) $errors[]='Mixed A7103/A7105 state; refusing partial migration.';
    return ['rows'=>$rows, 'errors'=>$errors,'states'=>$currentState,'guards'=>$guards, 'manifest'=>$manifest];
}

function a7105_main(array $argv): int
{
    $root=dirname(__DIR__);
    $mode=$argv[1]??'';
    if (count($argv)!==2 || !in_array($mode,['--check','--apply','--restore-original'],true)) {
        fwrite(STDERR,"Usage: php tools/a7105_rebuild_role_ui.php --check|--apply|--restore-original\n");return 2;
    }
    try {
        $plan=a7105_plan($root);
        foreach ($plan['errors'] as $message) fwrite(STDERR,'[FAIL] '.$message.PHP_EOL);
        if ($plan['errors']) {
            echo '[RESULT] A7105_HOLD_NO_FILES_MODIFIED'.PHP_EOL; return 1;
        }
        $state=key($plan['states']);
        printf("[CHECK] validated_views=%d guards=%d state=%s\n",count($plan['rows']),$plan['guards'],$state);
        if ($mode==='--check') {
            echo '[RESULT] '.($state==='new'?'A7105_ALREADY_REBUILT':'A7105_SAFE_REBUILD_READY').' (no files modified)'.PHP_EOL;
            return 0;
        }
        if ($mode==='--apply' && $state==='new') {
            echo '[RESULT] A7105_ALREADY_REBUILT'.PHP_EOL; return 0;
        }
        if ($mode==='--restore-original' && $state!=='new') {
            throw new RuntimeException('--restore-original only supported after A7105 apply.');
        }
        if ($mode==='--apply' && $state!=='old') {
            throw new RuntimeException('--apply requires current A7103 files unchanged.');
        }
        $snapshotId=date('Ymd-His').'-'.bin2hex(random_bytes(4));
        $backupDir=$root.'/storage/app/private/a7105_rebuild_backups/'.$snapshotId;
        $list=[];
        foreach ($plan['rows'] as $path=>$row) {
            $backup=$backupDir.'/'.$path;
            if (!is_dir(dirname($backup)) && !mkdir(dirname($backup),0770,true) && !is_dir(dirname($backup))) throw new RuntimeException('Cannot create snapshot directory.');
            if (file_put_contents($backup,$row['current'],LOCK_EX)===false) throw new RuntimeException('Cannot snapshot '.$path);
            $list[$path]=['current'=>hash('sha256',$row['current']),'target'=>hash('sha256',$mode==='--apply'?$row['rebuilt']:$row['original'])];
        }
        if (file_put_contents($backupDir.'/manifest.json',json_encode(['mode'=>$mode,'files'=>$list],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR), LOCK_EX)===false) throw new RuntimeException('Cannot write snapshot manifest.');
        $done=[];
        try {
            foreach ($plan['rows'] as $path=>$row) {
                $target=$mode==='--apply'?$row['rebuilt']:$row['original'];
                if (hash_file('sha256',$root.'/'.$path)!==hash('sha256',$row['current'])) throw new RuntimeException('Concurrent update: '.$path);
                if (file_put_contents($root.'/'.$path,$target,LOCK_EX)===false) throw new RuntimeException('Write failure: '.$path);
                $done[$path]=$row['current'];
            }
        } catch(Throwable $e) {
            foreach ($done as $path=>$value) @file_put_contents($root.'/'.$path,$value,LOCK_EX);
            throw $e;
        }
        echo '[BACKUP] '.$backupDir.PHP_EOL;
        echo '[RESULT] '.($mode==='--apply'?'A7105_ROLE_UI_REBUILT_AND_VALIDATED':'A7105_ORIGINAL_VIEWS_RESTORED').'; files='.count($done).PHP_EOL;
        return 0;
    } catch(Throwable $e) {
        fwrite(STDERR,'[FAIL] '.$e->getMessage().PHP_EOL);
        return 1;
    }
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) exit(a7105_main($argv));
