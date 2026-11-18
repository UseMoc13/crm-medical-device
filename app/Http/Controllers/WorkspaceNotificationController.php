<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceNotification;
use App\Models\WorkspaceNotificationPreference;
use App\Services\WorkspaceNotificationCenter;
use App\Services\WorkspaceNotificationPreferences;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class WorkspaceNotificationController extends Controller
{
    private function mine(): Builder
    {
        abort_unless(Auth::guard('web')->check(), 401);
        return WorkspaceNotification::query()->where('recipient_user_id', (string) Auth::guard('web')->id());
    }

    public function index(Request $request): View
    {
        $input = $request->validate(['filter' => ['nullable', Rule::in(['all', 'unread'])]]);
        $filter = $input['filter'] ?? 'all';
        $notifications = $this->mine()
            ->when($filter === 'unread', fn (Builder $q) => $q->whereNull('read_at'))
            ->orderByDesc('created_at')->orderByDesc('notification_id')
            ->paginate(20)->withQueryString();
        $unreadCount = $this->mine()->whereNull('read_at')->count();
        return view('workspace-notifications.index', compact('notifications', 'filter', 'unreadCount'));
    }

    /** Read-only polling. Response contains generic text and no raw target identifiers. */
    public function poll(): JsonResponse
    {
        $count = $this->mine()->whereNull('read_at')->count();
        $recent = $this->mine()->orderByDesc('created_at')->orderByDesc('notification_id')
            ->limit(5)->get();
        return response()->json([
            'unread_count' => $count,
            'notifications' => $recent->map(fn (WorkspaceNotification $notice) => [
                'id' => (string) $notice->getKey(),
                'title' => (string) $notice->title,
                'message' => (string) $notice->message,
                'created_label' => $notice->created_at?->diffForHumans() ?? '',
                'is_unread' => $notice->read_at === null,
            ])->values()->all(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function settings(WorkspaceNotificationPreferences $preferences): View
    {
        $this->mine();
        $settings = $preferences->forUser((string) Auth::guard('web')->id());
        return view('workspace-notifications.settings', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $rules = ['reminder_lead_minutes' => ['required', 'integer', Rule::in(WorkspaceNotificationPreferences::LEAD_OPTIONS)]];
        foreach (WorkspaceNotificationPreferences::FLAGS as $field) {
            $rules[$field] = ['sometimes', 'boolean'];
        }
        $data = $request->validate($rules);
        $payload = ['reminder_lead_minutes' => (int) $data['reminder_lead_minutes']];
        foreach (WorkspaceNotificationPreferences::FLAGS as $field) {
            if ($field === 'crm_status_enabled' && !\Illuminate\Support\Facades\Schema::hasColumn('workspace_notification_preferences', 'crm_status_enabled')) continue;
            $payload[$field] = $request->boolean($field);
        }
        WorkspaceNotificationPreference::query()->updateOrCreate(
            ['user_id' => (string) Auth::guard('web')->id()], $payload
        );
        return redirect()->route('workspace-notifications.settings')
            ->with('success', 'Notification preferences saved.');
    }

    public function open(WorkspaceNotification $notification, WorkspaceNotificationCenter $center): RedirectResponse
    {
        $record = $this->mine()->whereKey($notification->getKey())->firstOrFail();
        if (!$record->read_at) $record->update(['read_at' => now()]);
        if (!$center->canOpen(Auth::guard('web')->user(), $record)) {
            return redirect()->route('workspace-notifications.index')
                ->with('error', 'The referenced item is no longer accessible.');
        }
        return redirect()->to($center->urlFor($record));
    }

    public function read(WorkspaceNotification $notification): RedirectResponse
    {
        $record = $this->mine()->whereKey($notification->getKey())->firstOrFail();
        if (!$record->read_at) $record->update(['read_at' => now()]);
        return back()->with('success', 'Notification marked as read.');
    }

    public function readAll(): RedirectResponse
    {
        $this->mine()->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
