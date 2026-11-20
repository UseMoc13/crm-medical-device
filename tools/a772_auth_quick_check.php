<?php

declare(strict_types=1);

/** A7.7.2 static checks — no Laravel boot, no database/cache reads or writes. */
$root = dirname(__DIR__);
$files = [
    'app/Http/Controllers/Auth/CrmLoginController.php',
    'app/Services/CrmLoginThrottlePolicy.php',
    'app/Http/Middleware/EnsureCrmAuthenticated.php',
    'tests/Unit/CrmLoginThrottlePolicyTest.php',
    'tests/Unit/CrmLoginThrottleSourceTest.php',
    'tests/Feature/CrmLoginThrottleHttpIsolationTest.php',
    'tools/a772_policy_smoke.php',
];
$passed = 0;
$failed = 0;
$check = static function (string $label, bool $ok) use (&$passed, &$failed): void {
    echo ($ok ? '[PASS] ' : '[FAIL] '), $label, PHP_EOL;
    $ok ? ++$passed : ++$failed;
};
foreach ($files as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $exists = is_file($path);
    $check('File: '.$relative, $exists);
    if (!$exists) {
        continue;
    }
    $command = escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1';
    $output = [];
    $code = 1;
    exec($command, $output, $code);
    $check('PHP syntax: '.$relative, $code === 0);
}
$controllerPath = $root.'/app/Http/Controllers/Auth/CrmLoginController.php';
$policyPath = $root.'/app/Services/CrmLoginThrottlePolicy.php';
$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$policy = is_file($policyPath) ? (string) file_get_contents($policyPath) : '';
$check('Both login limiter keys registered', str_contains($controller, 'CrmLoginThrottlePolicy::keys('));
$check('Per email+IP limit retained', str_contains($controller, 'RateLimiter::tooManyAttempts($pairKey,'));
$check('Independent per identity limit added', str_contains($controller, 'RateLimiter::tooManyAttempts($identityKey,'));
$check('Both keys increment on failure and inactive login', substr_count($controller, 'RateLimiter::hit($pairKey,') === 2 && substr_count($controller, 'RateLimiter::hit($identityKey,') === 2);
$check('Both keys clear on successful login', str_contains($controller, 'RateLimiter::clear($pairKey);') && str_contains($controller, 'RateLimiter::clear($identityKey);'));
$check('Session regeneration and inactive logout retained', str_contains($controller, '$request->session()->regenerate();') && str_contains($controller, '$request->session()->invalidate();'));
$check('No new controller DB mutations', !str_contains($controller, 'DB::') && !str_contains($controller, 'Schema::'));
$check('No email/IP plaintext in limiter keys', str_contains($policy, "hash('sha256',") && !str_contains($policy, "'crm-login:' . \$normalized"));
$check('Original login rate limits unchanged', str_contains($policy, 'PAIR_LIMIT = 5;') && str_contains($policy, 'PAIR_DECAY_SECONDS = 60;'));
$check('Isolated HTTP test uses SQLite memory only', str_contains((string) @file_get_contents($root.'/tests/Feature/CrmLoginThrottleHttpIsolationTest.php'), "'database' => ':memory:'"));
echo "A7.7.2 static: {$passed}/".($passed+$failed)." PASS; {$failed} FAIL. No DB used.", PHP_EOL;
exit($failed ? 1 : 0);
