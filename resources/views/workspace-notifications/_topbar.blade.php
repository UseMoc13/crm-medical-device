@auth
@php
    $wsnReady = \Illuminate\Support\Facades\Schema::hasTable('workspace_notifications');
    $wsnUnreadCount = $wsnReady ? \App\Models\WorkspaceNotification::query()
        ->where('recipient_user_id', (string) auth()->id())->whereNull('read_at')->count() : 0;
    $wsnRecent = $wsnReady ? \App\Models\WorkspaceNotification::query()
        ->where('recipient_user_id', (string) auth()->id())
        ->latest('created_at')->limit(5)->get() : collect();
@endphp
<details class="wsn-topbar" id="wsnTopbar">
    <summary class="icon-btn wsn-bell" aria-label="Notifications, {{ $wsnUnreadCount }} unread" title="Notifications">
        <span aria-hidden="true">🔔</span>
        @if($wsnUnreadCount > 0)<span class="wsn-count">{{ $wsnUnreadCount > 99 ? '99+' : $wsnUnreadCount }}</span>@endif
    </summary>
    <div class="wsn-dropdown" role="region" aria-label="Recent notifications">
        <div class="wsn-dropdown-head"><strong>Notifications</strong><a href="{{ route('workspace-notifications.index') }}">View all</a></div>
        @forelse($wsnRecent as $notice)
            <form method="POST" action="{{ route('workspace-notifications.open', ['notification' => $notice->getKey()]) }}" class="wsn-recent-item {{ $notice->read_at ? '' : 'is-unread' }}">
                @csrf
                <button type="submit" title="Open related item"><span class="wsn-recent-title">{{ $notice->title }}</span><span class="wsn-recent-sub">{{ $notice->message }}</span><small>{{ $notice->created_at?->diffForHumans() }}</small></button>
            </form>
        @empty
            <p class="wsn-empty">No notifications yet.</p>
        @endforelse
        <div class="wsn-dropdown-foot"><a href="{{ route('workspace-notifications.index', ['filter'=>'unread']) }}">{{ $wsnUnreadCount }} unread notifications</a></div>
    </div>
</details>
@endauth
