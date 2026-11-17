<?php
/**
 * A7.4.10.3 — safe replacement installer (no DB actions).
 * All replacement source files are bundled under tools/a74103_payload/.
 * Copies a timestamped backup of every existing file before writing.
 */
declare(strict_types=1);
$root = dirname(__DIR__);
$payload = __DIR__.'/a74103_payload';
$files = [
    'app/Http/Controllers/WorkspacePersonalNoteController.php',
    'resources/views/notes/_floating.blade.php',
    'resources/views/notes/_form.blade.php',
    'resources/views/notes/_linked.blade.php',
    'resources/views/notes/_rich_runtime.blade.php',
    'resources/views/notes/_style.blade.php',
    'resources/views/notes/create.blade.php',
    'resources/views/notes/edit.blade.php',
    'resources/views/notes/index.blade.php',
    'resources/views/notes/show.blade.php',
    'public/js/crm-notes-rich-editor.js',
    'tests/Feature/WorkspacePersonalNoteHttpTest.php',
];
if (!is_file($root.'/artisan') || !is_file($root.'/routes/notes.php') ||
    !str_contains((string)@file_get_contents($root.'/routes/notes.php'), 'notes.floating.save') ||
    !str_contains((string)@file_get_contents($root.'/resources/views/layouts/app.blade.php'), "notes._floating")) {
    fwrite(STDERR,"[FAIL] A7.4.10.1 floating Notes must be installed first. Nothing changed.\n");exit(1);
}
$expected = [
    'resources/views/notes/_floating.blade.php' => 'crmNotesBody',
    'resources/views/notes/_form.blade.php' => 'personalNoteBody',
    'resources/views/notes/_style.blade.php' => 'notes-floating-workspace',
    'app/Http/Controllers/WorkspacePersonalNoteController.php' => 'class WorkspacePersonalNoteController',
    'tests/Feature/WorkspacePersonalNoteHttpTest.php' => 'class WorkspacePersonalNoteHttpTest',
];
foreach ($files as $path) {
    if (!is_file($payload.'/'.$path)) {
        fwrite(STDERR,"[FAIL] Patch payload missing {$path}. Nothing changed.\n");exit(1);
    }
    $target=$root.'/'.$path;
    if (isset($expected[$path]) && (!is_file($target) ||
        !str_contains((string)file_get_contents($target),$expected[$path]))) {
        fwrite(STDERR,"[FAIL] Unsupported/missing base file {$path}. Nothing changed.\n");exit(1);
    }
    if (is_file($target) && !is_writable($target)) {
        fwrite(STDERR,"[FAIL] Target is not writable {$path}. Nothing changed.\n");exit(1);
    }
}
$changes = [];
foreach ($files as $path) {
    $target=$root.'/'.$path;$source=$payload.'/'.$path;
    if (!is_file($target) || hash_file('sha256',$target)!==hash_file('sha256',$source)) $changes[]=$path;
}
if (!$changes) {echo "[PASS] A7.4.10.3 already installed. No duplicated changes.\n";exit(0);}
$stamp=date('Ymd-His').'-'.getmypid();
$backup=$root.'/tools/a74103_backups/'.$stamp;
$original = [];
foreach ($changes as $path) {
    $target=$root.'/'.$path;
    $original[$path]=is_file($target)?file_get_contents($target):null;
    if ($original[$path]===false) {fwrite(STDERR,"[FAIL] Could not read {$path}.\n");exit(1);}
    if ($original[$path]!==null) {
        $dest=$backup.'/'.$path;
        if (!is_dir(dirname($dest)) && !mkdir(dirname($dest),0775,true) && !is_dir(dirname($dest))) {
            fwrite(STDERR,"[FAIL] Could not create backup folder. Nothing changed.\n");exit(1);
        }
        if (file_put_contents($dest,$original[$path],LOCK_EX)===false) {
            fwrite(STDERR,"[FAIL] Could not back up {$path}. Nothing changed.\n");exit(1);
        }
    }
}
$written=[];
foreach ($changes as $path) {
    $target=$root.'/'.$path;
    if (!is_dir(dirname($target)) && !mkdir(dirname($target),0775,true) && !is_dir(dirname($target))) break;
    $bytes=file_get_contents($payload.'/'.$path);
    if ($bytes===false || file_put_contents($target,$bytes,LOCK_EX)===false) break;
    $written[]=$path;
}
if (count($written)!==count($changes)) {
    foreach ($written as $path) {
        if ($original[$path]===null) @unlink($root.'/'.$path);
        else file_put_contents($root.'/'.$path,$original[$path],LOCK_EX);
    }
    fwrite(STDERR,"[FAIL] File write failed. Applied files restored from the in-memory backup.\n");exit(1);
}
foreach ($written as $path) echo "Integrated: {$path}\n";
echo "[PASS] A7.4.10.3 installed. Backup: tools/a74103_backups/{$stamp}\n";
echo "No database migration or reset is needed.\n";
