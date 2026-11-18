<?php
/** A7.5.2.1.1 static: test expectation and privacy guards. */
declare(strict_types=1);
$root=dirname(__DIR__);
$path=$root.'/tests/Feature/CrmIntegrationInvestigationCommandTest.php';
$installer=$root.'/tools/install_a75211_investigation_test.php';
$src=is_file($path) ? (file_get_contents($path) ?: '') : '';
$ins=is_file($installer) ? (file_get_contents($installer) ?: '') : '';
$checks=[
 'Investigation command HTTP test installed'=>is_file($path),
 'Test captures Artisan output exactly once'=>substr_count($src,'Artisan::output()')===2, // one mention is comment
 'Test decodes captured JSON'=>str_contains($src,'json_decode($output'),
 'Test checks query errors explicitly'=>str_contains($src,"['errors']"),
 'Test accepts only real missing schema'=>str_contains($src,'Schema::hasTable') && str_contains($src,'Schema::hasColumn'),
 'Test verifies skipped report count'=>str_contains($src,"['skipped']"),
 'Test requires nonzero exit on skips'=>str_contains($src,'$skipped === [] ? 0 : 1'),
 'No report details or record identifiers'=>str_contains($src,"assertArrayNotHasKey('details'") && str_contains($src,"assertArrayNotHasKey('record_id'"),
 'Privacy check distinguishes schema name from serial value'=>str_contains($src, "'\"serial_number\":'"),
 'Read-only database listener test retained'=>str_contains($src,'test_scan_issues_no_database_writes') && str_contains($src,'DB::listen'),
 'Guarded installer requires original hash'=>str_contains($ins,'$currentSha !== $oldSha'),
 'Installer backs up original'=>str_contains($ins,'copy($target, $backup)'),
 'No schema or production files in payload'=>!is_dir($root.'/tools/a75211_payload/app') && !is_dir($root.'/tools/a75211_payload/database'),
];
foreach([$path,$installer] as $php){$out=[];$rc=1;exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($php).' 2>&1',$out,$rc);$checks['PHP syntax: '.basename($php)]=$rc===0;}
$pass=0;
foreach($checks as $label=>$ok){if($ok)$pass++;echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;}
echo "\nA7.5.2.1.1 static: $pass/".count($checks)." PASS, ".(count($checks)-$pass)." FAIL.\n";
exit($pass===count($checks)?0:1);
