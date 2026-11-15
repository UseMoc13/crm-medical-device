<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Models\WorkspaceTask;
use App\Models\WorkspaceTaskAssignment;
use App\Services\CrmPermissionResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TodoController extends Controller
{
    private const STATUSES = ['todo', 'in_progress', 'completed', 'cancelled'];
    private const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    private const VISIBILITIES = ['personal', 'work'];

    private function actorId(): string
    {
        abort_unless(Auth::guard('web')->check(), 401);
        return (string) Auth::guard('web')->id();
    }

    private function mine(): Builder
    {
        return WorkspaceTask::query()->where('owner_user_id', $this->actorId());
    }

    /** Owner of any task, or recipient of a work assignment. Personal is NEVER shared. */
    private function visible(): Builder
    {
        $user = $this->actorId();
        return WorkspaceTask::query()->where(function (Builder $query) use ($user) {
            $query->where('owner_user_id', $user)
                ->orWhere(function (Builder $work) use ($user) {
                    $work->where('visibility', 'work')->whereHas('assignments',
                        fn (Builder $q) => $q->where('assigned_user_id', $user));
                });
        });
    }

    private function findVisible(WorkspaceTask $todo): WorkspaceTask
    {
        return $this->visible()->whereKey($todo->getKey())->firstOrFail();
    }

    private function findOwned(WorkspaceTask $todo): WorkspaceTask
    {
        $todo = $this->findVisible($todo);
        abort_unless((string) $todo->owner_user_id === $this->actorId(), 403,
            'Only the task creator can change task details.');
        return $todo;
    }

    public function index(Request $request): View
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'priority' => ['nullable', Rule::in(self::PRIORITIES)],
            'visibility' => ['nullable', Rule::in(self::VISIBILITIES)],
            'scope' => ['nullable', Rule::in(['all','created','assigned','personal'])],
            'sort' => ['nullable', Rule::in(['due_at', 'created_at', 'title', 'priority', 'status'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $query = $this->visible()->with(['owner', 'assignments.assignee']);
        $scope = $data['scope'] ?? 'all';
        if ($scope === 'created') $query->where('owner_user_id', $this->actorId());
        if ($scope === 'assigned') $query->where('visibility', 'work')
            ->whereHas('assignments', fn (Builder $q) => $q->where('assigned_user_id', $this->actorId()));
        if ($scope === 'personal') $query->where('visibility', 'personal')
            ->where('owner_user_id', $this->actorId());

        if ($search = trim((string) ($data['search'] ?? ''))) {
            $term = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(fn (Builder $q) => $q->where('title','ilike',$term)->orWhere('description','ilike',$term));
        }
        foreach (['status', 'priority', 'visibility'] as $field) {
            if (!empty($data[$field])) $query->where($field, $data[$field]);
        }
        $sort = $data['sort'] ?? 'created_at';
        $direction = $data['direction'] ?? 'desc';
        $todos = $query->orderBy($sort, $direction)->orderBy('task_id')->paginate(10)->withQueryString();

        $summary = $this->visible()->selectRaw("count(*) as total,
            count(*) filter (where status = 'completed') as completed,
            count(*) filter (where status not in ('completed','cancelled') and due_at < now()) as overdue,
            count(*) filter (where status not in ('completed','cancelled') and due_at >= now() and due_at < now() + interval '7 days') as upcoming")->first();
        $assignedCount = WorkspaceTaskAssignment::query()->where('assigned_user_id', $this->actorId())
            ->whereHas('task', fn (Builder $q) => $q->where('visibility','work'))->count();
        return view('todos.index', compact('todos', 'summary', 'assignedCount', 'scope'));
    }

    private function activities()
    {
        return Activity::query()->where('user_id', $this->actorId())
            ->orderByDesc('activity_date')->limit(200)->get(['activity_id','subject','activity_date']);
    }

    /** Active users in the creator's branch, except protected Super Admin who can choose all. */
    private function assigneeOptions()
    {
        $user = Auth::guard('web')->user();
        $query = User::query()->where('status', 'active');
        if (!app(CrmPermissionResolver::class)->isSuperAdmin($user)) {
            if ($user->branch_id === null) {
                $query->where('user_id', $user->getKey());
            } else {
                $query->where('branch_id', $user->branch_id);
            }
        }
        return $query->orderBy('name')->limit(500)->get(['user_id','name','branch_id']);
    }

    public function create(): View
    {
        return view('todos.create', [
            'activities' => $this->activities(),
            'assigneeOptions' => $this->assigneeOptions(),
        ]);
    }

    /** @return array{data:array, assignee_ids:array, required_ids:array} */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required','string','max:150'],
            'description' => ['nullable','string','max:5000'],
            'visibility' => ['required',Rule::in(self::VISIBILITIES)],
            'status' => ['required',Rule::in(self::STATUSES)],
            'priority' => ['required',Rule::in(self::PRIORITIES)],
            'start_day' => ['nullable','required_with:start_time','date_format:Y-m-d'],
            'start_time' => ['nullable','date_format:H:i'],
            'due_day' => ['nullable','required_with:due_time,start_day','date_format:Y-m-d'],
            'due_time' => ['nullable','date_format:H:i'],
            'activity_id' => ['nullable','uuid'],
            'assignee_ids' => ['sometimes','array','max:20'],
            'assignee_ids.*' => ['required','uuid','distinct'],
            'required_assignee_ids' => ['sometimes','array','max:20'],
            'mandatory_assignee_ids' => ['sometimes','array','max:20'],
            'required_assignee_ids.*' => ['required','uuid','distinct'],
            'mandatory_assignee_ids.*' => ['required','uuid','distinct'],
        ]);

        $assignees = array_values($data['assignee_ids'] ?? []);
        $requiredIds = array_values($data['required_assignee_ids'] ?? []);
        $mandatoryIds = array_values($data['mandatory_assignee_ids'] ?? []);
        if (array_diff($mandatoryIds, $assignees)) {
            throw ValidationException::withMessages(['mandatory_assignee_ids' => 'Mandatory status must belong to a selected assignee.']);
        }
        if (array_diff($requiredIds, $assignees)) {
            throw ValidationException::withMessages([
                'required_assignee_ids' => 'File requirement must belong to a selected assignee.',
            ]);
        }
        if ($data['visibility'] === 'personal' && ($assignees || $requiredIds || $mandatoryIds)) {
            throw ValidationException::withMessages([
                'assignee_ids' => 'Personal tasks cannot be assigned or shared.',
            ]);
        }
        if ($data['visibility'] === 'work') {
            $allowed = $this->assigneeOptions()->pluck('user_id')->map(fn ($v) => (string) $v)->all();
            if (array_diff($assignees, $allowed)) {
                throw ValidationException::withMessages([
                    'assignee_ids' => 'Only active users in your authorized assignment scope may be selected.',
                ]);
            }
        }
        if ($data['visibility'] === 'personal') $data['activity_id'] = null;
        if (!empty($data['activity_id'])) {
            abort_unless($data['visibility'] === 'work' && Activity::query()
                ->where('user_id', $this->actorId())->whereKey($data['activity_id'])->exists(),
                422, 'The selected Activity is not available for this task.');
        }
        $data['starts_at'] = !empty($data['start_day'])
            ? $data['start_day'] . ' ' . (($data['start_time'] ?? null) ?: '09:00') . ':00' : null;
        $data['due_at'] = !empty($data['due_day'])
            ? $data['due_day'] . ' ' . (($data['due_time'] ?? null) ?: '17:00') . ':00' : null;
        if ($data['starts_at'] && $data['due_at'] &&
            CarbonImmutable::parse($data['starts_at'])->gt(CarbonImmutable::parse($data['due_at']))) {
            throw ValidationException::withMessages(['due_day' => 'End date/time must be after the start.']);
        }
        foreach (['start_day','start_time','due_day','due_time','assignee_ids','required_assignee_ids','mandatory_assignee_ids'] as $field) {
            unset($data[$field]);
        }
        return ['data'=>$data, 'assignee_ids'=>$assignees, 'required_ids'=>$requiredIds, 'mandatory_ids'=>$mandatoryIds];
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validated($request);
        if ($payload['mandatory_ids'] && $payload['data']['status'] === 'completed') {
            throw ValidationException::withMessages(['status' => 'Complete mandatory assignments before marking the whole task completed.']);
        }
        $todo = DB::transaction(function () use ($payload) {
            $data = $payload['data'];
            $data['owner_user_id'] = $this->actorId();
            $data['assigned_user_id'] = count($payload['assignee_ids']) === 1
                ? $payload['assignee_ids'][0] : null;
            $data['completed_at'] = $data['status'] === 'completed' ? now() : null;
            $todo = WorkspaceTask::create($data);
            $this->syncAssignments($todo, $payload['assignee_ids'], $payload['required_ids'], $payload['mandatory_ids']);
            return $todo;
        });
        return redirect()->route('todos.show', ['todo' => $todo->getKey()])
            ->with('success', 'To-Do created successfully.');
    }

    /** Only creator can change the recipient list; submitted evidence cannot be silently removed. */
    private function syncAssignments(WorkspaceTask $todo, array $users, array $required, array $mandatory): void
    {
        $existing = $todo->assignments()->lockForUpdate()->get()->keyBy('assigned_user_id');
        foreach ($existing as $userId => $assignment) {
            if (!in_array((string) $userId, $users, true)) {
                if ($assignment->status === 'completed' || $assignment->files()->exists()) {
                    throw ValidationException::withMessages([
                        'assignee_ids' => 'Cannot remove an assignee with submitted files or completed work.',
                    ]);
                }
                $assignment->delete();
                continue;
            }
            $filesRequired = in_array((string) $userId, $required, true);
            if ($filesRequired && $assignment->status === 'completed' && !$assignment->files()->exists()) {
                throw ValidationException::withMessages([
                    'required_assignee_ids' => 'Cannot require files from an already completed empty submission.',
                ]);
            }
            $assignment->update([
                'submission_required'=>$filesRequired,
                'completion_required'=>in_array((string) $userId, $mandatory, true),
            ]);
        }
        foreach ($users as $userId) {
            if ($existing->has($userId)) continue;
            $todo->assignments()->create([
                'assigned_user_id' => $userId,
                'submission_required' => in_array($userId, $required, true),
                'completion_required' => in_array($userId, $mandatory, true),
                'status' => 'assigned',
            ]);
        }
    }

    public function show(WorkspaceTask $todo): View
    {
        $todo = $this->findVisible($todo)->load('owner');
        $isOwner = (string) $todo->owner_user_id === $this->actorId();
        $assignments = $todo->assignments()->with(['assignee','files'])
            ->when(!$isOwner, fn (Builder $q) => $q->where('assigned_user_id', $this->actorId()))
            ->orderBy('created_at')->get();
        $myAssignment = $isOwner ? $assignments->firstWhere('assigned_user_id',$this->actorId())
            : $assignments->first();
        // Do not disclose an Activity's details if the viewer lacks access to it.
        $activity = $todo->activity_id && $todo->visibility === 'work'
            ? Activity::query()->where('user_id', $this->actorId())->whereKey($todo->activity_id)->first()
            : null;
        $assignmentCount = $todo->assignments()->count();
        $completedCount = $todo->assignments()->where('status','completed')->count();
        $mandatoryCount = $todo->assignments()->where('completion_required', true)->count();
        $mandatoryPendingCount = $todo->assignments()->where('completion_required',true)->where('status','!=','completed')->count();
        return view('todos.show', compact('todo','activity','assignments','myAssignment','isOwner','assignmentCount','completedCount','mandatoryCount','mandatoryPendingCount'));
    }

    public function edit(WorkspaceTask $todo): View
    {
        $todo = $this->findOwned($todo)->load('assignments');
        return view('todos.edit', [
            'todo'=>$todo,
            'activities'=>$this->activities(),
            'assigneeOptions'=>$this->assigneeOptions(),
        ]);
    }

    public function update(Request $request, WorkspaceTask $todo): RedirectResponse
    {
        $todo = $this->findOwned($todo);
        $payload = $this->validated($request);
        DB::transaction(function () use ($todo, $payload) {
            $record = $this->mine()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
            $this->syncAssignments($record, $payload['assignee_ids'], $payload['required_ids'], $payload['mandatory_ids']);
            $data = $payload['data'];
            if ($data['status'] === 'completed' && $record->assignments()
                ->where('completion_required', true)->where('status','!=','completed')->exists()) {
                throw ValidationException::withMessages(['status' => 'All mandatory assignees must finish before the task can be closed.']);
            }
            if ($data['visibility'] === 'personal' && $record->assignments()->exists()) {
                throw ValidationException::withMessages(['visibility' => 'Remove active assignments before making a task personal.']);
            }
            $data['assigned_user_id'] = count($payload['assignee_ids']) === 1
                ? $payload['assignee_ids'][0] : null;
            $data['completed_at'] = $data['status'] === 'completed' ? ($record->completed_at ?? now()) : null;
            $record->update($data);
        });
        return redirect()->route('todos.show', ['todo'=>$todo->getKey()])
            ->with('success', 'To-Do updated successfully.');
    }

    public function complete(WorkspaceTask $todo): RedirectResponse
    {
        $todo = $this->findOwned($todo);
        DB::transaction(function () use ($todo) {
            $record = $this->mine()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
            if ($record->assignments()->where('completion_required', true)->where('status','!=','completed')->exists()) {
                throw ValidationException::withMessages(['status'=>'Wait for mandatory assignees to finish before completing this task.']);
            }
            $record->update(['status'=>'completed','completed_at'=>$record->completed_at ?? now()]);
        });
        return back()->with('success', 'To-Do marked as completed.');
    }

    public function destroy(WorkspaceTask $todo): RedirectResponse
    {
        $todo = $this->findOwned($todo);
        $paths = $todo->assignments()->with('files')->get()->flatMap(fn ($a) => $a->files)
            ->pluck('stored_path')->all();
        DB::transaction(fn () => $this->mine()->whereKey($todo->getKey())
            ->lockForUpdate()->firstOrFail()->delete());
        // Data is private even if filesystem cleanup temporarily fails.
        if ($paths) \Illuminate\Support\Facades\Storage::disk('local')->delete($paths);
        return redirect()->route('todos.index')->with('success', 'To-Do deleted successfully.');
    }
}
