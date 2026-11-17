<?php
/**
 * A7.4.11.2 safe file replacement installer.
 * Verifies all source versions first; refuses unknown edits; backs up before writing.
 * No data, migrations, or database connections are touched by this installer.
 */
declare(strict_types=1);
$root = dirname(__DIR__);
$expected = [
    'app/Http/Controllers/WorkspaceNotificationController.php' => 'ee5a6bb9ea27f2ddc1103004a086f9f44f5d4323f02f713b627117b08670e810',
    'app/Services/WorkspaceNotificationCenter.php' => 'a5f5b72fa716adc492fa9b718517290a8a132fb87b9d3c26d48e7e9335349f39',
    'app/Services/WorkspaceReminderSync.php' => '9b09fce5ee94ac5641fd202c78857eee3b9621306998a36cc42410d240ff0ba5',
    'routes/workspace_notifications.php' => 'fb850213edced7d45e4cce57a7becc8b36caf02fd771d27818d3159c64c032ec',
    'resources/views/workspace-notifications/_topbar.blade.php' => 'd9c4e08258d1608dd27c69a2cc3cdd028030ecfa2b072c2905be588fe98397d9',
    'resources/views/workspace-notifications/_style.blade.php' => '9f48a8f9b4ec3c925a443955127b8b16c4bc3cd5c826171e3e23f53d13910b5b',
    'resources/views/workspace-notifications/index.blade.php' => '6b28004308a7d274c9c911a7fc6945dbb9f46c34a9979ea3a4998d37a84aa89d',

];
$fail=static function (string $reason): never {
    fwrite(STDERR,"ERROR: {$reason}\nNo tracked files were changed by this run.\n");
    exit(1);
};
foreach ([
    'app/Models/WorkspaceNotificationPreference.php',
    'app/Services/WorkspaceNotificationPreferences.php',
    'database/migrations/2026_11_17_234000_create_workspace_notification_preferences.php',
    'resources/views/workspace-notifications/settings.blade.php',
    'resources/views/workspace-notifications/_bell_icon.blade.php',
    'public/js/workspace-notifications-poll.js',
    'tests/Feature/WorkspaceNotificationPreferencesHttpTest.php',
] as $required) {
    if (!is_file($root.'/'.$required)) $fail('Required new file missing: '.$required);
}
$changes=[];
foreach ($expected as $relative=>$originalHash) {
    $file=$root.'/'.$relative;
    $payload=$root.'/tools/a74112_payload/'.$relative;
    if (!is_file($file) || !is_file($payload)) $fail('Missing tracked file or payload: '.$relative);
    $current=hash_file('sha256',$file);
    $target=hash_file('sha256',$payload);
    if ($current === false || $target === false) $fail('Cannot hash '.$relative);
    if ($current === $target) {echo '[SKIP] Already updated: '.$relative.PHP_EOL;continue;}
    // A7.4.11.2 installer fix v2: A7.4.11.1 release reminder has a different
    // SHA-256 from the earlier internal fixture. Both are inspected and allowlisted.
    // Every other file still requires its original exact hash (or installed payload).
    $isReleasedReminder = $relative === 'app/Services/WorkspaceReminderSync.php'
        && hash_equals('82526d5fd0c8b538c0a499c1ce4fc137a6e644acb01c778a4c39f36d966e9ec3', $current);
    if (!hash_equals($originalHash,$current) && !$isReleasedReminder) {
        $fail('Unexpected local changes in '.$relative.'. Commit/backup your files and compare before patching.');
    }
    $changes[$relative]=$payload;
}
if (!$changes) {echo "[PASS] A7.4.11.2 already installed; nothing to replace.\n";exit(0);}
$backup=$root.'/tools/a74112_backups/'.date('Ymd-His').'-'.getmypid();
if (!mkdir($backup,0775,true) && !is_dir($backup)) $fail('Cannot create backup root');
foreach ($changes as $relative=>$payload) {
    $destination=$backup.'/'.$relative;
    if (!is_dir(dirname($destination)) && !mkdir(dirname($destination),0775,true)) {
        $fail('Cannot create backup path for '.$relative);
    }
    if (!copy($root.'/'.$relative,$destination)) $fail('Cannot back up '.$relative);
}
foreach ($changes as $relative=>$payload) {
    $target=$root.'/'.$relative;
    $temp=$target.'.a74112.tmp';
    if (!copy($payload,$temp) || !rename($temp,$target)) {
        fwrite(STDERR,'Write failed at '.$relative.'. Restore changed files from '.$backup.PHP_EOL);
        exit(1);
    }
    echo '[PASS] Updated: '.$relative.PHP_EOL;
}
echo '[PASS] A7.4.11.2 installed. Backup: '.str_replace($root.'/','',$backup).PHP_EOL;
echo "Run php artisan migrate, then quick check, runtime preflight and HTTP tests.\n";
