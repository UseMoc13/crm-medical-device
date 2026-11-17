<?php
/** A7.4.10 static / read-only smoke checks. */
declare(strict_types=1);
$root = dirname(__DIR__);
$checks = [
    'Personal Note controller' => ['app/Http/Controllers/WorkspacePersonalNoteController.php', 'class WorkspacePersonalNoteController'],
    'UUID model' => ['app/Models/WorkspacePersonalNote.php', 'use HasUuids;'],
    'Activity linkage migration' => ['database/migrations/2026_11_17_210000_link_workspace_personal_notes_to_activities.php', "'activity_id'"],
    'Private note routes' => ['routes/notes.php', "Route::resource('notes'"],
    'Routes registered' => ['routes/web.php', "require __DIR__ . '/notes.php'"],
    'Topbar notes' => ['resources/views/layouts/app.blade.php', "@include('notes._topbar')"],
    'One Notes stylesheet' => ['resources/views/notes/_style.blade.php', '<style>'],
    'Notes index' => ['resources/views/notes/index.blade.php', 'My Notes'],
    'Notes create' => ['resources/views/notes/create.blade.php', "@include('notes._form')"],
    'Notes edit' => ['resources/views/notes/edit.blade.php', "@include('notes._form')"],
    'Notes details' => ['resources/views/notes/show.blade.php', 'Related Workspace'],
    'Notes linked card' => ['resources/views/notes/_linked.blade.php', 'owner_user_id'],
    'Activity private notes' => ['resources/views/activities/show.blade.php', "'linkType'=>'activity'"],
    'Calendar private notes' => ['resources/views/calendar/show.blade.php', "'linkType'=>'calendar'"],
    'Calendar note reminders' => ['app/Http/Controllers/WorkspaceCalendarController.php', "'kind' => 'note'"],
    'Calendar day quick add' => ['resources/views/calendar/index.blade.php', 'calendarDayNote'],
    'Calendar single stylesheet' => ['resources/views/calendar/_style.blade.php', '.calendar-event.note'],
    'Owner scoped CRUD' => ['app/Http/Controllers/WorkspacePersonalNoteController.php', "where('owner_user_id', \$this->actor())"],
    'Owner scoped link validation' => ['app/Http/Controllers/WorkspacePersonalNoteController.php', "Rule::exists('activities'"],
    'Private topbar query' => ['resources/views/notes/_topbar.blade.php', "where('owner_user_id'"],
    'Isolated feature tests' => ['tests/Feature/WorkspacePersonalNoteHttpTest.php', "':memory:'"],
];
$passed = 0;
foreach ($checks as $name => [$path, $needle]) {
    $file = $root . '/' . $path;
    $good = is_file($file) && str_contains((string)file_get_contents($file), $needle);
    echo '[' . ($good ? 'PASS' : 'FAIL') . "] {$name}\n";
    if ($good) $passed++;
}
echo "\nA7.4.10 static: {$passed}/" . count($checks) . " PASS\n";
if ($passed !== count($checks)) exit(1);
