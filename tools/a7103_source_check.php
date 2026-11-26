<?php
/** A7.10.3 no-DB source sanity check, no files are modified. */
declare(strict_types=1);
$root = dirname(__DIR__);
$needles = [
    'app/Support/CrmViewAction.php' => ['CrmRouteAuthorizationPolicy', 'CrmPermissionResolver', "'active'", 'a7103_role_actions', 'isSuperAdmin'],
    'tools/a7103_crud_ui_installer.php' => ['--check', '--apply', '--rollback=', 'a7103_transform', 'A7103_ROLE_AWARE_UI_APPLIED', 'A7103_ALREADY_INSTALLED'],
    'tests/Feature/CrmRoleAwareCrudVisibilityTest.php' => ['test_view_only_user_sees_details_but_not_create_edit_delete', 'test_status_workflow_requires_extra_permission'],
    'tests/Unit/CrmViewActionRouteGuardTest.php' => ['test_crm_crud_and_workflow_routes_are_covered'],
    'docs/A7103_START_HERE.md' => ['Backend authorization', 'PowerShell', 'Rollback'],
];
$n=0;
foreach ($needles as $rel=>$strings) {
    $path = $root.'/'.$rel;
    $body = is_file($path) ? file_get_contents($path) : false;
    if ($body === false) {fwrite(STDERR, '[FAIL] Missing '.$rel.PHP_EOL);exit(1);}
    foreach ($strings as $needle) {
        if (!str_contains($body,$needle)) {fwrite(STDERR, '[FAIL] Missing needle '.$rel.' '.$needle.PHP_EOL);exit(1);}
        $n++;
    }
    if (str_ends_with($rel, '.php')) {
        $o=[];$rc=0;exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1', $o, $rc);
        if ($rc !== 0) {fwrite(STDERR, '[FAIL] PHP syntax '.$rel.' '.implode(' ',$o).PHP_EOL);exit(1);}
        $n++;
    }
    echo '[PASS] '.$rel.PHP_EOL;
}
$viewBase=$root.'/resources/views';
if (!is_dir($viewBase)) {fwrite(STDERR, '[FAIL] Missing resources/views'.PHP_EOL);exit(1);}
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewBase, FilesystemIterator::SKIP_DOTS));
$open=$close=$files=0;
foreach($it as $f){
    if(!$f->isFile()||!str_ends_with($f->getFilename(),'.blade.php'))continue;
    $s=(string)file_get_contents($f->getPathname());
    $a=preg_match_all('/\{\{-- A7103:[A-Za-z0-9_.-]+ --\}\}/', $s);
    $b=preg_match_all('/\{\{-- \/A7103 --\}\}/', $s);
    if ($a!==$b) {fwrite(STDERR,'[FAIL] Mismatched action guards in '.$f->getPathname().PHP_EOL);exit(1);}
    if($a){$files++;$open+=$a;$close+=$b;}
}
echo '[CHECK] installed_guards='.$open.' installed_files='.$files.' (0 is expected before --apply)'.PHP_EOL;
echo '[RESULT] A7103_SOURCE_READY checks='.$n.'; database and HTTP not executed'.PHP_EOL;
