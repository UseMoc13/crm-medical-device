<?php
/** A7.6.3 guarded, idempotent Dashboard-only patch; no SQL or migrations. */
declare(strict_types=1);
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
$controller = 'app/Http/Controllers/DashboardController.php';
$view = 'resources/views/dashboard/index.blade.php';
$payloadRoot = __DIR__ . '/a763_payload/';
$added = [
    'app/Services/CrmDashboardViewData.php',
    'tests/Unit/CrmDashboardViewDataTest.php',
    'tests/Feature/CrmDashboardViewerIsolationTest.php',
];
function a763_die(string $msg): never { fwrite(STDERR, '[FAIL] '.$msg.PHP_EOL); exit(1); }
function a763_read(string $path): string
{
    if (!is_file($path) || !is_readable($path)) a763_die('Missing/unreadable: '.$path);
    $data = file_get_contents($path);
    if ($data === false) a763_die('Cannot read: '.$path);
    return $data;
}
function a763_blob(string $text): string { return sha1('blob ' . strlen($text) . "\0" . $text); }
function a763_controller(): string
{
    return <<<'PHP_CONTROLLER'
<?php

namespace App\Http\Controllers;

use App\Services\CrmDashboardViewData;

/** A7.6.3: all Dashboard KPI/Activity values come from permission-aware read model. */
class DashboardController extends Controller
{
    public function index(CrmDashboardViewData $dashboard)
    {
        return view('dashboard.index', $dashboard->forCurrentUser());
    }
}
PHP_CONTROLLER;
}
function a763_view(string $old): string
{
    $replacement = <<<'BLADE'
            <div class="dash-a763-sales" role="list" aria-label="Accessible sales indicators">
                @forelse($salesCards as $sale)
                    <div class="dash-a763-metric" role="listitem">
                        <div class="dash-a763-metric-label">{{ $sale['label'] }}</div>
                        <strong class="dash-a763-metric-value">{{ $sale['value'] }}</strong>
                        <small class="dash-a763-metric-hint">{{ $sale['hint'] }}</small>
                    </div>
                @empty
                    <div class="empty">No sales metrics available for your access.</div>
                @endforelse
            </div>
BLADE;
    $pattern = '/[ \t]*<div class="empty">\s*Sales data will appear here\.\s*<\/div>/';
    $result = preg_replace($pattern, $replacement, $old, 1, $count);
    if ($result === null || $count !== 1) a763_die('Sales placeholder changed; refusing view patch.');
    $hints = [
        'Customer database' => 'customers',
        'Current leads' => 'leads',
        'Active opportunities' => 'opportunities',
        'Current service tickets' => 'service_tickets',
    ];
    foreach ($hints as $source => $key) {
        if (substr_count($result, $source) !== 1) a763_die('Dashboard hint changed: '.$source);
        $result = str_replace($source, "{{ \$statHints['".$key."'] }}", $result);
    }
    $css = <<<'CSS'
    /* A7.6.3: isolated sales KPI grid; announcement slider remains unchanged. */
    .dash-a763-sales { display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:12px; }
    .dash-a763-metric { display:flex; flex-direction:column; gap:5px; min-width:0; padding:14px; border:1px solid #e5e9f0; border-radius:10px; background:#fbfcfe; }
    .dash-a763-metric-label { color:#64748b; font-size:12px; font-weight:600; }
    .dash-a763-metric-value { color:#17284f; font-size:20px; font-weight:700; overflow-wrap:anywhere; font-variant-numeric:tabular-nums; }
    .dash-a763-metric-hint { color:#7e899b; font-size:11px; line-height:1.5; }
    @media (max-width:640px) { .dash-a763-sales { grid-template-columns:1fr; } }
CSS;
    if (substr_count($result, '</style>') !== 1) a763_die('Expected exactly one scoped Dashboard style block.');
    $result = str_replace('</style>', $css."\n</style>", $result);
    return $result;
}
$oldController = a763_read($root . '/' . $controller);
$oldView = a763_read($root . '/' . $view);
$newController = a763_controller();
$installedController = trim($oldController) === trim($newController);
$installedView = str_contains($oldView, 'dash-a763-sales');
if ($installedController xor $installedView) a763_die('Partial previous installation. Restore backups before retrying.');
if (!$installedController) {
    // Git blob SHA from the original Big-Changes DashboardController.php, 3,179 bytes.
    // Changing even one byte requires manual review instead of overwriting local edits.
    if (a763_blob(str_replace(["\r\n", "\r"], "\n", $oldController)) !== '0542fd14fe2625f43936d0567335eaa6545dc08e') {
        a763_die('DashboardController differs from audited baseline. No overwrite; preserve local edits.');
    }
    foreach (["{{ \$stats['customers'] }}", "{{ \$stats['leads'] }}", "{{ \$stats['opportunities'] }}", "{{ \$stats['service_tickets'] }}"] as $needle) {
        if (substr_count($oldView, $needle) !== 1) a763_die('Dashboard stat template changed: '.$needle);
    }
    if (!str_contains($oldView, 'Announcement::publishDueScheduled();') || !str_contains($oldView, 'dashAnnouncementSlider')) {
        a763_die('Announcement slider differs from audited structure. Refusing patch.');
    }
    $newView = a763_view($oldView);
} else {
    $newView = $oldView;
}
$plans = [];
if (!$installedController) {
    $plans[$controller] = $newController;
    $plans[$view] = $newView;
}
foreach ($added as $path) {
    $data = a763_read($payloadRoot . $path);
    if (is_file($root . '/' . $path)) {
        if (a763_read($root . '/' . $path) !== $data) a763_die('Collision: '.$path.' differs from payload.');
        echo '[PASS] Already installed: '.$path.PHP_EOL;
    } else {
        $plans[$path] = $data;
    }
}
foreach ($plans as $path => $_) echo '[CHECK] '.(in_array($path, $added, true) ? 'add: ' : 'patch: ').$path.PHP_EOL;
if ($dry) { echo '[PASS] Dry run. No application files or DB rows changed.'.PHP_EOL; exit(0); }
if ($plans === []) { echo '[PASS] A7.6.3 already installed, no changes.'.PHP_EOL; exit(0); }
$backupPath = $root . '/tools/a763_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
if (!is_dir($backupPath) && !mkdir($backupPath, 0775, true)) a763_die('Cannot create backup folder.');
$previous = [];
$created = [];
try {
    // Back up both original changed files before starting writes.
    foreach ([$controller, $view] as $path) {
        if (!array_key_exists($path, $plans)) continue;
        $previous[$path] = a763_read($root . '/' . $path);
        $destination = $backupPath . '/' . $path;
        if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0775, true)) throw new RuntimeException('Backup directory failure');
        if (file_put_contents($destination, $previous[$path], LOCK_EX) === false) throw new RuntimeException('Backup write failure');
    }
    foreach ($plans as $path => $data) {
        $destination = $root . '/' . $path;
        if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0775, true)) throw new RuntimeException('Destination directory failure');
        if (!is_file($destination)) $created[] = $destination;
        if (file_put_contents($destination, $data, LOCK_EX) === false) throw new RuntimeException('Write failure: '.$path);
        echo '[PASS] '.(in_array($path, $added, true) ? 'Added: ' : 'Patched: ').$path.PHP_EOL;
    }
} catch (Throwable $e) {
    foreach ($previous as $path => $text) file_put_contents($root.'/'.$path, $text);
    foreach ($created as $path) if (is_file($path)) unlink($path);
    a763_die('Installer restored originals after failure: '.$e->getMessage());
}
echo '[PASS] A7.6.3 installed. Backup: '.$backupPath.PHP_EOL;
echo 'No migration, DB writes, reporting controller modifications, or announcement-slider edits performed.'.PHP_EOL;
