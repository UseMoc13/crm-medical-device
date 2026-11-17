<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent reminder scan: past 60 minutes through next 24 hours, capped per
 * category for predictable runtime. Run every 15 minutes via Laravel scheduler.
 * No reminder is sent to people outside the current visibility grants.
 */
final class WorkspaceReminderSync
{
    public function run(bool $dryRun = false): array
    {
        foreach (['workspace_notifications', 'workspace_personal_notes',
            'workspace_calendar_events', 'activities', 'workspace_tasks'] as $table) {
            if (!Schema::hasTable($table)) throw new \RuntimeException("Missing table {$table}; run php artisan migrate first.");
        }
        $now = now();
        $from = $now->copy()->subHour();
        $until = $now->copy()->addDay();
        $types = ['note' => 0, 'calendar' => 0, 'activity' => 0, 'todo' => 0];
        $center = app(WorkspaceNotificationCenter::class);
        $send = function (string $recipient, string $type, string $id, string $eventTime) use ($center, &$types, $dryRun): void {
            $label = match ($type) {
                'note' => 'Personal note reminder',
                'calendar' => 'Personal calendar reminder',
                'activity' => 'Upcoming activity',
                'todo' => 'Upcoming TO-DO deadline',
            };
            $message = match ($type) {
                'note' => 'A saved note reminder is coming up.',
                'calendar' => 'Your personal calendar event is coming up.',
                'activity' => 'An activity on your calendar is coming up.',
                'todo' => 'A TO-DO deadline is approaching.',
            };
            // A schedule edit changes this key, while repeated scans stay idempotent.
            $key = 'remind:'.hash('sha256', $type.':'.$id.':'.$eventTime);
            if ($dryRun) {
                if (DB::table('users')->where('user_id', $recipient)->where('status', 'active')->exists()
                    && !DB::table('workspace_notifications')->where('recipient_user_id', $recipient)
                        ->where('dedupe_key', $key)->exists()) $types[$type]++;
                return;
            }
            if ($center->send($recipient, 'reminder', $type, $id, $label, $message, $key)) $types[$type]++;
        };

        $notes = DB::table('workspace_personal_notes')->whereNull('archived_at')
            ->whereBetween('remind_at', [$from, $until])->limit(500)
            ->get(['note_id','owner_user_id','remind_at']);
        foreach ($notes as $note) $send((string)$note->owner_user_id, 'note', (string)$note->note_id, (string)$note->remind_at);

        $events = DB::table('workspace_calendar_events')
            ->whereBetween('starts_at', [$from, $until])->limit(500)
            ->get(['calendar_event_id','owner_user_id','starts_at']);
        foreach ($events as $event) $send((string)$event->owner_user_id, 'calendar', (string)$event->calendar_event_id, (string)$event->starts_at);

        $activities = DB::table('activities')->whereBetween('activity_date', [$from, $until])
            ->limit(500)->get(['activity_id','user_id','activity_date']);
        foreach ($activities as $activity) {
            $recipients = [(string)$activity->user_id];
            if (Schema::hasTable('workspace_collaboration_members')) {
                $recipients = array_merge($recipients, DB::table('workspace_collaboration_members')
                    ->where('activity_id', $activity->activity_id)->pluck('user_id')->map(fn($id)=>(string)$id)->all());
            }
            foreach (array_unique($recipients) as $recipient) $send($recipient, 'activity', (string)$activity->activity_id, (string)$activity->activity_date);
        }

        $tasks = DB::table('workspace_tasks')->whereBetween('due_at', [$from, $until])
            ->whereNotIn('status',['completed','cancelled'])->limit(500)
            ->get(['task_id','owner_user_id','visibility','due_at']);
        foreach ($tasks as $task) {
            $recipients = [(string)$task->owner_user_id];
            if ($task->visibility === 'work') {
                $recipients = array_merge($recipients, DB::table('workspace_task_assignments')
                    ->where('task_id', $task->task_id)->pluck('assigned_user_id')->map(fn($id)=>(string)$id)->all());
                if (Schema::hasTable('workspace_collaboration_members')) {
                    $recipients = array_merge($recipients, DB::table('workspace_collaboration_members')
                        ->where('task_id', $task->task_id)->pluck('user_id')->map(fn($id)=>(string)$id)->all());
                }
            }
            foreach (array_unique($recipients) as $recipient) $send($recipient, 'todo', (string)$task->task_id, (string)$task->due_at);
        }
        return $types;
    }
}
