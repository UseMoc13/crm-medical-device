<?php
/** Static A7.6.4.2 verification; no CRM table queries or mutations. */
declare(strict_types=1);
$root=dirname(__DIR__);
$checks=[];
$check=static function(string $name,bool $passed)use(&$checks):void {
    $checks[]=$passed;
    echo '['.($passed?'PASS':'FAIL').'] '.$name.PHP_EOL;
};
$unit='tests/Unit/CrmDashboardAnnouncementSourceTest.php';
$feature='tests/Feature/CrmAnnouncementScheduleIsolatedTest.php';
$paths=[$unit,$feature,'tools/install_a7642_announcement_test_fix.php','tools/a7642_quick_check.php',
    'tools/a7642_payload/'.$unit,'tools/a7642_payload/'.$feature];
foreach($paths as $p) $check('File '.$p,is_file($root.'/'.$p));
$unitText=(string)@file_get_contents($root.'/'.$unit);
$featureText=(string)@file_get_contents($root.'/'.$feature);
$check('Windows case check uses scandir',str_contains($unitText,"scandir(\$root.'/app/Http/Controllers')"));
$check('Canonical case asserted',str_contains($unitText,"assertContains('ReportController.php', \$entries)"));
$check('Wrong-case entry rejected',str_contains($unitText,"assertNotContains('Reportcontroller.php', \$entries)"));
$check('Windows-sensitive assertion removed',!str_contains($unitText,'assertFileDoesNotExist('));
$check('Report file and class checks retained',str_contains($unitText,'assertFileExists(')&&str_contains($unitText,'ReportController\\b'));
$check('Fixture mirrors announcement timestamps',str_contains($featureText,'$table->timestamps();'));
$check('Fixture still uses disposable SQLite',str_contains($featureText,"'database' => ':memory:'"));
$check('Fixture requires test environment',str_contains($featureText,"app()->environment('testing')"));
$check('Read-only assertion preserved',str_contains($featureText,"self::assertSame([], \$writes)"));
$check('Publisher command assertion preserved',str_contains($featureText,"Artisan::call('crm:announcements:publish-due')"));
$check('No CRM migrations or resets added',!is_dir($root.'/tools/a7642_payload/database'));
foreach($paths as $p){
    $cmd=[];$rc=1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$p).' 2>&1',$cmd,$rc);
    $check('PHP syntax '.$p,$rc===0);
}
$passed=count(array_filter($checks));$total=count($checks);
echo "A7.6.4.2 static: {$passed}/{$total} PASS, ".($total-$passed).' FAIL.'.PHP_EOL;
exit($passed===$total?0:1);
