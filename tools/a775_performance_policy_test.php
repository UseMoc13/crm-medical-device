<?php

declare(strict_types=1);

require __DIR__ . '/a775_performance_policy.php';
$cases = [
    ['tiny table does not trigger flags', [50, 10, 10000, 0, 0], []],
    ['large frequent scans flagged for investigation', [10000, 10, 800, 10, 1], ['REVIEW_HIGH_SEQUENTIAL_SCAN_COUNT']],
    ['high dead tuple ratio flagged', [2000, 1000, 0, 500, 3], ['REVIEW_DEAD_TUPLE_RATIO']],
    ['large table without index flagged', [5000, 0, 0, 0, 0], ['REVIEW_LARGE_TABLE_WITHOUT_INDEX']],
    ['index count alone does not imply problem', [5000, 0, 10, 1000, 7], []],
    ['small dead tuple count not flagged', [1200, 100, 0, 500, 2], []],
    ['multiple flags combined', [10000, 3000, 200, 1, 0], ['REVIEW_HIGH_SEQUENTIAL_SCAN_COUNT','REVIEW_DEAD_TUPLE_RATIO','REVIEW_LARGE_TABLE_WITHOUT_INDEX']],
];
$failed = 0;
foreach ($cases as [$name, $args, $expected]) {
    $actual = CrmPerformancePreflightPolicy::reviewFlags(...$args);
    if ($actual !== $expected) { $failed++; echo "[FAIL] {$name}\n"; }
    else echo "[PASS] {$name}\n";
}
try { CrmPerformancePreflightPolicy::reviewFlags(-1, 0, 0, 0, 0); echo "[FAIL] negative stats\n"; $failed++; }
catch (InvalidArgumentException) { echo "[PASS] negative stats fail closed\n"; }
echo 'A7.7.5 policy: '.(count($cases)+1-$failed).'/'.(count($cases)+1).' PASS; '.$failed." FAIL; No DB.\n";
exit($failed ? 1 : 0);
