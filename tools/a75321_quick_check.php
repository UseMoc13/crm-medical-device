<?php
/** Source and syntax checks for A7.5.3.2.1. */

declare(strict_types=1);

$base = dirname(__DIR__);
$path = $base . '/tests/Unit/CrmStatusNotificationSourceTest.php';
$checks = [];
$check = static function (string $name, bool $pass) use (&$checks): void {
    $checks[] = $pass;
    echo ($pass ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
};
$check('Notification source test exists', is_file($path));
$src = is_file($path) ? (string) file_get_contents($path) : '';
$check('Literal permission assertion (no interpolation)', str_contains($src, <<<'LITERAL'
->allows($userId, \'crm_status\', $type)
LITERAL
));
$check('Previous interpolating assertion absent', !str_contains($src, <<<'BROKEN'
"->allows($userId, 'crm_status', $type)"
BROKEN
));
$check('Preference default-off assertion retained', str_contains($src, <<<'LITERAL'
assertFalse($defaults['crm_status_enabled'])
LITERAL
));
$check('Privacy assertion retained', str_contains($src, "assertStringNotContainsString('customer_name'"));
$check('Authorization assertion retained', str_contains($src, "'CrmNavigationAccess::class'"));
$check('Transaction assertion retained', str_contains($src, "'DB::transaction('"));
$check('No migration/seed invocation added', !preg_match('/Artisan::call\s*\(\s*[\'\"](?:migrate|db:seed)/', $src));
$php = PHP_BINARY;
$command = escapeshellarg($php) . ' -l ' . escapeshellarg($path) . ' 2>&1';
$output = [];
$exit = 1;
exec($command, $output, $exit);
$check('PHP syntax source test', $exit === 0);
$check('Existing test class name retained', str_contains($src, 'final class CrmStatusNotificationSourceTest'));
$fail = count(array_filter($checks, fn (bool $value): bool => !$value));
printf("A7.5.3.2.1 static: %d/%d PASS, %d FAIL.\n", count($checks)-$fail, count($checks), $fail);
exit($fail ? 1 : 0);
