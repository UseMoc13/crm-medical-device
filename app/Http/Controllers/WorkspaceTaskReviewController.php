<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskAssignment;
use App\Models\WorkspaceTaskReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** A7.4.9: the task creator alone reviews assignee submissions. */
final class WorkspaceTaskReviewController extends Controller
{
    public function decide(Request $request, WorkspaceTask $todo, WorkspaceTaskAssignment $assignment): RedirectResponse
    {
        $actor = (string) Auth::guard('web')->id();
        abort_unless($actor !== '' && (string) $todo->owner_user_id === $actor, 403,
            'Only the task creator may review submissions.');
        // Reject a foreign assignment before validating the review body.
        abort_unless((string) $assignment->task_id === (string) $todo->getKey(), 404);
        abort_unless($todo->visibility === 'work', 404);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'request_revision'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $note = trim((string) ($data['note'] ?? ''));
        if ($data['decision'] === 'request_revision' && mb_strlen($note) < 10) {
            throw ValidationException::withMessages([
                'note' => 'Explain the revision needed (at least 10 characters).',
            ]);
        }

        DB::transaction(function () use ($todo, $assignment, $actor, $data, $note) {
            // Lock order: task -> assignment. Same task lock used by TO-DO closure.
            $lockedTask = WorkspaceTask::query()->whereKey($todo->getKey())
                ->lockForUpdate()->firstOrFail();
            abort_unless((string) $lockedTask->owner_user_id === $actor, 403);
            abort_unless($lockedTask->visibility === 'work', 404);
            abort_if(in_array($lockedTask->status, ['completed', 'cancelled'], true), 422,
                'This task is closed and cannot be reviewed.');

            $record = WorkspaceTaskAssignment::query()->whereKey($assignment->getKey())
                ->where('task_id', $lockedTask->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'completed' && $record->review_status === 'pending_review',
                422, 'Only a submitted assignment awaiting review can be approved or revised.');

            $decision = $data['decision'] === 'approve' ? 'approved' : 'revision_requested';
            $changes = [
                'review_status' => $decision,
                'reviewed_by_user_id' => $actor,
                'reviewed_at' => now(),
                'review_note' => $note !== '' ? $note : null,
            ];
            if ($decision === 'revision_requested') {
                // Keep uploaded files and original audit history. The assignee can
                // remove/replace evidence and submit again with no assignment deletion.
                $changes['status'] = 'assigned';
                $changes['completed_at'] = null;
            }
            $record->update($changes);
            WorkspaceTaskReview::query()->create([
                'task_id' => $lockedTask->getKey(),
                'assignment_id' => $record->getKey(),
                'actor_user_id' => $actor,
                'decision' => $decision,
                'note' => $note !== '' ? $note : null,
            ]);
        }, 3);

        return back()->with('success', $data['decision'] === 'approve'
            ? 'Assignment approved.'
            : 'Revision requested. The assignee may update and resubmit the work.');
    }
}
