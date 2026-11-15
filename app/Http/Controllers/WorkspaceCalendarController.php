<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\WorkspaceCalendarEvent;
use App\Models\WorkspaceCalendarDayExclusion;
use App\Models\WorkspaceTask;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
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
        $view = (string) $request->query('view', 'all');
        if (!in_array($view, ['all', 'work', 'personal'], true)) $view = 'all';

        $monthValue = (string) $request->query('month', now()->format('Y-m'));
        if (!preg_match('/^(20\d{2})-(0[1-9]|1[0-2])$/', $monthValue)) $monthValue = now()->format('Y-m');
        $month = CarbonImmutable::createFromFormat('!Y-m', $monthValue);
        $start = $month->startOfMonth()->startOfDay();
        $end = $month->endOfMonth()->endOfDay();
        $events = [];

        if ($view !== 'work') {
            $personal = WorkspaceCalendarEvent::query()
                ->with('excludedDays')
                ->where('owner_user_id', $owner)
                ->where('starts_at', '<=', $end)
                ->where(function ($q) use ($start) {
                    $q->where('starts_at', '>=', $start)->orWhere('ends_at', '>=', $start);
                })->orderBy('starts_at')->get();
            foreach ($personal as $event) {
                $events[] = [
                    'kind' => 'personal', 'title' => $event->title,
                    'start' => $event->starts_at, 'end' => $event->ends_at,
                    'url' => route('calendar.show', ['event' => $event->getKey()]),
                    'subtitle' => $event->location ?: 'Personal event',
                    'all_day' => (bool) $event->is_all_day,
                    'excluded' => $event->excludedDays->pluck('excluded_date')
                        ->map(fn ($d) => CarbonImmutable::parse($d)->format('Y-m-d'))->all(),
                ];
            }
        }

        if ($view !== 'personal') {
            // Work activities are owner-only until CRM Calendar RBAC scope is finalized.
            foreach (Activity::query()->where('user_id', $owner)
                ->whereBetween('activity_date', [$start, $end])->orderBy('activity_date')->get() as $activity) {
                $events[] = [
                    'kind' => 'activity', 'title' => $activity->subject,
                    'start' => $activity->activity_date, 'end' => null,
                    'url' => route('activities.show', ['activity' => $activity->getKey()]),
                    'subtitle' => $activity->activity_type ?: 'Activity', 'all_day' => false,
                ];
            }
        }

        // Tasks are classified by their own visibility. Personal tasks appear in Personal.
        // Work tasks appear in Work; All includes both.
        $tasks = WorkspaceTask::query()->where('owner_user_id', $owner)
            ->whereNotNull('due_at')->whereBetween('due_at', [$start, $end])
            ->where('status', '!=', 'cancelled');
        if ($view !== 'all') $tasks->where('visibility', $view);
        foreach ($tasks->orderBy('due_at')->get() as $task) {
            $events[] = [
                'kind' => 'todo', 'title' => $task->title,
                'start' => $task->due_at, 'end' => null,
                'url' => route('todos.show', ['todo' => $task->getKey()]),
                'subtitle' => ($task->visibility === 'personal' ? 'Personal · ' : 'Work · ')
                    . ($task->status === 'completed' ? 'Completed task' : 'Task deadline'),
                'all_day' => false,
            ];
        }

        $byDay = [];
        foreach ($events as $event) {
            $date = CarbonImmutable::parse($event['start']);
            $last = $event['kind'] === 'personal' && $event['end']
                ? CarbonImmutable::parse($event['end']) : $date;
            $cursor = $date->startOfDay()->max($start);
            $stop = $last->startOfDay()->min($end->startOfDay());
            for ($i = 0; $cursor->lte($stop) && $i < 32; $i++, $cursor = $cursor->addDay()) {
                $key = $cursor->format('Y-m-d');
                if (in_array($key, $event['excluded'] ?? [], true)) continue;
                $instance = $event;
                $instance['date'] = $key;
                $instance['ongoing'] = !$date->isSameDay($cursor);
                $instance['time_label'] = $event['all_day'] ? 'All day'
                    : ($instance['ongoing'] ? '00:00' : $date->format('H:i'));
                $byDay[$key][] = $instance;
            }
        }
        foreach ($byDay as &$dayEvents) {
            usort($dayEvents, static function ($a, $b) {
                $aTime = $a['all_day'] ? '0' : '1' . $a['time_label'];
                $bTime = $b['all_day'] ? '0' : '1' . $b['time_label'];
                return strcmp($aTime, $bTime) ?: strcmp($a['title'], $b['title']);
            });
        }
        unset($dayEvents);

        return view('calendar.index', compact('month', 'view', 'byDay'));
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
        return redirect()->route('calendar.show', ['event' => $event->calendar_event_id])
            ->with('success', 'Personal event created successfully.');
    }

    public function show(string $event): View
    {
        $event = $this->ownedEvent($event)->load('excludedDays');
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
        $data = $this->validateEvent($request);
        DB::transaction(function () use ($model, $data) {
            $model->update($data);
            // Old excluded dates must not silently survive edits to the date range.
            // Only clear exceptions if the schedule itself changes.
            if ($model->wasChanged(['starts_at', 'ends_at', 'is_all_day'])) {
                $model->excludedDays()->delete();
            }
        });
        return redirect()->route('calendar.show', ['event' => $model->calendar_event_id])
            ->with('success', 'Personal event updated successfully.');
    }

    public function destroy(string $event): RedirectResponse
    {
        $model = $this->ownedEvent($event);
        $month = $model->starts_at->format('Y-m');
        $model->delete();
        return redirect()->route('calendar.index', ['month' => $month])
            ->with('success', 'Entire personal event deleted successfully.');
    }

    /**
     * Backward compatibility with A7.4.2 single-date endpoint.
     * The new mini calendar submits to destroyDays() instead.
     */
    public function destroyDay(Request $request, string $event): RedirectResponse
    {
        $validated = $request->validate(['date' => ['required', 'date_format:Y-m-d']]);

        return $this->excludeEventDates($event, [$validated['date']]);
    }

    /**
     * Exclude multiple calendar dates from an owned all-day event.
     * This never modifies Work/TO-DO records or another user's events.
     */
    public function destroyDays(Request $request, string $event): RedirectResponse
    {
        $validated = $request->validate([
            'dates' => ['required', 'array', 'min:1', 'max:366'],
            'dates.*' => ['required', 'date_format:Y-m-d', 'distinct'],
        ]);

        return $this->excludeEventDates($event, $validated['dates']);
    }

    private function excludeEventDates(string $eventId, array $dates): RedirectResponse
    {
        $model = $this->ownedEvent($eventId);
        abort_unless($model->is_all_day, 422, 'Date removal is only available for all-day events.');

        $first = CarbonImmutable::parse($model->starts_at)->startOfDay();
        $last = CarbonImmutable::parse($model->ends_at ?? $model->starts_at)->startOfDay();
        $uniqueDates = array_values(array_unique($dates));
        foreach ($uniqueDates as $day) {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $day);
            if (!$date || $date->format('Y-m-d') !== $day || !$date->betweenIncluded($first, $last)) {
                throw ValidationException::withMessages([
                    'dates' => 'Selected dates must belong to the event schedule.',
                ]);
            }
        }

        // Lock the event so two simultaneous bulk requests cannot incorrectly
        // count remaining dates or delete a changed event schedule.
        [$removedAll, $added] = DB::transaction(function () use ($model, $uniqueDates) {
            $locked = WorkspaceCalendarEvent::query()
                ->where('owner_user_id', $this->ownerId())
                ->whereKey($model->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($locked->is_all_day, 422, 'Event is no longer an all-day event.');
            $first = CarbonImmutable::parse($locked->starts_at)->startOfDay();
            $last = CarbonImmutable::parse($locked->ends_at ?? $locked->starts_at)->startOfDay();
            foreach ($uniqueDates as $day) {
                if (!CarbonImmutable::createFromFormat('!Y-m-d', $day)->betweenIncluded($first, $last)) {
                    throw ValidationException::withMessages([
                        'dates' => 'Event dates have changed. Reload the page and try again.',
                    ]);
                }
            }

            $rows = array_map(static fn (string $day) => [
                'exclusion_id' => (string) Str::uuid(),
                'calendar_event_id' => $locked->getKey(),
                'excluded_date' => $day,
            ], $uniqueDates);
            $added = DB::table('workspace_calendar_day_exclusions')->insertOrIgnore($rows);
            $totalDays = (int) $first->diffInDays($last) + 1;
            $excludedCount = $locked->excludedDays()->count();
            if ($excludedCount >= $totalDays) {
                $locked->delete();
                return [true, $added];
            }
            return [false, $added];
        });

        if ($removedAll) {
            return redirect()->route('calendar.index', [
                'month' => $first->format('Y-m'),
                'view' => 'personal',
            ])->with('success', 'All dates were removed. The event is now deleted.');
        }

        return redirect()->route('calendar.show', ['event' => $model->getKey()])
            ->with('success', $added > 0
                ? $added . ' date(s) removed from this event. Other dates remain unchanged.'
                : 'Selected dates were already removed.');
    }

    private function validateEvent(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:10000'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_all_day' => ['sometimes', Rule::in(['0', '1', 0, 1])],
        ]);
        $validated['visibility'] = 'personal';
        $validated['is_all_day'] = $request->boolean('is_all_day');
        return $validated;
    }
}
