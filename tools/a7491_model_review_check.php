<?php
/** A7.4.9.1 mass-assignment regression gate; read-only; no DB connection. */
declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root.'/vendor/autoload.php';
if (!is_file($autoload)) { fwrite(STDERR, "[FAIL] vendor/autoload.php missing. Run composer install.\n"); exit(1); }
require $autoload;

$model = new \App\Models\WorkspaceTaskAssignment();
$checks = [
    'Model uses workspace_task_assignments table' => $model->getTable() === 'workspace_task_assignments',
    'UUID primary key preserved' => $model->getKeyName() === 'assignment_id' && !$model->getIncrementing(),
    'Submission workflow columns are fillable' => !array_diff(['status','completed_at','submission_required','completion_required'], $model->getFillable()),
    'Review workflow columns are fillable' => !array_diff(['review_status','reviewed_by_user_id','reviewed_at','review_note'], $model->getFillable()),
    'Review timestamp uses datetime cast' => $model->hasCast('reviewed_at','datetime'),
];
$test = new \App\Models\WorkspaceTaskAssignment();
$test->fill([
    'status'=>'completed','review_status'=>'pending_review',
    'reviewed_by_user_id'=>'11111111-1111-4111-8111-111111111111',
    'reviewed_at'=>'2026-11-17 09:00:00','review_note'=>'Approved after verification',
]);
$checks['Mass assignment keeps pending review'] = $test->review_status === 'pending_review';
$checks['Mass assignment keeps reviewer metadata'] = $test->reviewed_by_user_id === '11111111-1111-4111-8111-111111111111' && $test->review_note === 'Approved after verification';
$checks['Review timestamp is a date object'] = $test->reviewed_at instanceof \DateTimeInterface;
$passed = 0;
foreach ($checks as $label=>$ok) {
    echo ($ok ? '[PASS] ' : '[FAIL] ').$label.PHP_EOL;
    if ($ok) $passed++;
}
echo "A7.4.9.1 model check: {$passed}/".count($checks)." PASS. No DB writes.\n";
exit($passed === count($checks) ? 0 : 1);
