<?php
/** A7.10.7.2 source-only validation. Does NOT access business database. */
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [
    'app/Support/CrmPgLoopbackEndpointPolicy.php' => ['final class CrmPgLoopbackEndpointPolicy', 'FILTER_FLAG_IPV4', "'32'", "'128'", "inet_pton('::1')"],
    'tools/a71072_workspace_audit_permission.php' => ['CrmPgLoopbackEndpointPolicy::isLoopback', '--apply', 'A7107-ONE-PERMISSION', 'insertOrIgnore', 'A7107_AUDIT_PERMISSION_CREATED', 'Workspace Audit migration'],
    'tests/Unit/CrmPgLoopbackEndpointPolicyTest.php' => ['test_postgresql_loopback_addresses_are_accepted', 'test_non_loopback_and_broad_masks_are_rejected'],
    'docs/A71072_START_HERE.md' => ['127.0.0.1/32', 'rollback', '--check', '--apply'],
];
$count=0;
foreach ($checks as $file=>$needles) {
    $path=$root.'/'.$file;
    $content=is_file($path) ? file_get_contents($path) : false;
    if ($content===false) {
        fwrite(STDERR,'[FAIL] Missing '.$file.PHP_EOL); exit(1);
    }
    foreach($needles as $needle) {
        if (!str_contains($content,$needle)) {
            fwrite(STDERR,'[FAIL] '.$file.' missing '.$needle.PHP_EOL); exit(1);
        }
        $count++;
    }
    if (str_ends_with($file,'.php')) {
        $cmd=escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1';
        $out=[];$rc=0;
        exec($cmd,$out,$rc);
        if ($rc!==0) {fwrite(STDERR,'[FAIL] lint '.$file.' '.implode(' ',$out).PHP_EOL);exit(1);}
        $count++;
    }
    echo '[PASS] '.$file.PHP_EOL;
}
require $root.'/app/Support/CrmPgLoopbackEndpointPolicy.php';
foreach (['127.0.0.1/32','::1/128'] as $valid) {
    if (!\App\Support\CrmPgLoopbackEndpointPolicy::isLoopback($valid)) {fwrite(STDERR,'[FAIL] loopback '.$valid.PHP_EOL);exit(1);}
    $count++;
}
foreach (['127.0.0.1/24','127.0.0.2/32','192.168.1.10/32','::1/64','::ffff:127.0.0.1/128'] as $invalid) {
    if (\App\Support\CrmPgLoopbackEndpointPolicy::isLoopback($invalid)) {fwrite(STDERR,'[FAIL] unsafe address '.$invalid.PHP_EOL);exit(1);}
    $count++;
}
echo '[RESULT] A71072_SOURCE_READY checks='.$count.' (Laravel/PostgreSQL NOT accessed)'.PHP_EOL;
