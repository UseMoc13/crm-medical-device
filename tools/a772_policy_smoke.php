<?php

declare(strict_types=1);

/** Standalone PHP smoke test: no vendor, Laravel, database or cache required. */
require_once dirname(__DIR__).'/app/Services/CrmLoginThrottlePolicy.php';

use App\Services\CrmLoginThrottlePolicy as Policy;

$passes = 0;
$failures = 0;
$check = static function (string $name, bool $result) use (&$passes, &$failures): void {
    echo ($result ? '[PASS] ' : '[FAIL] '), $name, PHP_EOL;
    $result ? ++$passes : ++$failures;
};
$a = Policy::keys(' User@Example.Test ', '192.0.2.1');
$b = Policy::keys('user@example.test', '192.0.2.2');
$c = Policy::keys('user@example.test', '192.0.2.1');
$d = Policy::keys('other@example.test', '192.0.2.1');
$check('identity stays stable after IP rotation', $a['identity'] === $b['identity']);
$check('pair key rotates when IP changes', $a['pair'] !== $b['pair']);
$check('case and whitespace normalized', $a === $c);
$check('different identity creates different key', $a['identity'] !== $d['identity']);
$check('identity key hashed, no user data', str_starts_with($a['identity'], 'crm-login-identity:') && !str_contains($a['identity'], 'user@'));
$check('pair key hashed, no IP data', str_starts_with($a['pair'], 'crm-login:') && !str_contains($a['pair'], '192.0.2.1'));
$check('original limit unchanged', Policy::PAIR_LIMIT === 5 && Policy::PAIR_DECAY_SECONDS === 60);
$check('identity limit bounded', Policy::IDENTITY_LIMIT === 20 && Policy::IDENTITY_DECAY_SECONDS === 300);
$check('empty IP defaults consistently', Policy::keys('user@example.test', '')['pair'] === Policy::keys('user@example.test', 'unknown')['pair']);
$blankRejected = false;
try {
    Policy::keys('  ', '192.0.2.1');
} catch (InvalidArgumentException) {
    $blankRejected = true;
}
$check('blank identity rejected', $blankRejected);
echo "A7.7.2 policy smoke: {$passes}/".($passes+$failures)." PASS; {$failures} FAIL. No DB.", PHP_EOL;
exit($failures ? 1 : 0);
