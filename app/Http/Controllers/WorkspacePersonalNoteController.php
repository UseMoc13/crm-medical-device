<?php

namespace App\Http\Controllers;

use App\Models\WorkspacePersonalNote;
use App\Services\WorkspaceNoteMentions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class WorkspacePersonalNoteController extends Controller
{
    private function actor(): string
    {
        abort_unless(Auth::guard('web')->check(), 401);
        return (string) Auth::guard('web')->id();
    }

    private function mine(): Builder
    {
        return WorkspacePersonalNote::query()->where('owner_user_id', $this->actor());
    }

    private function findMine(WorkspacePersonalNote $note): WorkspacePersonalNote
    {
        return $this->mine()->whereKey($note->getKey())->firstOrFail();
    }

    public function index(Request $request): View
    {
        $input = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'view' => ['nullable', Rule::in(['active', 'archived', 'all'])],
            'pinned' => ['nullable', Rule::in(['1'])],
        ]);
        $view = $input['view'] ?? 'active';
        $query = $this->mine();
        if ($view === 'active') $query->whereNull('archived_at');
        if ($view === 'archived') $query->whereNotNull('archived_at');
        if (($input['pinned'] ?? '') === '1') $query->where('is_pinned', true);
        if ($search = trim((string) ($input['search'] ?? ''))) {
            $term = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(fn (Builder $q) => $q->where('title', 'ilike', $term)
                ->orWhere('body', 'ilike', $term));
        }
        $notes = $query->orderByDesc('is_pinned')->orderByDesc('updated_at')
            ->paginate(12)->withQueryString();
        $summary = [
            'active' => $this->mine()->whereNull('archived_at')->count(),
            'pinned' => $this->mine()->whereNull('archived_at')->where('is_pinned', true)->count(),
            'archived' => $this->mine()->whereNotNull('archived_at')->count(),
        ];
        return view('notes.index', compact('notes', 'summary', 'view'));
    }

    public function create(Request $request): View
    {
        // Deep links from Activity/Calendar prefill an in-text mention rather
        // than the retired single-link dropdown. Inaccessible IDs are ignored.
        $actor = $this->actor();
        $service = app(WorkspaceNoteMentions::class);
        $tokens = [];
        foreach (['activity_id' => 'activity', 'calendar_event_id' => 'calendar'] as $key => $type) {
            $id = (string) $request->query($key, '');
            if (!\Illuminate\Support\Str::isUuid($id)) continue;
            $target = $service->target($type, $id, $actor);
            if (!$target) continue;
            $label = trim(str_replace([']', "\r", "\n"], ' ', (string) $target['title']));
            $label = \Illuminate\Support\Str::limit($label ?: 'Untitled', 100, '');
            $tokens[] = '@[' . ($type === 'activity' ? 'Activity' : 'Calendar') . ':' . $label . '](' . $type . ':' . $id . ')';
        }
        return view('notes.create', ['defaults' => ['body' => implode("\n", $tokens)]]);
    }

    private function validated(Request $request): array
    {
        $id = $this->actor();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:10000'],
            'is_pinned' => ['nullable', 'boolean'],
            'remind_day' => ['nullable', 'date_format:Y-m-d'],
            'remind_time' => ['nullable', 'date_format:H:i'],
            'activity_id' => ['nullable', 'uuid', Rule::exists('activities', 'activity_id')->where('user_id', $id)],
            'calendar_event_id' => ['nullable', 'uuid', Rule::exists('workspace_calendar_events', 'calendar_event_id')->where('owner_user_id', $id)],
        ]);
        // With the new title/body-only page, missing legacy fields are NOT
        // commands to unpin, clear reminders, or remove old link metadata.
        // Explicit legacy API/form inputs remain validated for compatibility.
        if ($request->exists('is_pinned')) $data['is_pinned'] = $request->boolean('is_pinned');
        if ($request->exists('remind_day') || $request->exists('remind_time')) {
            $data['remind_at'] = !empty($data['remind_day'])
                ? $data['remind_day'].' '.(($data['remind_time'] ?? null) ?: '09:00').':00'
                : null;
        }
        unset($data['remind_day'], $data['remind_time']);
        return $data;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $note = DB::transaction(function () use ($data): WorkspacePersonalNote {
            $note = WorkspacePersonalNote::create(['owner_user_id' => $this->actor()] + $data);
            app(WorkspaceNoteMentions::class)->synchronize($note, (string)($note->body ?? ''), $this->actor());
            return $note;
        });
        return redirect()->route('notes.show', ['note' => $note->getKey()])
            ->with('success', 'Personal note created successfully.');
    }

    public function show(WorkspacePersonalNote $note): View
    {
        $note = $this->findMine($note);
        return view('notes.show', compact('note'));
    }

    public function edit(WorkspacePersonalNote $note): View
    {
        $note = $this->findMine($note);
        return view('notes.edit', compact('note'));
    }

    public function update(Request $request, WorkspacePersonalNote $note): RedirectResponse
    {
        $note = $this->findMine($note);
        $data = $this->validated($request);
        DB::transaction(function () use ($note, $data): void {
            $record = $this->mine()->whereKey($note->getKey())->lockForUpdate()->firstOrFail();
            app(WorkspaceNoteMentions::class)->synchronize($record, (string)($data['body'] ?? $record->body ?? ''), $this->actor());
            $data['revision'] = (int)$record->revision + 1;
            $record->update($data);
        });
        return redirect()->route('notes.show', ['note' => $note->getKey()])
            ->with('success', 'Personal note updated successfully.');
    }

    public function togglePin(WorkspacePersonalNote $note): RedirectResponse
    {
        $note = $this->findMine($note);
        DB::transaction(function () use ($note): void {
            $locked = $this->mine()->whereKey($note->getKey())->lockForUpdate()->firstOrFail();
            $locked->update(['is_pinned' => !$locked->is_pinned, 'revision' => (int)$locked->revision + 1]);
        });
        return back()->with('success', 'Note pin updated.');
    }

    public function archive(WorkspacePersonalNote $note): RedirectResponse
    {
        $note = $this->findMine($note);
        DB::transaction(function () use ($note): void {
            $locked = $this->mine()->whereKey($note->getKey())->lockForUpdate()->firstOrFail();
            $locked->update(['archived_at' => now(), 'revision' => (int)$locked->revision + 1]);
        });
        return redirect()->route('notes.index')->with('success', 'Note archived.');
    }

    public function restore(WorkspacePersonalNote $note): RedirectResponse
    {
        $note = $this->findMine($note);
        DB::transaction(function () use ($note): void {
            $locked = $this->mine()->whereKey($note->getKey())->lockForUpdate()->firstOrFail();
            $locked->update(['archived_at' => null, 'revision' => (int)$locked->revision + 1]);
        });
        return redirect()->route('notes.show', ['note' => $note->getKey()])
            ->with('success', 'Note restored.');
    }

    public function destroy(WorkspacePersonalNote $note): RedirectResponse
    {
        $note = $this->findMine($note);
        DB::transaction(fn () => $this->mine()->whereKey($note->getKey())->lockForUpdate()->firstOrFail()->delete());
        return redirect()->route('notes.index')->with('success', 'Personal note deleted.');
    }
}
