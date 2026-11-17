<?php
/** Safe A7.4.10.1-4 insertion into CRM master Blade layout. No migration or DB writes. */
declare(strict_types=1);

$root = dirname(__DIR__);
$file = $root.'/resources/views/layouts/app.blade.php';
if (!is_file($file)) {fwrite(STDERR,"[FAIL] Missing CRM layout.\n");exit(1);}
$original = file_get_contents($file);
if ($original === false || !str_contains($original, "@include('notes._topbar')")) {
    fwrite(STDERR,"[FAIL] A7.4.10 Notes not installed. Install that package first; no changes made.\n");exit(1);
}
$partial = "    @include('notes._floating')";
if (str_contains($original, $partial)) {echo "[PASS] Global floating Notes already integrated. No duplicate changes.\n";exit(0);}
if (substr_count($original, '</body>') !== 1) {
    fwrite(STDERR,"[FAIL] Expected exactly one layout body end; no files changed.\n");exit(1);
}
$updated = str_replace('</body>', $partial."\n\n</body>", $original);
$stamp = date('Ymd-His').'-'.getmypid();
$backup = $root.'/tools/a74101_backups/'.$stamp.'/resources/views/layouts/app.blade.php';
if (!is_dir(dirname($backup)) && !mkdir(dirname($backup),0775,true) && !is_dir(dirname($backup))) {
    fwrite(STDERR,"[FAIL] Cannot create backup path; no changes made.\n");exit(1);
}
if (file_put_contents($backup,$original,LOCK_EX)===false) {
    fwrite(STDERR,"[FAIL] Cannot back up original layout; no changes made.\n");exit(1);
}
if (file_put_contents($file,$updated,LOCK_EX)===false) {
    fwrite(STDERR,"[FAIL] Failed to patch layout; restore from {$backup}.\n");exit(1);
}
echo "[PASS] Global floating Notes integrated into resources/views/layouts/app.blade.php\n";
echo "Backup: tools/a74101_backups/{$stamp}\n";
