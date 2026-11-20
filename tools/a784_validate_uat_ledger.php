<?php
/** A7.8.4 opt-in file audit; NO Laravel bootstrap, DB, HTTP or writes. */
declare(strict_types=1);
$root = dirname(__DIR__);
require_once $root.'/app/Support/CrmUatWorkflowCoverage.php';
require_once $root.'/app/Support/CrmUatExecutionLedger.php';
use App\Support\CrmUatExecutionLedger;

if ($argc !== 2 || !str_starts_with($argv[1], '--ledger=')) {
    fwrite(STDERR, "Usage: php tools/a784_validate_uat_ledger.php --ledger=path/to/ledger.json\n");
    exit(2);
}
$path = substr($argv[1], strlen('--ledger='));
if ($path === '' || !is_file($path) || !is_readable($path)) {
    fwrite(STDERR, "[FAIL] Cannot read ledger file.\n");
    exit(2);
}
try {
    if (filesize($path) === false || filesize($path) > 262144) throw new RuntimeException('Ledger too large.');
    $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $report = CrmUatExecutionLedger::evaluate($data);
    echo "A7.8.4 Manual UAT Register Validation [READ ONLY]\n";
    echo '[RESULT] total='.$report['total_scenarios'].' not_run='.$report['not_run']
        .' pass='.$report['passed'].' fail='.$report['failed'].' blocked='.$report['blocked']
        .' policy_holds='.$report['policy_holds'].' decision='.$report['decision'].PHP_EOL;
    echo '[LIMIT] Evidence references are self-reported, not independently verified. Manual UAT and release signoff are distinct.' . PHP_EOL;
    echo '[SAFE] No names, role strings, observed results, URLs, file references or IDs printed. No DB, HTTP or writes.' . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, "[FAIL] Invalid UAT ledger: ".$e->getMessage()."\n");
    exit(1);
}
exit(0);
