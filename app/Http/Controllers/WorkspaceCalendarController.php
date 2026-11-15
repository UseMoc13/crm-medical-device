<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\WorkspaceCalendarEvent;
use App\Models\WorkspaceTask;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkspaceCalendarController extends Controller
{
    private function ownerId(): string
    {
        abort_unless(Auth::guard('web')->check(), 403);
        return (string) Auth::guard('web')->id();
    }

    private function ownedEvent(string $id): WorkspaceCalendarEvent
    {
        return WorkspaceCalendarEvent::query()
            ->where('owner_user_id', $this->ownerId())
            ->whereKey($id)->firstOrFail();
    }

    public function index(Request $request): View
    {
        $owner = $this->ownerId();
        $view = $request->query('view', 'all');
        if (!in_array($view, ['all', 'work', 'personal'], true)) $view = 'all';

        $monthValue = (string) $request->query('month', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthValue)) $monthValue = now()->format('Y-m');
        $month = CarbonImmutable::createFromFormat('!Y-m', $monthValue);
        $start = $month->startOfMonth()->startOfDay();
        $end = $month->endOfMonth()->endOfDay();
        $events = [];

        if ($view !== 'work') {
            $personal = WorkspaceCalendarEvent::query()
                ->where('owner_user_id', $owner)
                ->where('starts_at', '<=', $end)
                ->where(function ($q) use ($start) {
                    $q->where('starts_at', '>=', $start)->orWhere('ends_at', '>=', $start);
                })->orderBy('starts_at')->get();
            foreach ($personal as $event) {
                $events[] = [
                    'kind' => 'personal', 'title' => $event->title,
                    'start' => $event->starts_at, 'end' => $event->ends_at,
                    'url' => route('calendar.show', $event->getKey()),
                    'subtitle' => $event->location ?: 'Personal event',
                    'all_day' => (bool) $event->is_all_day,
                ];
            }
        }

        if ($view !== 'personal') {
            // A7.4 uses owner-only work visibility. Cross-user scope is deliberately excluded.
            foreach (Activity::query()->where('user_id', $owner)
                ->whereBetween('activity_date', [$start, $end])->orderBy('activity_date')->get() as $activity) {
                $events[] = [
                    'kind' => 'activity', 'title' => $activity->subject,
                    'start' => $activity->activity_date, 'end' => null,
                    'url' => route('activities.show', $activity->getKey()),
                    'subtitle' => $activity->activity_type ?: 'Activity', 'all_day' => false,
                ];
            }
            foreach (WorkspaceTask::query()->where('owner_user_id', $owner)
                ->whereNotNull('due_at')->whereBetween('due_at', [$start, $end])
                ->whereNotIn('status', ['cancelled'])->orderBy('due_at')->get() as $task) {
                $events[] = [
                    'kind' => 'todo', 'title' => $task->title,
                    'start' => $task->due_at, 'end' => null,
                    'url' => route('todos.show', $task->getKey()),
                    'subtitle' => $task->status === 'completed' ? 'Completed task' : 'Task deadline',
                    'all_day' => false,
                ];
            }
        }

        $byDay = [];
        foreach ($events as $event) {
            $date = CarbonImmutable::parse($event['start']);
            $last = $event['kind'] === 'personal' && $event['end']
                ? CarbonImmutable::parse($event['end']) : $date;
            // Cap multi-day rendering to the visible month and prevent unbounded loops.
            $cursor = $date->startOfDay()->max($start);
            $stop = $last->startOfDay()->min($end->startOfDay());
            for ($i = 0; $cursor->lte($stop) && $i < 32; $i++, $cursor = $cursor->addDay()) {
                $byDay[$cursor->format('Y-m-d')][] = $event;
            }
        }
        foreach ($byDay as &$dayEvents) {
            usort($dayEvents, static fn ($a, $b) => $a['start'] <=> $b['start']);
        }
        unset($dayEvents);

        return view('calendar.index', compact('month', 'view', 'byDay', 'events'));
    }

    public function create(Request $request): View
    {
        $this->ownerId();
        $prefillDate = (string) $request->query('date', '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $prefillDate) || !strtotime($prefillDate)) $prefillDate = now()->format('Y-m-d');
        return view('calendar.create', compact('prefillDate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $this->ownerId();
        $data = $this->validateEvent($request);
        $event = WorkspaceCalendarEvent::create(['owner_user_id' => $userId] + $data);
        // HasUuids assigns calendar_event_id before insert. Pass its UUID explicitly.
        return redirect()->route('calendar.show', ['event' => $event->calendar_event_id])
            ->with('success', 'Personal event created successfully.');
    }

    public function show(string $event): View
    {
        $event = $this->ownedEvent($event);
        return view('calendar.show', compact('event'));
    }

    public function edit(string $event): View
    {
        $event = $this->ownedEvent($event);
        return view('calendar.edit', compact('event'));
    }

    public function update(Request $request, string $event): RedirectResponse
    {
        $model = $this->ownedEvent($event);
        $model->update($this->validateEvent($request));
        return redirect()->route('calendar.show', ['event' => $model->calendar_event_id])
            ->with('success', 'Personal event updated successfully.');
    }

    public function destroy(string $event): RedirectResponse
    {
        $model = $this->ownedEvent($event);
        $model->delete();
        return redirect()->route('calendar.index')->with('success', 'Personal event deleted successfully.');
    }

    private function validateEvent(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:10000'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_all_day' => ['sometimes', Rule::in(['0','1', 0, 1])],
        ]);
        $validated['visibility'] = 'personal'; // Never trust browser visibility.
        $validated['is_all_day'] = $request->boolean('is_all_day');
        return $validated;
    }
}
