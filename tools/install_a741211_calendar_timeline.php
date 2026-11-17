<?php
/**
 * A7.4.12.1.1 — surgical Calendar day-timeline click repair.
 * Repairs the literal backslash+n that breaks the entire inline Calendar JS.
 * Edits only calendar/index.blade.php and preserves every other local customization.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$path = $root . '/resources/views/calendar/index.blade.php';

if (!is_file($path)) {
    fwrite(STDERR, "ERROR: Calendar index view not found: {$path}\n");
    exit(1);
}
$original = file_get_contents($path);
if ($original === false) {
    fwrite(STDERR, "ERROR: Could not read Calendar index view.\n");
    exit(1);
}

$start = 'add.href = `${base}?date=${encodeURIComponent(date)}`;';
$bad = $start . '\\n        noteAdd.href =';
$good = $start . "\n" . '        noteAdd.href =';
$required = [
    'const showDay = (date) =>',
    'id="calendarDayBackdrop"',
    'id="calendarDayTimeline"',
    "document.querySelectorAll('[data-calendar-open]')",
    "document.querySelectorAll('[data-calendar-date]')",
];
foreach ($required as $item) {
    if (!str_contains($original, $item)) {
        fwrite(STDERR, "ERROR: Unexpected Calendar layout (missing {$item}). No changes made.\n");
        exit(1);
    }
}

if (str_contains($original, $good) && !str_contains($original, $bad)) {
    echo "[PASS] Calendar timeline JavaScript already fixed. No files modified.\n";
    exit(0);
}
if (substr_count($original, $bad) !== 1) {
    fwrite(STDERR, "ERROR: Unexpected Calendar source near timeline links. No changes made.\n");
    fwrite(STDERR, "Check the two statements add.href and noteAdd.href in resources/views/calendar/index.blade.php.\n");
    exit(1);
}

$patched = str_replace($bad, $good, $original);
$backupDir = $root . '/tools/a741211_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true)) {
    fwrite(STDERR, "ERROR: Cannot create backup directory. No changes made.\n");
    exit(1);
}
$backup = $backupDir . '/calendar_index.blade.php';
if (!copy($path, $backup)) {
    fwrite(STDERR, "ERROR: Cannot backup Calendar index. No changes made.\n");
    exit(1);
}
$temp = $path . '.a741211.tmp';
if (file_put_contents($temp, $patched, LOCK_EX) === false) {
    fwrite(STDERR, "ERROR: Cannot write temporary file. Original and backup preserved.\n");
    exit(1);
}
if (!rename($temp, $path)) {
    @unlink($temp);
    fwrite(STDERR, "ERROR: Cannot install fix. Original and backup preserved.\n");
    exit(1);
}
echo "[PASS] Calendar day click and timeline JavaScript syntax repaired.\n";
echo "[PASS] Backup: " . str_replace('\\', '/', substr($backupDir, strlen($root) + 1)) . "\n";
echo "No routes, database, notification, notes, or audit files were modified.\n";
