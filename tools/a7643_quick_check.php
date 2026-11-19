<?php
/** A7.6.4.3 read-only static checks. */
declare(strict_types=1);
$root = dirname(__DIR__);
$path = $root.'/tests/Feature/CrmAnnouncementScheduleIsolatedTest.php';
$install = __DIR__.'/install_a7643_command_test_fix.php';
$payload = __DIR__.'/a7643_payload/tests/Feature/CrmAnnouncementScheduleIsolatedTest.php';
$checks = [];
$check = static function (string $label, bool $valid) use (&$checks): void {
    $checks[] = $valid;
    echo '['.($valid ? 'PASS' : 'FAIL').'] '.$label.PHP_EOL;
};
$check('Current isolated fixture exists', is_file($path));
$check('Installer exists', is_file($install));
$check('Guarded payload exists', is_file($payload));
$s = is_file($path) ? str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($path)) : '';
$check('No Artisan Kernel call in fixture', !str_contains($s, "Artisan::call('crm:announcements:publish-due')"));
$check('Checks dedicated SQLite default', str_contains($s, "DB::getDefaultConnection()"));
$check('Checks Eloquent model connection', str_contains($s, "(new Announcement())->getConnection()->getName()"));
$check('Uses command instance', str_contains($s, 'app(PublishDueCrmAnnouncements::class)'));
$check('Binds Laravel container', str_contains($s, '$command->setLaravel(app());'));
$check('Uses Symfony command runner', str_contains($s, '$command->run(new ArrayInput([]), new BufferedOutput())'));
$check('Fixtures remain memory-only', str_contains($s, "'database' => ':memory:'"));
$check('Environment testing required', str_contains($s, "app()->environment('testing')"));
$check('Realistic timestamps retained', str_contains($s, '$table->timestamps();'));
$check('Dashboard read-only assertion retained', str_contains($s, 'self::assertSame([], $writes)'));
$check('Due item published assertion retained', str_contains($s, "self::assertSame('published'"));
$check('Future item stays scheduled', str_contains($s, "self::assertSame('scheduled'"));
$check('No migration or seed in test', !preg_match('/artisan\s+(migrate|db:seed)|migrate:fresh|migrate:refresh|\$this->seed\(/i',$s));
$check('Isolated connection restored in finally', str_contains($s, 'DB::setDefaultConnection($previous);'));
$check('Installer checks normalized SHA-256', str_contains((string) file_get_contents($install), "hash('sha256', a7643_lf(\$before))"));
foreach ([$path,$install,$payload,__FILE__] as $phpFile) {
    $output=[];$code=1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($phpFile).' 2>&1',$output,$code);
    $check('PHP syntax: '.basename($phpFile), $code===0);
}
$passed=count(array_filter($checks));
$total=count($checks);
echo PHP_EOL.'A7.6.4.3 static: '.$passed.'/'.$total.' PASS, '.($total-$passed).' FAIL.'.PHP_EOL;
exit($passed===$total ? 0 : 1);
