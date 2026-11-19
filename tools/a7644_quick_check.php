<?php
/** A7.6.4.4 static source validation without DB or Laravel bootstrap. */
declare(strict_types=1);
$root = dirname(__DIR__);
$relative = 'tests/Feature/CrmAnnouncementScheduleIsolatedTest.php';
$target = $root.'/'.$relative;
$payload = __DIR__.'/a7644_payload/'.$relative;
$installer = __DIR__.'/install_a7644_bulk_insert_test_fix.php';
$checks=[];
$check = static function (string $message, bool $ok) use (&$checks): void {
    $checks[]=$ok;
    echo '['.($ok ? 'PASS' : 'FAIL').'] '.$message.PHP_EOL;
};
$source = is_file($target) ? str_replace(["\r\n","\r"],"\n",(string)file_get_contents($target)) : '';
$check('Target PHPUnit fixture exists',is_file($target));
$check('Guarded payload exists',is_file($payload));
$check('Installer exists',is_file($installer));
$check('Fixture uses SQLite memory only',str_contains($source,"'database' => ':memory:'"));
$check('Environment restricted to testing',str_contains($source,"app()->environment('testing')"));
$check('Fixture defines created_at and updated_at',str_contains($source,'$table->timestamps();'));
$check('Exactly three bulk insert schedule keys',substr_count($source,"'scheduled_at' =>") === 3);
$check('Exactly three bulk insert publication keys',substr_count($source,"'published_at' =>") === 3);
$check('Published item has null schedule',str_contains($source,"'scheduled_at' => null, 'published_at' => \$now->copy()->subDay()"));
$check('Due item has null publication',str_contains($source,"'scheduled_at' => \$now->copy()->subHour(), 'published_at' => null"));
$check('Future item has null publication',str_contains($source,"'scheduled_at' => \$now->copy()->addDay(), 'published_at' => null"));
$check('Due fixture validated before command',str_contains($source,"where('scheduled_at', '<=', \$now)->exists()"));
$check('Future fixture validated before command',str_contains($source,"where('scheduled_at', '>', \$now)->exists()"));
$check('Checks Eloquent memory DB',str_contains($source,'(new Announcement())->getConnection()->getName()'));
$check('Uses direct publisher command',str_contains($source,'$command->run(new ArrayInput([]), new BufferedOutput())'));
$check('Due item must become published',str_contains($source,"self::assertSame('published', DB::table('announcements')"));
$check('Future item must remain scheduled',str_contains($source,"self::assertSame('scheduled', DB::table('announcements')"));
$check('Dashboard read-only check retained',str_contains($source,'self::assertSame([], $writes)'));
$check('Connection restored in finally',str_contains($source,'DB::setDefaultConnection($previous);'));
$check('No DB migration or seed in test',!preg_match('/migrate:fresh|migrate:refresh|artisan\s+(migrate|db:seed)|\$this->seed\(/i', $source));
$check('Installer verifies known source fingerprint',str_contains((string)file_get_contents($installer),'$beforeHash !== $oldHash'));
$check('Installer creates backup',str_contains((string)file_get_contents($installer),'a7644_backups'));
$check('Installer offers dry-run',str_contains((string)file_get_contents($installer),'--dry-run'));
foreach ([$target,$payload,$installer,__FILE__] as $p) {
    $out=[];$rc=1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($p).' 2>&1',$out,$rc);
    $check('PHP syntax '.basename($p),$rc===0);
}
$passed=count(array_filter($checks));$total=count($checks);
echo PHP_EOL.'A7.6.4.4 static: '.$passed.'/'.$total.' PASS, '.($total-$passed).' FAIL.'.PHP_EOL;
exit($passed===$total?0:1);
