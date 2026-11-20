<?php
/** Pure, no Laravel bootstrap and no database. */
declare(strict_types=1);
require dirname(__DIR__).'/app/Support/CrmQueryPlanDigest.php';

use App\Support\CrmQueryPlanDigest;

$tests = [
    'Index scan counted' => static fn (): bool => CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"Index Scan"}}]')['index_scans'] === 1,
    'Sequential scan counted' => static fn (): bool => CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"Seq Scan"}}]')['sequential_scans'] === 1,
    'Estimate rounded' => static fn (): bool => CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"Limit","Total Cost":12.345,"Plan Rows":7}}]')['cost'] === 12.35,
    'Plan rows preserved' => static fn (): bool => CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"Limit","Total Cost":12.345,"Plan Rows":7}}]')['estimated_rows'] === 7,
    'Repeated nodes unique' => static fn (): bool => CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"Append","Plans":[{"Node Type":"Seq Scan"},{"Node Type":"Seq Scan"}]}}]')['nodes'] === ['Append','Seq Scan'],
    'Repeated scan count preserved' => static fn (): bool => CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"Append","Plans":[{"Node Type":"Seq Scan"},{"Node Type":"Seq Scan"}]}}]')['sequential_scans'] === 2,
    'Unknown node redacted' => static fn (): bool => CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"secret relation name"}}]')['nodes'] === ['Other'],
    'Relation identifier redacted' => static fn (): bool => !str_contains(json_encode(CrmQueryPlanDigest::summarize('[{"Plan":{"Node Type":"Seq Scan","Relation Name":"secret_customer_row"}}]')), 'secret'),
    'Malformed JSON rejected' => static function (): bool { try { CrmQueryPlanDigest::summarize('invalid'); return false; } catch (UnexpectedValueException) { return true; } },
    'Empty plan rejected' => static function (): bool { try { CrmQueryPlanDigest::summarize('[]'); return false; } catch (UnexpectedValueException) { return true; } },
];
$pass = 0;
foreach ($tests as $name => $fn) {
    try { $ok = $fn(); } catch (Throwable $e) { $ok = false; }
    echo ($ok ? '[PASS] ' : '[FAIL] ').$name."\n";
    if (!$ok) exit(1);
    $pass++;
}
echo "A7.7.5.1 smoke: {$pass}/{$pass} PASS; no DB.\n";
