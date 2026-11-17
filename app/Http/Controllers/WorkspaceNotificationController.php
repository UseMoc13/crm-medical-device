<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceNotification;
use App\Services\WorkspaceNotificationCenter;
use Illuminate\Database\Eloquent\Builder;
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
