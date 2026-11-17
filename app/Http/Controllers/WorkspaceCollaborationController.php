<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\WorkspaceCollaborationMember;
use App\Models\WorkspaceTask;
use App\Services\CrmTodoRecipientDirectory;
use App\Services\WorkspaceCollaborationAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class WorkspaceCollaborationController extends Controller
{
    private function acl(): WorkspaceCollaborationAccess
    {
        return app(WorkspaceCollaborationAccess::class);
    }

    private function recipientDirectory(): CrmTodoRecipientDirectory
    {
        return app(CrmTodoRecipientDirectory::class);
    }

    /** Preserve the old full-detail templates for creators and existing assignees. */
    public function showActivity(Activity $activity): View
    {
        $role = $this->acl()->role($activity);
        if ($role === null) return app(ActivityController::class)->show($activity);
        if ($role === 'creator') return app(ActivityController::class)->show($activity);
        return $this->collaboratorDetail($activity, $role);
    }

    public function showTodo(WorkspaceTask $todo): View
    {
        $role = $this->acl()->role($todo);
        if ($role === null || in_array($role, ['creator', 'assignee'], true)) {
            return app(TodoController::class)->show($todo);
        }
        return $this->collaboratorDetail($todo, $role);
    }

    private function collaboratorDetail(Activity|WorkspaceTask $resource, string $role): View
    {
        // No Eloquent load() of CRM customers/leads/opportunities, other task assignees or submission files.
        $entityType = $this->acl()->type($resource);
        $entity = $resource;
        return view('workspace-collaboration.show', compact('entityType', 'entity', 'role'));
    }

    public function sharedActivities(): View
    {
        $id = $this->acl()->actorId();
        $activities = Activity::query()->where('user_id', '!=', $id)
            ->whereExists(function ($query) use ($id) {
                $query->selectRaw('1')->from('workspace_collaboration_members as wcm')
                    ->whereColumn('wcm.activity_id', 'activities.activity_id')
                    ->where('wcm.user_id', $id);
            })->orderByDesc('activity_date')->paginate(10);
        return view('workspace-collaboration.shared', ['entityType'=>'activity', 'entities'=>$activities]);
    }

    public function sharedTodos(): View
    {
        $id = $this->acl()->actorId();
        $todos = WorkspaceTask::query()->where('visibility', 'work')
            ->where('owner_user_id', '!=', $id)
            ->whereExists(function ($query) use ($id) {
                $query->selectRaw('1')->from('workspace_collaboration_members as wcm')
                    ->whereColumn('wcm.task_id', 'workspace_tasks.task_id')
                    ->where('wcm.user_id', $id);
            })->orderByDesc('created_at')->paginate(10);
        return view('workspace-collaboration.shared', ['entityType'=>'todo', 'entities'=>$todos]);
    }

    public function manageActivity(Request $request, Activity $activity): View
    {
        return $this->manage($request, $activity);
    }

    public function manageTodo(Request $request, WorkspaceTask $todo): View
    {
        return $this->manage($request, $todo);
    }

    private function manage(Request $request, Activity|WorkspaceTask $resource): View
    {
        $this->acl()->assertOwner($resource);
        if ($resource instanceof WorkspaceTask) abort_unless($resource->visibility === 'work', 404);

        $filter = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'role_id' => ['nullable', 'uuid'],
            'branch_id' => ['nullable', 'uuid'],
            'team_id' => ['nullable', 'uuid'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $actor = Auth::guard('web')->user();
        $directory = $this->recipientDirectory();
        $people = $directory->search($actor, $filter);
        $filters = $directory->filterOptions($actor);
        $members = $this->acl()->members($resource)->with('user')
            ->orderBy('created_at')->get();
        $entityType = $this->acl()->type($resource);
        $entity = $resource;
        return view('workspace-collaboration.manage', compact('people', 'filters', 'members', 'entityType', 'entity'));
    }

    public function addActivity(Request $request, Activity $activity): RedirectResponse
    {
        return $this->add($request, $activity);
    }

    public function addTodo(Request $request, WorkspaceTask $todo): RedirectResponse
    {
        return $this->add($request, $todo);
    }

    private function add(Request $request, Activity|WorkspaceTask $resource): RedirectResponse
    {
        $this->acl()->assertOwner($resource);
        if ($resource instanceof WorkspaceTask) abort_unless($resource->visibility === 'work', 404);
        $data = $request->validate([
            'user_id' => ['required', 'uuid'],
            'access_role' => ['required', Rule::in(['viewer','contributor'])],
        ]);
        if ($data['user_id'] === $this->acl()->actorId()) {
            throw ValidationException::withMessages(['user_id' => 'The creator already has full access.']);
        }
        // A branch/team filter does not grant access: check the selected person again on POST.
        // The shared recipient directory is also used by the To-Do form, where its
        // field is named assignee_ids. This collaboration form submits user_id,
        // so map a rejected recipient to the field the user actually selected.
        try {
            $this->recipientDirectory()->assertAssignable(Auth::guard('web')->user(), [$data['user_id']]);
        } catch (ValidationException $exception) {
            $errors = $exception->errors();
            if (!array_key_exists('assignee_ids', $errors)) {
                throw $exception;
            }
            throw ValidationException::withMessages([
                'user_id' => $errors['assignee_ids'],
            ]);
        }
        DB::transaction(function () use ($resource, $data) {
            // Lock the parent resource to serialize collaboration membership changes with its lifecycle.
            $locked = $resource::query()->whereKey($resource->getKey())->lockForUpdate()->firstOrFail();
            $this->acl()->assertOwner($locked);
            // A7.4.8.1: Work -> Personal may have committed while this request waited
            // for the row lock. Never allow a stale Work check to grant private access.
            if ($locked instanceof WorkspaceTask) {
                abort_unless($locked->visibility === 'work', 404);
            }
            $members = $this->acl()->members($locked);
            if (!(clone $members)->where('user_id', $data['user_id'])->exists() && $members->count() >= 20) {
                throw ValidationException::withMessages(['user_id'=>'Maximum 20 collaborators per record.']);
            }
            $attributes = [$this->acl()->foreignKey($locked) => (string)$locked->getKey(), 'user_id'=>$data['user_id']];
            WorkspaceCollaborationMember::query()->updateOrCreate($attributes, ['access_role'=>$data['access_role']]);
        });
        return back()->with('success', 'Collaborator access saved.');
    }

    public function removeActivity(Activity $activity, WorkspaceCollaborationMember $member): RedirectResponse
    {
        return $this->remove($activity, $member);
    }

    public function removeTodo(WorkspaceTask $todo, WorkspaceCollaborationMember $member): RedirectResponse
    {
        return $this->remove($todo, $member);
    }

    private function remove(Activity|WorkspaceTask $resource, WorkspaceCollaborationMember $member): RedirectResponse
    {
        $this->acl()->assertOwner($resource);
        DB::transaction(function () use ($resource, $member) {
            // Serialize revocations with invitation, progress writes, and Work->Personal.
            $locked = $resource::query()->whereKey($resource->getKey())->lockForUpdate()->firstOrFail();
            $this->acl()->assertOwner($locked);
            // A child UUID from another parent must never be removable here.
            $record = $this->acl()->members($locked)->whereKey($member->getKey())->firstOrFail();
            $record->delete();
        });
        // Entries remain as an audit trail, but the permission grant is removed.
        return back()->with('success', 'Collaborator access revoked.');
    }

    public function entryActivity(Request $request, Activity $activity): RedirectResponse
    {
        return $this->entry($request, $activity);
    }

    public function entryTodo(Request $request, WorkspaceTask $todo): RedirectResponse
    {
        return $this->entry($request, $todo);
    }

    private function entry(Request $request, Activity|WorkspaceTask $resource): RedirectResponse
    {
        // Early authorization avoids disclosing validation results to outsiders.
        $this->acl()->assertCanWrite($resource);
        if ($resource instanceof WorkspaceTask) {
            abort_unless($resource->visibility === 'work', 404);
        }
        $data = $request->validate([
            'entry_type' => ['required', Rule::in(['comment', 'progress'])],
            'body' => ['required', 'string', 'max:3000'],
        ]);
        $body = trim($data['body']);
        if ($body === '') throw ValidationException::withMessages(['body'=>'Enter a comment or progress update.']);

        DB::transaction(function () use ($resource, $data, $body) {
            // The parent lock is also acquired by member add/remove. Re-check after
            // acquiring it to ensure a revoked member cannot post a late entry.
            $locked = $resource::query()->whereKey($resource->getKey())->lockForUpdate()->firstOrFail();
            $this->acl()->assertCanWrite($locked);
            if ($locked instanceof WorkspaceTask) {
                abort_unless($locked->visibility === 'work', 404);
            }
            $this->acl()->entries($locked)->create([
                $this->acl()->foreignKey($locked) => (string)$locked->getKey(),
                'author_user_id' => $this->acl()->actorId(),
                'entry_type' => $data['entry_type'],
                'body' => $body,
            ]);
        });
        return back()->with('success', $data['entry_type'] === 'progress'
            ? 'Progress update recorded.' : 'Comment added successfully.');
    }
}
