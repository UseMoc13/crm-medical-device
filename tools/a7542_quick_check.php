<?php
/** A7.5.4.2 code safety quick check. No Laravel bootstrap or DB access. */
declare(strict_types=1);
$root=dirname(__DIR__);
$fixtureRel='tests/Feature/CrmIntegrationAtomicityIsolatedTest.php';
$checkFile="$root/$fixtureRel";
$source=is_file($checkFile)?file_get_contents($checkFile):'';
$pre=is_file(__DIR__.'/a7542_http_preflight.php')?file_get_contents(__DIR__.'/a7542_http_preflight.php'):'';
$installer=is_file(__DIR__.'/install_a7542_qa.php')?file_get_contents(__DIR__.'/install_a7542_qa.php'):'';
$matrix=is_file("$root/docs/A7542_BROWSER_QA_MATRIX.md")?file_get_contents("$root/docs/A7542_BROWSER_QA_MATRIX.md"):'';
$n=0;$bad=0;
$check=static function (string $msg,bool $ok) use (&$n,&$bad): void {
  $n++;if(!$ok)$bad++; echo ($ok?'[PASS] ':'[FAIL] ').$msg.PHP_EOL;
};
$check('Isolated test installed',is_file($checkFile));
$check('Read-only preflight exists',$pre!=='');
$check('Manual browser matrix exists',$matrix!=='');
$check('Test enforces APP_ENV testing',str_contains($source,"environment('testing')"));
$check('Test creates isolated memory connection',str_contains($source,"'database' => ':memory:'"));
$check('Test restores previous connection in finally',str_contains($source,'finally {') && str_contains($source,'DB::setDefaultConnection($original)'));
$check('Test purges dedicated connection',str_contains($source,'DB::purge($name)'));
$check('Test covers forced rollback',str_contains($source,'intentional rollback') && str_contains($source,'DB::transaction('));
$check('Test asserts audit and inbox both gone after rollback',substr_count($source,"self::assertSame(0, DB::table('workspace_audit_events')->count());")===1 && substr_count($source,"self::assertSame(0, DB::table('workspace_notifications')->count());")===1);
$check('Test covers committed event',str_contains($source,'test_commit_persists_sanitized_status_event'));
$check('Test covers opt-out',str_contains($source,'self::assertFalse($this->notify($disabled'));
$check('Test covers audit metadata allowlist',str_contains($source,"'status_from' => 'pending'"));
$check('Preflight has no DB mutation',!preg_match('/(?:->|::)(?:create|insert|update|delete|truncate|drop|statement|unprepared)\s*\(/i',$pre));
$check('Preflight checks browser route methods',str_contains($pre,'->methods()') && str_contains($pre,"'workspace-notifications.open'=>['POST']"));
$check('Preflight checks notification default-off',str_contains($pre,"['crm_status_enabled'] === false"));
$check('Manual matrix includes denied access',str_contains($matrix,'Revoke B') && str_contains($matrix,'Access denied'));
$check('Manual matrix includes state rollback validation',str_contains($matrix,'Rejected') && str_contains($matrix,'rollback'));
$check('Installer additive only',str_contains($installer,"$".'tasks[$rel]=\'add\'') && !str_contains($installer,'file_put_contents($target'));
$check('Installer handles conflict',str_contains($installer,'refusing to overwrite'));
$check('No business controller changed',!str_contains($installer,'app/Http/Controllers/'));
foreach ([$fixtureRel,'tools/a7542_http_preflight.php','tools/a7542_quick_check.php','tools/install_a7542_qa.php'] as $rel) {
 $full="$root/$rel";
 if (!is_file($full)) {$check('PHP syntax '.$rel,false);continue;}
 $out=[];$status=1;
 exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($full).' 2>&1',$out,$status);
 $check('PHP syntax '.$rel,$status===0);
}
echo "\nA7.5.4.2 static: ".($n-$bad)."/$n PASS, $bad FAIL.\n";
exit($bad?1:0);
