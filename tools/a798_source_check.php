<?php
/** A7.9.8 static checks (no database calls). */
declare(strict_types=1);
$root = dirname(__DIR__);
$requirements = [
    'app/Services/CrmWorkLogAuthorPolicy.php' => ['final class CrmWorkLogAuthorPolicy', 'public function canWrite', 'function permits'],
    'app/Services/CrmAssignmentIntegrityReport.php' => ['final class CrmAssignmentIntegrityReport', 'function forUser', 'function summary'],
    'database/migrations/2026_11_26_150000_add_work_log_author_to_service_ticket_work_logs.php' => ['created_by_user_id', 'restrictOnDelete'],
    'resources/views/users/_assignment_impact.blade.php' => ['A798:', 'Existing Operational Assignments'],
    'tests/Unit/CrmWorkLogAuthorPolicyTest.php' => ['function test_active_assigned', 'function test_wrong_staff'],
    'app/Http/Controllers/ServiceTicketWorkLogController.php' => ['A798:worklog_actor_capture','A798:worklog_actor_guard','A798:worklog_author_insert'],
    'app/Http/Controllers/ServiceTicketController.php' => ['A798:ticket_author_query', 'authors.name as author_name'],
    'resources/views/service-tickets/_work_logs.blade.php' => ['A798:worklog_form_visibility','A798:worklog_unauthorized_hint','A798:worklog_author_label'],
    'app/Http/Controllers/UserController.php' => ['A798:user_assignment_impact'],
    'resources/views/users/edit.blade.php' => ['A798:user_impact_panel'],
];
$n = 0;
foreach ($requirements as $rel=>$tokens) {
    $file = $root . '/' . $rel;
    if (!is_file($file)) { fwrite(STDERR,"[FAIL] Missing $rel\n"); exit(1); }
    $s = (string) file_get_contents($file);
    foreach ($tokens as $token) {
        if (!str_contains($s, $token)) { fwrite(STDERR,"[FAIL] $rel missing $token\n");exit(1); }
        $n++;
    }
    echo "[PASS] $rel\n";
}
foreach (['app/Services/CrmWorkLogAuthorPolicy.php','app/Services/CrmAssignmentIntegrityReport.php','app/Http/Controllers/ServiceTicketWorkLogController.php','app/Http/Controllers/ServiceTicketController.php','app/Http/Controllers/UserController.php','database/migrations/2026_11_26_150000_add_work_log_author_to_service_ticket_work_logs.php'] as $rel) {
    $command = escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root . '/' . $rel).' 2>&1';
    exec($command, $lines, $exit);
    if ($exit !== 0) { fwrite(STDERR, "[FAIL] PHP lint $rel\n".implode("\n",$lines)."\n");exit(1); }
    echo "[PASS] PHP syntax $rel\n"; $n++;
}
echo "[RESULT] A798_SOURCE_READY checks=$n (PostgreSQL and HTTP not executed)\n";
