<?php
/**
 * A7.4.9 installation: preserves recent Big-Changes pages, patches only known anchors.
 * Run once (or safely rerun). Does not run migrations or reset any data.
 * Validates ALL target files before writing ANY; backs up originals under tools/.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$head = $root.'/.git/HEAD';
if (is_file($head)) {
    $raw = trim((string)file_get_contents($head));
    if (str_starts_with($raw, 'ref: refs/heads/')) {
        $branch = substr($raw, strlen('ref: refs/heads/'));
        if ($branch !== 'Big-Changes') {
            fwrite(STDERR, "ERROR: requires Big-Changes branch (current {$branch}).\n");
            exit(1);
        }
    }
}

function a749Replace(string $text, string $old, string $new, string $file): string
{
    if (str_contains($text, $new)) return $text; // already integrated
    $count = substr_count($text, $old);
    if ($count !== 1) throw new RuntimeException("{$file}: expected 1 safe anchor, found {$count}. No files changed.");
    return str_replace($old, $new, $text);
}

/** @return array<string, array{before:string,after:string}> */
function a749Plan(string $root): array
{
    $targets = [
        'routes/todos.php',
        'app/Http/Controllers/WorkspaceTaskSubmissionController.php',
        'app/Http/Controllers/TodoController.php',
        'resources/views/todos/show.blade.php',
        'resources/views/todos/_style.blade.php',
        'tests/Feature/WorkspaceCollaborationHttpTest.php',
    ];
    $planned = [];
    foreach ($targets as $relative) {
        $filename = $root.'/'.$relative;
        if (!is_file($filename)) throw new RuntimeException("Missing {$relative}. No files changed.");
        $before = file_get_contents($filename);
        if ($before === false) throw new RuntimeException("Cannot read {$relative}.");
        $after = $before;

        if ($relative === 'routes/todos.php') {
            $after = a749Replace($after,
                'use App\\Http\\Controllers\\WorkspaceTaskSubmissionController;',
                'use App\\Http\\Controllers\\WorkspaceTaskSubmissionController;' . "\n" .
                'use App\\Http\\Controllers\\WorkspaceTaskReviewController;', $relative);
            $anchor = "    Route::patch('todos/{todo}/complete', [TodoController::class, 'complete'])->name('todos.complete');";
            $after = a749Replace($after, $anchor, $anchor."\n".
                "    // A7.4.9: Creator-only approval/revision of a specific assignment.\n".
                "    Route::post('todos/{todo}/assignments/{assignment}/review', [WorkspaceTaskReviewController::class, 'decide'])\n".
                "        ->middleware('throttle:20,1')->whereUuid('todo')->whereUuid('assignment')->name('todos.assignments.review');", $relative);
        }
        if ($relative === 'app/Http/Controllers/WorkspaceTaskSubmissionController.php') {
            // A7.4.9: use parent -> child row-lock order for submission/review/close.
            $oldUpload = "                // Lock assignment so completion cannot race with upload.\n";
            $newUpload = "                // A7.4.9: serialize upload with task closure before locking the assignment.\n".
                "                \$lockedTask = WorkspaceTask::query()->whereKey(\$todo->getKey())->lockForUpdate()->firstOrFail();\n".
                "                abort_unless(\$lockedTask->visibility === 'work' &&\n".
                "                    !in_array(\$lockedTask->status, ['completed','cancelled'], true), 422,\n".
                "                    'This task no longer accepts submissions.');\n".
                "                // Lock assignment so completion cannot race with upload.\n";
            $after = a749Replace($after, $oldUpload, $newUpload, $relative);
            $oldComplete = <<<'BLOCK'
    public function complete(WorkspaceTask $todo): RedirectResponse
    {
        $assignment = $this->ownAssignment($todo);
        DB::transaction(function () use ($todo, $assignment) {
            $locked = WorkspaceTaskAssignment::query()->whereKey($assignment->getKey())
                ->where('task_id', $todo->getKey())->lockForUpdate()->firstOrFail();
            $currentTask = WorkspaceTask::query()->whereKey($todo->getKey())->firstOrFail();
            abort_if(in_array($currentTask->status,['completed','cancelled'],true),422,
                'This task is no longer accepting submissions.');
            if ($locked->submission_required && !$locked->files()->exists()) {
                throw ValidationException::withMessages([
                    'files' => 'This assignment requires at least one file before completion.',
                ]);
            }
            if ($locked->status !== 'completed') {
                $locked->update(['status'=>'completed','completed_at'=>now()]);
                if ($currentTask->status === 'todo') $currentTask->update(['status'=>'in_progress']);
            }
        });
        return back()->with('success','Your assignment is marked completed.');
    }
BLOCK;
            $newComplete = <<<'BLOCK'
    public function complete(WorkspaceTask $todo): RedirectResponse
    {
        $assignment = $this->ownAssignment($todo);
        DB::transaction(function () use ($todo, $assignment) {
            // Task first, then assignment: identical lock order to creator review.
            $currentTask = WorkspaceTask::query()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($currentTask->visibility === 'work', 404);
            abort_if(in_array($currentTask->status,['completed','cancelled'],true),422,
                'This task is no longer accepting submissions.');
            $locked = WorkspaceTaskAssignment::query()->whereKey($assignment->getKey())
                ->where('task_id', $currentTask->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((string) $locked->assigned_user_id === $this->actorId(), 404);
            if ($locked->submission_required && !$locked->files()->exists()) {
                throw ValidationException::withMessages([
                    'files' => 'This assignment requires at least one file before completion.',
                ]);
            }
            if ($locked->status !== 'completed') {
                $locked->update([
                    'status'=>'completed', 'completed_at'=>now(),
                    'review_status'=>'pending_review',
                    'reviewed_by_user_id'=>null, 'reviewed_at'=>null, 'review_note'=>null,
                ]);
                if ($currentTask->status === 'todo') $currentTask->update(['status'=>'in_progress']);
            }
        }, 3);
        return back()->with('success','Submission received. Awaiting the creator\'s review.');
    }
BLOCK;
            $after = a749Replace($after, $oldComplete, $newComplete, $relative);
            $oldRemove = <<<'BLOCK'
    public function removeFile(WorkspaceTask $todo, string $file): RedirectResponse
    {
        $assignment = $this->ownAssignment($todo);
        abort_if(in_array($todo->status,['completed','cancelled'],true),422);
        $document = $assignment->files()->whereKey($file)->firstOrFail();
        abort_unless($document->uploaded_by_user_id === $this->actorId(), 403);
        abort_if($assignment->status === 'completed',422, 'Submitted evidence is locked.');
        $path = $document->stored_path;
        $document->delete();
        Storage::disk('local')->delete($path);
        return back()->with('success', 'Attachment removed.');
    }
BLOCK;
            $newRemove = <<<'BLOCK'
    public function removeFile(WorkspaceTask $todo, string $file): RedirectResponse
    {
        $assignment = $this->ownAssignment($todo);
        $path = DB::transaction(function () use ($todo, $assignment, $file) {
            $lockedTask = WorkspaceTask::query()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($lockedTask->visibility === 'work', 404);
            abort_if(in_array($lockedTask->status,['completed','cancelled'],true),422);
            $locked = WorkspaceTaskAssignment::query()->whereKey($assignment->getKey())
                ->where('task_id', $lockedTask->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((string) $locked->assigned_user_id === $this->actorId(), 404);
            abort_if($locked->status === 'completed',422, 'Submitted evidence is locked.');
            $document = $locked->files()->whereKey($file)->firstOrFail();
            abort_unless((string) $document->uploaded_by_user_id === $this->actorId(), 403);
            $path = $document->stored_path;
            $document->delete();
            return $path;
        }, 3);
        Storage::disk('local')->delete($path);
        return back()->with('success', 'Attachment removed.');
    }
BLOCK;
            $after = a749Replace($after, $oldRemove, $newRemove, $relative);
        }
        if ($relative === 'app/Http/Controllers/TodoController.php') {
            // Legacy and approved submissions count as done; pending review does not.
            $oldPending = "\$mandatoryPendingCount = \$todo->assignments()->where('completion_required',true)->where('status','!=','completed')->count();";
            $newPending = "\$mandatoryPendingCount = \$todo->assignments()->where('completion_required',true)\n".
                "            ->where(fn (Builder \$q) => \$q->where('status','!=','completed')\n".
                "                ->orWhereNotIn('review_status', ['approved','legacy_accepted']))->count();";
            $after = a749Replace($after, $oldPending, $newPending, $relative);
            $oldReturn = "return view('todos.show', compact('todo','activity','assignments','myAssignment','isOwner','assignmentCount','completedCount','mandatoryCount','mandatoryPendingCount'));";
            $newReturn = "\$reviewHistory = \\App\\Models\\WorkspaceTaskReview::query()\n".
                "            ->where('task_id', \$todo->getKey())\n".
                "            ->when(!\$isOwner, fn (Builder \$q) => \$q->whereHas('assignment',\n".
                "                fn (Builder \$a) => \$a->where('assigned_user_id', \$this->actorId())))\n".
                "            ->with(['actor', 'assignment.assignee'])\n".
                "            ->orderByDesc('created_at')->limit(25)->get();\n".
                "        return view('todos.show', compact('todo','activity','assignments','myAssignment','isOwner','assignmentCount','completedCount','mandatoryCount','mandatoryPendingCount','reviewHistory'));";
            $after = a749Replace($after, $oldReturn, $newReturn, $relative);
            $oldUpdate = <<<'BLOCK'
            if ($data['status'] === 'completed' && $record->assignments()
                ->where('completion_required', true)->where('status','!=','completed')->exists()) {
                throw ValidationException::withMessages(['status' => 'All mandatory assignees must finish before the task can be closed.']);
            }
BLOCK;
            $newUpdate = <<<'BLOCK'
            if ($data['status'] === 'completed' && $record->assignments()
                ->where('completion_required', true)
                ->where(fn (Builder $q) => $q->where('status','!=','completed')
                    ->orWhereNotIn('review_status', ['approved','legacy_accepted']))->exists()) {
                throw ValidationException::withMessages(['status' => 'All mandatory submissions must be approved before closing this task.']);
            }
BLOCK;
            $after = a749Replace($after, $oldUpdate, $newUpdate, $relative);
            $oldComplete = <<<'BLOCK'
            if ($record->assignments()->where('completion_required', true)->where('status','!=','completed')->exists()) {
                throw ValidationException::withMessages(['status'=>'Wait for mandatory assignees to finish before completing this task.']);
            }
BLOCK;
            $newComplete = <<<'BLOCK'
            if ($record->assignments()->where('completion_required', true)
                ->where(fn (Builder $q) => $q->where('status','!=','completed')
                    ->orWhereNotIn('review_status', ['approved','legacy_accepted']))->exists()) {
                throw ValidationException::withMessages(['status'=>'Approve all mandatory submissions before completing this task.']);
            }
BLOCK;
            $after = a749Replace($after, $oldComplete, $newComplete, $relative);
            $oldRemoval = "if (\$assignment->status === 'completed' || \$assignment->files()->exists()) {";
            $newRemoval = "if (\$assignment->status === 'completed' || \$assignment->files()->exists() ||\n".
                "                    \\App\\Models\\WorkspaceTaskReview::query()->where('assignment_id', \$assignment->getKey())->exists()) {";
            $after = a749Replace($after, $oldRemoval, $newRemoval, $relative);
        }
        if ($relative === 'resources/views/todos/show.blade.php') {
            $after = a749Replace($after,
                "@include('todos._style')",
                "@include('todos._review')\n@include('todos._style')", $relative);
        }
        if ($relative === 'resources/views/todos/_style.blade.php') {
            $style = trim((string)file_get_contents($root.'/tools/a749_review_style.snippet.css'));
            if ($style === '') throw new RuntimeException('Missing A7.4.9 CSS snippet.');
            if (!str_contains($after, '/* A7.4.9 submission review.')) {
                $end = strrpos($after, '</style>');
                if ($end === false || substr_count($after, '</style>') !== 1)
                    throw new RuntimeException('To-Do stylesheet closing tag not unique.');
                $after = substr_replace($after, "\n".$style."\n", $end, 0);
            }
        }
        if ($relative === 'tests/Feature/WorkspaceCollaborationHttpTest.php') {
            $old = "            \$t->string('status')->default('assigned');\n            \$t->timestamp('completed_at')->nullable();";
            $new = "            \$t->string('status')->default('assigned');\n".
                "            // A7.4.9 compatibility for existing isolated A7.4.8 suite.\n".
                "            \$t->string('review_status')->default('not_submitted');\n".
                "            \$t->uuid('reviewed_by_user_id')->nullable();\n".
                "            \$t->timestamp('reviewed_at')->nullable();\n".
                "            \$t->text('review_note')->nullable();\n".
                "            \$t->timestamp('completed_at')->nullable();";
            $after = a749Replace($after, $old, $new, $relative);
            $oldInsert = "        Schema::create('workspace_task_submission_files', function (Blueprint \$t): void {";
            $newInsert = "        // A7.4.9 review decision history needed by To-Do detail.\n".
                "        Schema::create('workspace_task_reviews', function (Blueprint \$t): void {\n".
                "            \$t->uuid('review_id')->primary();\n".
                "            \$t->uuid('task_id');\n".
                "            \$t->uuid('assignment_id');\n".
                "            \$t->uuid('actor_user_id');\n".
                "            \$t->string('decision');\n".
                "            \$t->text('note')->nullable();\n".
                "            \$t->timestamp('created_at')->nullable();\n".
                "        });\n".$oldInsert;
            $after = a749Replace($after, $oldInsert, $newInsert, $relative);
            $oldDrop = "foreach (['workspace_task_submission_files', 'workspace_task_assignments',";
            $newDrop = "foreach (['workspace_task_reviews', 'workspace_task_submission_files', 'workspace_task_assignments',";
            $after = a749Replace($after, $oldDrop, $newDrop, $relative);
        }
        if ($after !== $before) $planned[$relative] = ['before'=>$before, 'after'=>$after];
    }
    return $planned;
}

try {
    $planned = a749Plan($root);
    if (!$planned) { echo "A7.4.9 already integrated. No file changes.\n"; exit(0); }
    $backupRoot = $root.'/tools/a749_backups/'.date('Ymd-His').'-'.getmypid();
    foreach ($planned as $rel => $change) {
        $dest = $backupRoot.'/'.$rel;
        if (!is_dir(dirname($dest)) && !mkdir(dirname($dest), 0775, true) && !is_dir(dirname($dest)))
            throw new RuntimeException('Cannot create backup folder for '.$rel);
        if (file_put_contents($dest, $change['before'], LOCK_EX) === false)
            throw new RuntimeException('Failed to back up '.$rel);
    }
    $written = [];
    try {
        foreach ($planned as $rel => $change) {
            if (file_put_contents($root.'/'.$rel, $change['after'], LOCK_EX) === false)
                throw new RuntimeException('Cannot install '.$rel);
            $written[] = $rel;
            echo "Integrated: {$rel}\n";
        }
    } catch (Throwable $e) {
        foreach ($written as $rel) @file_put_contents($root.'/'.$rel, $planned[$rel]['before'], LOCK_EX);
        throw $e;
    }
    echo 'Complete. Backups: '.str_replace($root.'/', '', $backupRoot)."\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: '.$e->getMessage()."\n");
    exit(1);
}
