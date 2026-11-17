<?php
/**
 * A7.4.10 safe, idempotent integration for the existing Big-Changes UI.
 * Existing module pages are patched only after all anchors are verified.
 * Originals are backed up to tools/a7410_backups/.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$patches = [];

function a7410_once(string $source, string $old, string $new, string $path): string
{
    if (str_contains($source, $new)) return $source;
    $count = substr_count($source, $old);
    if ($count !== 1) throw new RuntimeException("Unexpected anchor in {$path}: expected one, found {$count}.");
    return str_replace($old, $new, $source);
}

function a7410_last(string $source, string $suffix, string $insert, string $path): string
{
    if (str_contains($source, $insert)) return $source;
    $pos = strrpos($source, $suffix);
    if ($pos === false) throw new RuntimeException("Missing closing anchor in {$path}.");
    return substr($source, 0, $pos) . $insert . substr($source, $pos);
}

$patches['routes/web.php'] = function (string $s): string {
    return a7410_once($s, "require __DIR__ . '/calendar.php';", "require __DIR__ . '/calendar.php';\n\n// A7.4.10: private personal notes\nrequire __DIR__ . '/notes.php';", 'routes/web.php');
};
$patches['resources/views/layouts/app.blade.php'] = function (string $s): string {
    $p = 'resources/views/layouts/app.blade.php';
    $s = a7410_once($s, '    @stack(\'styles\')', "    @stack('styles')\n    @include('notes._style')", $p);
    return a7410_once($s, '<div class="top-actions">', "<div class=\"top-actions\">\n                    @include('notes._topbar')", $p);
};
$patches['app/Http/Controllers/WorkspaceCalendarController.php'] = function (string $s): string {
    $p = 'app/Http/Controllers/WorkspaceCalendarController.php';
    $s = a7410_once($s, 'use App\\Models\\WorkspaceCalendarEvent;', "use App\\Models\\WorkspaceCalendarEvent;\nuse App\\Models\\WorkspacePersonalNote;", $p);
    $old = '        $events = [];';
    $new = <<<'CODE'
        $events = [];

        // A7.4.10: only the owner's active reminders can be placed on Personal Calendar.
        if ($view !== 'work') {
            $noteReminders = WorkspacePersonalNote::query()
                ->where('owner_user_id', $owner)->whereNull('archived_at')
                ->whereNotNull('remind_at')->whereBetween('remind_at', [$start, $end])
                ->orderBy('remind_at')->limit(500)->get();
            foreach ($noteReminders as $note) {
                $events[] = [
                    'kind' => 'note', 'title' => $note->title,
                    'start' => $note->remind_at, 'end' => null,
                    'url' => route('notes.show', ['note' => $note->getKey()]),
                    'subtitle' => 'Private note reminder', 'all_day' => false,
                ];
            }
        }
CODE;
    return a7410_once($s, $old, $new, $p);
};
$patches['resources/views/calendar/index.blade.php'] = function (string $s): string {
    $p = 'resources/views/calendar/index.blade.php';
    $s = a7410_once($s, '<div class="actions"><a class="btn primary" href="{{ route(\'calendar.create\') }}">+ New Personal Event</a></div>', '<div class="actions"><a class="btn" href="{{ route(\'notes.create\') }}">+ New Personal Note</a><a class="btn primary" href="{{ route(\'calendar.create\') }}">+ New Personal Event</a></div>', $p);
    $s = a7410_once($s, '<div class="calendar-legend"><span><i class="calendar-dot activity"></i> Activity</span>', '<div class="calendar-legend"><span><i class="calendar-dot note"></i> Note Reminder</span><span><i class="calendar-dot activity"></i> Activity</span>', $p);
    $s = a7410_once($s, '<div class="calendar-day-dialog-foot"><a class="btn" id="calendarDayAdd" href="{{ route(\'calendar.create\') }}">+ New Personal Event</a>', '<div class="calendar-day-dialog-foot"><a class="btn" id="calendarDayNote" href="{{ route(\'notes.create\') }}">+ New Note</a><a class="btn" id="calendarDayAdd" href="{{ route(\'calendar.create\') }}">+ New Personal Event</a>', $p);
    $s = a7410_once($s, "    const add = document.getElementById('calendarDayAdd');", "    const add = document.getElementById('calendarDayAdd');\n    const noteAdd = document.getElementById('calendarDayNote');", $p);
    $s = a7410_once($s, '        add.href = `${base}?date=${encodeURIComponent(date)}`;', '        add.href = `${base}?date=${encodeURIComponent(date)}`;\n        noteAdd.href = `{{ route(\'notes.create\') }}?date=${encodeURIComponent(date)}`;', $p);
    return $s;
};
$patches['resources/views/calendar/show.blade.php'] = function (string $s): string {
    $p = 'resources/views/calendar/show.blade.php';
    return a7410_once($s, '<div class="calendar-confirm-backdrop" id="calendarConfirm" hidden>', "@include('notes._linked', ['linkType'=>'calendar', 'linkId'=>\$event->getKey()])\n<div class=\"calendar-confirm-backdrop\" id=\"calendarConfirm\" hidden>", $p);
};
$patches['resources/views/activities/show.blade.php'] = function (string $s): string {
    $p = 'resources/views/activities/show.blade.php';
    return a7410_last($s, '@endsection', "@if(\$isOwner)\n@include('notes._linked', ['linkType'=>'activity', 'linkId'=>\$activity->getKey()])\n@endif\n", $p);
};
$patches['resources/views/calendar/_style.blade.php'] = function (string $s): string {
    $p = 'resources/views/calendar/_style.blade.php';
    $css = <<<'CSS'
/* A7.4.10 Private note reminders; keep Calendar's existing palette and single stylesheet. */
.calendar-dot.note{background:#8b6ac6}.calendar-event.note{background:#f4efff;color:#6c4b9c;border-color:#8b6ac6}.calendar-timeline-bar.note{background:#8b6ac6}.calendar-timeline-kind.note{color:#7152aa;background:#f3efff}
CSS;
    return a7410_last($s, '</style>', "\n" . $css . "\n", $p);
};

