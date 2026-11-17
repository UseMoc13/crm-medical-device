<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\WorkspaceCalendarEvent;
use App\Models\WorkspaceNoteMention;
use App\Models\WorkspacePersonalNote;
use App\Models\WorkspaceTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * A7.4.10.1-4: plain-text mention references. Only permitted CRM targets are
 * linkable; a private note never grants access to the referenced record.
 * Token: @[Activity: Customer follow-up](activity:UUID)
 */
final class WorkspaceNoteMentions
{
    public const TOKEN = '/@\[(Activity|TO-DO|Calendar):([^\]\r\n]{1,120})\]\((activity|todo|calendar):([0-9a-fA-F-]{36})\)/u';
    private const LIMIT = 40;

    public function parse(string $body): array
    {
        preg_match_all(self::TOKEN, $body, $match, PREG_SET_ORDER);
        $items = [];
        foreach ($match as $m) {
            $type = strtolower($m[1]) === 'to-do' ? 'todo' : strtolower($m[1]);
            $id = strtolower($m[4]);
            if ($type !== $m[3] || !Str::isUuid($id)) continue;
            $items[$type . ':' . $id] = ['type' => $type, 'id' => $id, 'label' => $m[2]];
        }
        if (count($items) > self::LIMIT) {
            throw ValidationException::withMessages(['body' => 'A note supports at most 40 unique mentions.']);
        }
        return array_values($items);
    }

    private function visibleTasks(string $actor): Builder
    {
        return WorkspaceTask::query()->where(function (Builder $q) use ($actor) {
            $q->where('owner_user_id', $actor)->orWhere(function (Builder $work) use ($actor) {
                $work->where('visibility', 'work')->where(function (Builder $w) use ($actor) {
                    $w->whereHas('assignments', fn (Builder $a) => $a->where('assigned_user_id', $actor))
                        ->orWhereExists(function ($sub) use ($actor) {
                            $sub->selectRaw('1')->from('workspace_collaboration_members as ncm')
                                ->whereColumn('ncm.task_id', 'workspace_tasks.task_id')
                                ->where('ncm.user_id', $actor);
                        });
                });
            });
        });
    }

    private function visibleActivities(string $actor): Builder
    {
        return Activity::query()->where(function (Builder $q) use ($actor) {
            $q->where('user_id', $actor)->orWhereExists(function ($sub) use ($actor) {
                $sub->selectRaw('1')->from('workspace_collaboration_members as ncm')
                    ->whereColumn('ncm.activity_id', 'activities.activity_id')
                    ->where('ncm.user_id', $actor);
            });
        });
    }

    /** Returns an actual viewable destination or null, never a guessed URL. */
    public function target(string $type, string $id, string $actor): ?array
    {
        if (!Str::isUuid($id)) return null;
        if ($type === 'activity') {
            $record = $this->visibleActivities($actor)->whereKey($id)->first(['activity_id', 'subject']);
            return $record ? ['title' => (string) $record->subject,
                'url' => route('activities.show', ['activity' => $record->getKey()])] : null;
        }
        if ($type === 'todo') {
            $record = $this->visibleTasks($actor)->whereKey($id)->first(['task_id', 'title']);
            return $record ? ['title' => (string) $record->title,
                'url' => route('todos.show', ['todo' => $record->getKey()])] : null;
        }
        if ($type === 'calendar') {
            $record = WorkspaceCalendarEvent::query()->where('owner_user_id', $actor)
                ->whereKey($id)->first(['calendar_event_id', 'title']);
            return $record ? ['title' => (string) $record->title,
                'url' => route('calendar.show', ['event' => $record->getKey()])] : null;
        }
        return null;
    }

    /** Query time limits also prevent browsing hidden/unrelated records. */
    public function search(string $actor, string $type, string $q): array
    {
        $term = '%' . addcslashes(trim($q), '%_\\') . '%';
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $types = $type === 'all' ? ['activity', 'todo', 'calendar'] : [$type];
        $result = [];
        foreach ($types as $kind) {
            if ($kind === 'activity') {
                $items = $this->visibleActivities($actor)->where('subject', $like, $term)
                    ->orderByDesc('activity_date')->limit(8)->get(['activity_id', 'subject']);
                foreach ($items as $item) $result[] = ['type' => 'activity', 'id' => (string)$item->getKey(), 'title' => $item->subject];
            } elseif ($kind === 'todo') {
                $items = $this->visibleTasks($actor)->where('title', $like, $term)
                    ->orderByDesc('created_at')->limit(8)->get(['task_id', 'title']);
                foreach ($items as $item) $result[] = ['type' => 'todo', 'id' => (string)$item->getKey(), 'title' => $item->title];
            } elseif ($kind === 'calendar') {
                $items = WorkspaceCalendarEvent::query()->where('owner_user_id', $actor)
                    ->where('title', $like, $term)->orderByDesc('starts_at')->limit(8)
                    ->get(['calendar_event_id', 'title']);
                foreach ($items as $item) $result[] = ['type' => 'calendar', 'id' => (string)$item->getKey(), 'title' => $item->title];
            }
        }
        return array_slice($result, 0, 12);
    }

    /** Call only inside transaction holding the parent Note row lock. */
    public function synchronize(WorkspacePersonalNote $note, string $body, string $actor): void
    {
        $references = $this->parse($body);
        foreach ($references as $ref) {
            if (!$this->target($ref['type'], $ref['id'], $actor)) {
                throw ValidationException::withMessages(['body' => 'One or more mentions are inaccessible. Remove them or choose an accessible record.']);
            }
        }
        // No state leaks from non-owner: callers always scope/lock the Note first.
        WorkspaceNoteMention::query()->where('note_id', $note->getKey())->delete();
        foreach ($references as $ref) {
            WorkspaceNoteMention::create(['note_id' => $note->getKey(),
                'target_type' => $ref['type'], 'target_id' => $ref['id']]);
        }
    }

    public function render(string $body, string $actor): string
    {
        // Use preg_replace_callback instead of interpolating untrusted markup.
        $html = ''; $cursor = 0;
        preg_match_all(self::TOKEN, $body, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $i => $capture) {
            [$raw, $offset] = $capture;
            $html .= nl2br(e(substr($body, $cursor, $offset - $cursor)), false);
            $typeLabel = $matches[1][$i][0];
            $type = strtolower($typeLabel) === 'to-do' ? 'todo' : strtolower($typeLabel);
            $targetType = $matches[3][$i][0]; $targetId = strtolower($matches[4][$i][0]);
            $label = e('@' . $typeLabel . ': ' . $matches[2][$i][0]);
            $target = $type === $targetType ? $this->target($type, $targetId, $actor) : null;
            $html .= $target
                ? '<a class="notes-mention-link" href="' . e($target['url']) . '">' . $label . '</a>'
                : '<span class="notes-mention-unavailable" title="Referenced item is unavailable">' . $label . '</span>';
            $cursor = $offset + strlen($raw);
        }
        $html .= nl2br(e(substr($body, $cursor)), false);
        return $html;
    }
}
