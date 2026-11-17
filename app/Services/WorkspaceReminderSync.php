<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A7.4.11.2: per-user reminder lead time and category switches.
 * Note.remind_at is its explicitly chosen alert time; the lead setting
 * applies to Activity, Personal Calendar and TO-DO deadlines only.
 * Per-user, per-schedule unique keys ensure repeat scans are idempotent.
 */
final class WorkspaceReminderSync
{
    public function run(bool $dryRun = false): array
    {
        foreach (['workspace_notifications', 'workspace_personal_notes',
            'workspace_calendar_events', 'activities', 'workspace_tasks'] as $table) {
            if (!Schema::hasTable($table)) {
                throw new \RuntimeException("Missing table {$table}; run php artisan migrate first.");
            }
        }

        $now = now();
        $from = $now->copy()->subMinutes(15); // scheduler runs every 15 minutes
        $until = $now->copy()->addDay(); // largest configurable lead time
        $noteUntil = $now->copy()->addMinutes(15);
        $counts = ['note' => 0, 'calendar' => 0, 'activity' => 0, 'todo' => 0];
        $center = app(WorkspaceNotificationCenter::class);
        $preferences = app(WorkspaceNotificationPreferences::class);
        $cache = [];
        $setting = static function (string $id) use (&$cache, $preferences): array {
            return $cache[$id] ??= $preferences->forUser($id);
        };

        $send = function (string $recipient, string $type, string $id, $timestamp) use (
            $center, &$counts, $dryRun, $now, $from, $setting
        ): void {
            if ($recipient === '') return;
            $pref = $setting($recipient);
            if (!$pref['reminder_'.$type.'_enabled']) return;
            $time = \Illuminate\Support\Carbon::parse($timestamp);
            if ($time->lt($from)) return;
            // Notes use the explicit remind_at and a 15-minute scan window.
            $lead = $type === 'note' ? 15 : $pref['reminder_lead_minutes'];
            if ($time->gt($now->copy()->addMinutes($lead))) return;
            $title = match ($type) {
                'note' => 'Personal note reminder',
                'calendar' => 'Personal calendar reminder',
                'activity' => 'Upcoming activity',
                'todo' => 'Upcoming TO-DO deadline',
            };
            $message = match ($type) {
                'note' => 'A saved note reminder is due.',
                'calendar' => 'Your personal calendar event is coming up.',
                'activity' => 'An activity on your calendar is coming up.',
                'todo' => 'A TO-DO deadline is approaching.',
            };
            $key = 'remind:'.hash('sha256', $type.':'.$id.':'.$time->format('Y-m-d H:i:s'));
            if ($dryRun) {
                if (DB::table('users')->where('user_id', $recipient)->where('status', 'active')->exists()
                    && !DB::table('workspace_notifications')->where('recipient_user_id', $recipient)
                        ->where('dedupe_key', $key)->exists()) $counts[$type]++;
                return;
            }
            if ($center->send($recipient, 'reminder', $type, $id, $title, $message, $key)) $counts[$type]++;
        };

        foreach (DB::table('workspace_personal_notes')->whereNull('archived_at')
            ->whereBetween('remind_at', [$from, $noteUntil])->limit(500)
            ->get(['note_id','owner_user_id','remind_at']) as $note) {
            $send((string) $note->owner_user_id, 'note', (string) $note->note_id, $note->remind_at);
        }
        foreach (DB::table('workspace_calendar_events')->whereBetween('starts_at', [$from, $until])
            ->limit(500)->get(['calendar_event_id','owner_user_id','starts_at']) as $event) {
            $send((string) $event->owner_user_id, 'calendar', (string) $event->calendar_event_id, $event->starts_at);
        }
        foreach (DB::table('activities')->whereBetween('activity_date', [$from, $until])
            ->limit(500)->get(['activity_id','user_id','activity_date']) as $activity) {
            $recipients = [(string) $activity->user_id];
            if (Schema::hasTable('workspace_collaboration_members')) {
                $recipients = array_merge($recipients, DB::table('workspace_collaboration_members')
                    ->where('activity_id', $activity->activity_id)->pluck('user_id')->map('strval')->all());
            }
            foreach (array_unique($recipients) as $recipient) {
                $send($recipient, 'activity', (string) $activity->activity_id, $activity->activity_date);
            }
        }
        foreach (DB::table('workspace_tasks')->whereBetween('due_at', [$from, $until])
            ->whereNotIn('status', ['completed','cancelled'])->limit(500)
            ->get(['task_id','owner_user_id','visibility','due_at']) as $task) {
            $recipients = [(string) $task->owner_user_id];
            if ($task->visibility === 'work') {
                $recipients = array_merge($recipients, DB::table('workspace_task_assignments')
                    ->where('task_id', $task->task_id)->pluck('assigned_user_id')->map('strval')->all());
                if (Schema::hasTable('workspace_collaboration_members')) {
                    $recipients = array_merge($recipients, DB::table('workspace_collaboration_members')
                        ->where('task_id', $task->task_id)->pluck('user_id')->map('strval')->all());
                }
            }
            foreach (array_unique($recipients) as $recipient) {
                $send($recipient, 'todo', (string) $task->task_id, $task->due_at);
            }
        }
        return $counts;
    }
}
