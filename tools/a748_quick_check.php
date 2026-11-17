<?php
/** Static smoke checks for the A7.4.8 overlay. Does not connect to DB or execute migrations. */
declare(strict_types=1);
$root = dirname(__DIR__);
$files = [
    'routes/activities.php', 'routes/todos.php',
    'app/Http/Controllers/WorkspaceCollaborationController.php',
    'app/Services/WorkspaceCollaborationAccess.php',
    'app/Models/WorkspaceCollaborationMember.php',
    'app/Models/WorkspaceCollaborationEntry.php',
    'database/migrations/2026_11_16_160000_create_workspace_collaboration_tables.php',
    'resources/views/workspace-collaboration/_style.blade.php',
    'resources/views/workspace-collaboration/_panel.blade.php',
    'resources/views/workspace-collaboration/show.blade.php',
    'resources/views/workspace-collaboration/manage.blade.php',
    'resources/views/workspace-collaboration/shared.blade.php',
];
$tests = 0; $failed = 0;
$check = function(string $label, bool $ok) use (&$tests, &$failed): void {
    $tests++; if (!$ok) $failed++;
    echo ($ok ? '[PASS] ' : '[FAIL] ').$label.PHP_EOL;
};
foreach ($files as $file) $check('File '.$file, is_file($root.'/'.$file));
$read = static fn(string $file): string => is_file($root.'/'.$file) ? (string)file_get_contents($root.'/'.$file) : '';
$access = $read('app/Services/WorkspaceCollaborationAccess.php');
$control = $read('app/Http/Controllers/WorkspaceCollaborationController.php');
$routes = $read('routes/activities.php').$read('routes/todos.php');
$check('Owner-only member management', str_contains($control, 'assertOwner($resource)'));
$check('Viewer cannot write server-side', str_contains($access, "['creator', 'assignee', 'contributor']"));
$check('Personal TO-DO not shared', str_contains($access, "visibility !== 'work'"));
$check('Explicit membership instead of Team auto-grants', str_contains($access,'workspace_collaboration') || str_contains($access,'WorkspaceCollaborationMember'));
$check('Recipient eligibility checked on POST', str_contains($control,'assertAssignable('));
$check('Collaborator cap counts all members', str_contains($control, 'clone $members') && str_contains($control, '$members->count() >= 20'));
$check('Collaborator restricted detail view', str_contains($control,"'workspace-collaboration.show'"));
$check('Creator full detail controller preserved', str_contains($control,'ActivityController::class') && str_contains($control,'TodoController::class'));
$check('Entries rate limited', substr_count($routes,"throttle:30,1") === 2);
$check('Submission routes preserved', str_contains($routes,'WorkspaceTaskSubmissionController') && str_contains($routes,'submission.files.download'));
$check('One-resource CHECK constraints', str_contains($read('database/migrations/2026_11_16_160000_create_workspace_collaboration_tables.php'),'wce_one_resource'));
$check('UI integration applied to Activity detail', str_contains($read('resources/views/activities/show.blade.php'),"workspace-collaboration._panel"));
$check('UI integration applied to To-Do detail', str_contains($read('resources/views/todos/show.blade.php'),"workspace-collaboration._panel"));
$check('Work-to-personal revokes collaborators', str_contains($read('app/Http/Controllers/TodoController.php'),'A7.4.8: revoke explicit collaborators'));
$check('CRM toast layout retained', str_contains($read('resources/views/layouts/app.blade.php'),'crm-toast-area'));
$check('Global delete dialog still available', str_contains($read('resources/views/components/global-delete-confirm.blade.php'),'crm-global-delete-overlay'));
echo "\nStatic checks: ".($tests-$failed)."/{$tests} PASS".PHP_EOL;
echo "Reminder: Static PASS does NOT prove HTTP authorization, database migrations, or browser operation.\n";
exit($failed ? 1 : 0);
