<?php
/** Read-only A7.4.9 static integration check. Does not access the database. */
declare(strict_types=1);
$root = dirname(__DIR__);
$file = static fn (string $p): string => is_file($root.'/'.$p) ? (string)file_get_contents($root.'/'.$p) : '';
$todo = $file('app/Http/Controllers/TodoController.php');
$submission = $file('app/Http/Controllers/WorkspaceTaskSubmissionController.php');
$review = $file('app/Http/Controllers/WorkspaceTaskReviewController.php');
$routes = $file('routes/todos.php');
$checks = [
    'Review migration exists' => is_file($root.'/database/migrations/2026_11_17_120000_add_workspace_task_reviews.php'),
    'Review history model exists' => is_file($root.'/app/Models/WorkspaceTaskReview.php'),
    'Dedicated review controller exists' => $review !== '',
    'Creator-only review enforced server-side' => str_contains($review, 'Only the task creator may review submissions'),
    'Review controller locks task then assignment' => substr_count($review, 'lockForUpdate()') >= 2,
    'Cross-parent assignment rejected' => str_contains($review, '$assignment->task_id ===') || str_contains($review, '(string) $assignment->task_id'),
    'Review endpoint has throttle and UUID constraints' => str_contains($routes, 'todos.assignments.review') && str_contains($routes, "throttle:20,1"),
    'New submissions enter pending_review' => str_contains($submission, "'review_status'=>'pending_review'"),
    'Submission uses task-first row lock' => str_contains($submission, 'Task first, then assignment'),
    'Review gate in To-Do update & complete' => substr_count($todo, "orWhereNotIn('review_status', ['approved','legacy_accepted'])") >= 3,
    'History protected against assignee removal' => str_contains($todo, "WorkspaceTaskReview::query()->where('assignment_id'"),
    'Review timeline scoped to owner/assignee' => str_contains($todo, '->whereHas(\'assignment\''),
    'To-Do detail review panel integrated' => str_contains($file('resources/views/todos/show.blade.php'), "@include('todos._review')"),
    'Review panel exists' => is_file($root.'/resources/views/todos/_review.blade.php'),
    'Single To-Do stylesheet still used' => str_contains($file('resources/views/todos/_style.blade.php'), '/* A7.4.9 submission review.'),
    'Isolated review HTTP test exists' => is_file($root.'/tests/Feature/WorkspaceTaskReviewHttpTest.php'),
    'Existing collaboration HTTP fixture preserved' => str_contains($file('tests/Feature/WorkspaceCollaborationHttpTest.php'), 'A7.4.9 compatibility'),
    'Original file submission routes preserved' => str_contains($routes, 'todos.submission.upload') && str_contains($routes, 'todos.submissions.download-all'),
    'Prior A7.4.8 audit remains in tools' => is_file($root.'/tools/a748_quick_check.php'),
];
$pass = 0;
foreach ($checks as $name=>$ok) { echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL; $pass += (int)$ok; }
echo "\nA7.4.9 static check: {$pass}/".count($checks)." PASS.\n";
echo "HTTP tests and browser verification remain separate.\n";
exit($pass === count($checks) ? 0 : 1);
