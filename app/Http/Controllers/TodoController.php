<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\WorkspaceTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TodoController extends Controller
{
    private const STATUSES = ['todo', 'in_progress', 'completed', 'cancelled'];
    private const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    private const VISIBILITIES = ['personal', 'work'];

    private function mine()
    {
        // A7.3 first release: strictly owner-only, even when visibility = work.
        return WorkspaceTask::query()->where('owner_user_id', (string) Auth::id());
    }

    private function findMine(WorkspaceTask $todo): WorkspaceTask
    {
        return $this->mine()->whereKey($todo->getKey())->firstOrFail();
    }

    public function index(Request $request): View
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'priority' => ['nullable', Rule::in(self::PRIORITIES)],
            'visibility' => ['nullable', Rule::in(self::VISIBILITIES)],
            'sort' => ['nullable', Rule::in(['due_at', 'created_at', 'title', 'priority', 'status'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $query = $this->mine();
        if ($search = trim((string) ($data['search'] ?? ''))) {
            $escaped = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(fn ($q) => $q->where('title', 'ilike', $escaped)->orWhere('description', 'ilike', $escaped));
        }
        foreach (['status', 'priority', 'visibility'] as $key) {
            if (!empty($data[$key])) $query->where($key, $data[$key]);
        }
        $sort = $data['sort'] ?? 'created_at';
        $direction = $data['direction'] ?? 'desc';
        $todos = $query->orderBy($sort, $direction)->orderBy('task_id')->paginate(10)->withQueryString();
        $summary = $this->mine()->selectRaw("count(*) as total, count(*) filter (where status = 'completed') as completed, count(*) filter (where status not in ('completed','cancelled') and due_at < now()) as overdue")->first();
        return view('todos.index', compact('todos', 'summary'));
    }

    private function activities()
    {
        return Activity::query()->where('user_id', (string) Auth::id())
            ->orderByDesc('activity_date')->limit(200)->get(['activity_id', 'subject', 'activity_date']);
    }

    public function create(): View
    {
        return view('todos.create', ['activities' => $this->activities()]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'visibility' => ['required', Rule::in(self::VISIBILITIES)],
            'status' => ['required', Rule::in(self::STATUSES)],
            'priority' => ['required', Rule::in(self::PRIORITIES)],
            'due_day' => ['nullable', 'required_with:due_time', 'date_format:Y-m-d'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'activity_id' => ['nullable', 'uuid'],
        ]);
        if (!empty($data['activity_id'])) {
            // No cross-user references, even if the submitted UUID exists.
            abort_unless(
                $data['visibility'] === 'work' && Activity::query()
                    ->where('user_id', (string) Auth::id())->whereKey($data['activity_id'])->exists(),
                422, 'The selected Activity is not available for this work task.'
            );
        }
        $data['due_at'] = !empty($data['due_day'])
            ? $data['due_day'] . ' ' . ($data['due_time'] ?? '09:00') . ':00'
            : null;
        unset($data['due_day'], $data['due_time']);
        return $data;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['owner_user_id'] = (string) Auth::id();
        $data['assigned_user_id'] = (string) Auth::id();
        $data['completed_at'] = $data['status'] === 'completed' ? now() : null;
        $todo = WorkspaceTask::create($data);
        return redirect()->route('todos.show', ['todo' => $todo->getKey()])
            ->with('success', 'To-Do created successfully.');
    }

    public function show(WorkspaceTask $todo): View
    {
        $todo = $this->findMine($todo);
        $activity = $todo->activity_id
            ? Activity::query()->where('user_id', (string) Auth::id())->whereKey($todo->activity_id)->first()
            : null;
        return view('todos.show', compact('todo', 'activity'));
    }

    public function edit(WorkspaceTask $todo): View
    {
        $todo = $this->findMine($todo);
        return view('todos.edit', ['todo' => $todo, 'activities' => $this->activities()]);
    }

    public function update(Request $request, WorkspaceTask $todo): RedirectResponse
    {
        $todo = $this->findMine($todo);
        $data = $this->validated($request);
        DB::transaction(function () use ($todo, $data) {
            $record = $this->mine()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
            $data['completed_at'] = $data['status'] === 'completed'
                ? ($record->completed_at ?? now()) : null;
            $record->update($data);
        });
        return redirect()->route('todos.show', ['todo' => $todo->getKey()])
            ->with('success', 'To-Do updated successfully.');
    }

    public function complete(WorkspaceTask $todo): RedirectResponse
    {
        $todo = $this->findMine($todo);
        DB::transaction(function () use ($todo) {
            $record = $this->mine()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail();
            $record->update(['status' => 'completed', 'completed_at' => $record->completed_at ?? now()]);
        });
        return back()->with('success', 'To-Do marked as completed.');
    }

    public function destroy(WorkspaceTask $todo): RedirectResponse
    {
        $todo = $this->findMine($todo);
        DB::transaction(function () use ($todo) {
            $this->mine()->whereKey($todo->getKey())->lockForUpdate()->firstOrFail()->delete();
        });
        return redirect()->route('todos.index')->with('success', 'To-Do deleted successfully.');
    }
}