try {
    $writes = [];
    foreach ($patches as $relative => $transform) {
        $file = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (!is_file($file)) throw new RuntimeException("Missing file: {$relative}. No files changed.");
        $old = file_get_contents($file);
        if ($old === false) throw new RuntimeException("Cannot read: {$relative}. No files changed.");
        $new = $transform($old);
        if ($new !== $old) $writes[$relative] = ['before' => $old, 'after' => $new];
    }
    if (!$writes) { echo "Already installed. No changes required.\n"; exit(0); }

    $stamp = date('Ymd-His') . '-' . getmypid();
    $backupBase = $root . '/tools/a7410_backups/' . $stamp;
    foreach ($writes as $relative => $parts) {
        $backupPath = $backupBase . '/' . $relative;
        if (!is_dir(dirname($backupPath)) && !mkdir(dirname($backupPath), 0775, true) && !is_dir(dirname($backupPath))) {
            throw new RuntimeException("Cannot create backup directory for {$relative}.");
        }
        if (file_put_contents($backupPath, $parts['before']) === false) {
            throw new RuntimeException("Cannot back up {$relative}. No original files changed.");
        }
    }
    foreach ($writes as $relative => $parts) {
        $file = $root . '/' . $relative;
        if (file_put_contents($file, $parts['after'], LOCK_EX) === false) {
            throw new RuntimeException("Failed to write {$relative}. Restore from {$backupBase} before retrying.");
        }
        echo "Integrated: {$relative}\n";
    }
    echo "Complete. Original files backed up to tools/a7410_backups/{$stamp}\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . "\n");
    exit(1);
}
