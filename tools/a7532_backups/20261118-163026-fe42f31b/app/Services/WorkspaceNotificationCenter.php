<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkspaceNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Minimal, private, in-app workspace notifications.
 * Generic notification copy avoids leaking titles / private file names.
 * A notification never grants access to its referenced CRM record.
 */
final class WorkspaceNotificationCenter
{
    private const TARGET_ROUTES = [
        'todo' => ['todos.show', 'todo'],
        'activity' => ['activities.show', 'activity'],
        'calendar' => ['calendar.show', 'event'],
        'note' => ['notes.show', 'note'],
    ];

    public function send(
        string $recipientId,
        string $kind,
        string $targetType,
        string $targetId,
        string $title,
        string $message,
        string $dedupeKey,
        ?string $actorId = null,
    ): bool {
        if (!Schema::hasTable('workspace_notifications') || !isset(self::TARGET_ROUTES[$targetType]) || !Str::isUuid($recipientId)
            || !Str::isUuid($targetId) || trim($dedupeKey) === '') {
            return false;
        }
        // Do not send to suspended or deleted recipients. Recheck on every send.
        if (!DB::table('users')->where('user_id', $recipientId)
            ->where('status', 'active')->exists()) {
            return false;
        }
        // Per-recipient preference is applied to *new* notifications only; existing inbox rows stay intact.
        if (!app(WorkspaceNotificationPreferences::class)->allows($recipientId, $kind, $targetType)) return false;
        return DB::table('workspace_notifications')->insertOrIgnore([
            'notification_id' => (string) Str::uuid(),
            'recipient_user_id' => $recipientId,
            'actor_user_id' => $actorId,
            'kind' => Str::limit($kind, 60, ''),
            'title' => Str::limit($title, 160, ''),
            'message' => Str::limit($message, 255, ''),
            'target_type' => $targetType,
            'target_id' => $targetId,
            'dedupe_key' => Str::limit($dedupeKey, 160, ''),
            'created_at' => now(),
        ]) === 1;
    }

    public function canOpen(User $user, WorkspaceNotification $notice): bool
    {
        $actor = (string) $user->getKey();
        if ($actor !== (string) $notice->recipient_user_id) return false;
        $id = (string) $notice->target_id;
        return match ($notice->target_type) {
            'note' => DB::table('workspace_personal_notes')
                ->where('note_id', $id)->where('owner_user_id', $actor)->exists(),
            'calendar' => DB::table('workspace_calendar_events')
                ->where('calendar_event_id', $id)->where('owner_user_id', $actor)->exists(),
            'activity' => DB::table('activities')->where('activity_id', $id)
                ->where(function ($q) use ($actor, $id) {
                    $q->where('user_id', $actor)->orWhereExists(function ($members) use ($actor, $id) {
                        $members->selectRaw('1')->from('workspace_collaboration_members')
                            ->where('activity_id', $id)->where('user_id', $actor);
                    });
                })->exists(),
            'todo' => DB::table('workspace_tasks')->where('task_id', $id)
                ->where(function ($q) use ($actor, $id) {
                    $q->where('owner_user_id', $actor)
                        ->orWhere(function ($work) use ($actor, $id) {
                            $work->where('visibility', 'work')
                                ->where(function ($shared) use ($actor, $id) {
                                    $shared->whereExists(function ($assignments) use ($actor, $id) {
                                        $assignments->selectRaw('1')->from('workspace_task_assignments')
                                            ->where('task_id', $id)->where('assigned_user_id', $actor);
                                    })->orWhereExists(function ($members) use ($actor, $id) {
                                        $members->selectRaw('1')->from('workspace_collaboration_members')
                                            ->where('task_id', $id)->where('user_id', $actor);
                                    });
                                });
                        });
                })->exists(),
            default => false,
        };
    }

    public function urlFor(WorkspaceNotification $notice): string
    {
        [$routeName, $param] = self::TARGET_ROUTES[$notice->target_type]
            ?? abort(404);
        return route($routeName, [$param => $notice->target_id]);
    }
}
