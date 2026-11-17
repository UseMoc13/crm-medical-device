<?php
/** A7.4.12.1.1 Calendar script syntax + interaction contract preflight. */
declare(strict_types=1);

$root = dirname(__DIR__);
$view = $root . '/resources/views/calendar/index.blade.php';
$style = $root . '/resources/views/calendar/_style.blade.php';
$s = is_file($view) ? file_get_contents($view) : '';
$css = is_file($style) ? file_get_contents($style) : '';
$checks = [
    'Calendar index exists' => is_file($view),
    'Calendar shared stylesheet exists' => is_file($style),
    'Day popup container' => str_contains($s, 'id="calendarDayBackdrop"'),
    'Daily timeline target' => str_contains($s, 'id="calendarDayTimeline"'),
    'Day click handler' => str_contains($s, "document.querySelectorAll('[data-calendar-date]')"),
    'Event click handler' => str_contains($s, "document.querySelectorAll('[data-calendar-open]')"),
    'Popup opens on day selection' => str_contains($s, 'backdrop.hidden = false;'),
    'Popup closes through dismissal' => str_contains($s, 'backdrop.hidden = true;'),
    'Calendar JS literal newline bug removed' => !str_contains($s, ';\\n        noteAdd.href'),
    'Separate calendar and notes links retained' => (bool) preg_match('/add\.href\s*=\s*`[^`]+`;\R\s*noteAdd\.href\s*=/', $s),
    'Timeline entries rendered from day data' => str_contains($s, 'for (const item of list)'),
    'Accessible keyboard day opening' => str_contains($s, "e.key === 'Enter' || e.key === ' '"),
    'Modal hidden by default' => str_contains($css, '.calendar-day-backdrop[hidden]'),
    'One calendar stylesheet include' => substr_count($s, "@include('calendar._style')") === 1,
];
$pass = 0;
foreach ($checks as $name => $ok) {
    echo ($ok ? '[PASS]' : '[FAIL]') . " {$name}\n";
    if ($ok) $pass++;
}

// JS syntax test: isolate inline Calendar script, substitute only server-side Blade tokens.
// Browser receives concrete JSON/URLs at runtime; use minimal substitutes for `node --check`.
$scriptCheck = null;
if (preg_match('~<script>\s*(.*?)\s*</script>~s', $s, $match)) {
    $script = $match[1];
    $script = preg_replace('/const byDay = [^\n]+;/', 'const byDay = {};', $script, 1);
    $script = str_replace("@json(route('calendar.create'))", '"/calendar/create"', $script);
    $script = str_replace("{{ route('notes.create') }}", '/notes/create', $script);
    if (preg_match('/\{\{|@json\(/', $script)) {
        echo "[FAIL] Unprocessed Blade marker in JS syntax check\n";
        $scriptCheck = false;
    } else {
        $tmp = sys_get_temp_dir() . '/crm-calendar-' . bin2hex(random_bytes(6)) . '.js';
        file_put_contents($tmp, $script);
        $out = [];
        $exitCode = 0;
        @exec('node --check ' . escapeshellarg($tmp) . ' 2>&1', $out, $exitCode);
        @unlink($tmp);
        if ($exitCode === 0) {
            echo "[PASS] Calendar inline JavaScript parses (Node.js)\n";
            $scriptCheck = true;
        } elseif (str_contains(implode("\n", $out), 'not recognized') || str_contains(implode("\n", $out), 'not found')) {
            echo "[SKIP] Node.js unavailable; use browser console for syntax validation\n";
        } else {
            echo "[FAIL] Calendar JavaScript syntax: " . implode(' | ', $out) . "\n";
            $scriptCheck = false;
        }
    }
} else {
    echo "[FAIL] Missing Calendar inline JS\n";
    $scriptCheck = false;
}
if ($scriptCheck === true) $pass++;
$total = count($checks) + ($scriptCheck === null ? 0 : 1);
echo "\nA7.4.12.1.1 Calendar static: {$pass}/{$total} PASS" . ($pass === $total ? ", 0 FAIL.\n" : ".\n");
exit($pass === $total ? 0 : 1);
