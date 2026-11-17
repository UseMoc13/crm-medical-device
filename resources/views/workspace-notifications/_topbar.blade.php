@auth
@php
    $wsnReady = \Illuminate\Support\Facades\Schema::hasTable('workspace_notifications');
    $wsnUnreadCount = $wsnReady ? \App\Models\WorkspaceNotification::query()
        ->where('recipient_user_id', (string) auth()->id())->whereNull('read_at')->count() : 0;
    $wsnRecent = $wsnReady ? \App\Models\WorkspaceNotification::query()
        ->where('recipient_user_id', (string) auth()->id())
        ->orderByDesc('created_at')->orderByDesc('notification_id')->limit(5)->get() : collect();
@endphp
<details class="wsn-topbar" id="wsnTopbar"
    data-poll-url="{{ route('workspace-notifications.poll') }}"
    data-open-base="{{ url('/workspace-notifications') }}"
    data-csrf-token="{{ csrf_token() }}">
    <summary class="icon-btn wsn-bell" aria-label="Notifications, {{ $wsnUnreadCount }} unread" title="Notifications">
        @include('workspace-notifications._bell_icon')
        <span class="wsn-count" id="wsnBadge" @if($wsnUnreadCount === 0) hidden @endif>{{ $wsnUnreadCount > 99 ? '99+' : $wsnUnreadCount }}</span>
    </summary>
    <div class="wsn-dropdown" role="region" aria-label="Recent notifications">
        <div class="wsn-dropdown-head"><strong>Notifications</strong><span class="wsn-dropdown-links"><a href="{{ route('workspace-notifications.settings') }}">Settings</a><a href="{{ route('workspace-notifications.index') }}">View all</a></span></div>
        <div id="wsnRecent" aria-live="polite">
            @forelse($wsnRecent as $notice)
                <form method="POST" action="{{ route('workspace-notifications.open', ['notification' => $notice->getKey()]) }}" class="wsn-recent-item {{ $notice->read_at ? '' : 'is-unread' }}">
                    @csrf
                    <button type="submit" title="Open related item"><span class="wsn-recent-title">{{ $notice->title }}</span><span class="wsn-recent-sub">{{ $notice->message }}</span><small>{{ $notice->created_at?->diffForHumans() }}</small></button>
                </form>
            @empty
                <p class="wsn-empty">No notifications yet.</p>
            @endforelse
        </div>
        <div class="wsn-dropdown-foot"><a id="wsnUnreadLink" href="{{ route('workspace-notifications.index', ['filter'=>'unread']) }}">{{ $wsnUnreadCount }} unread notifications</a></div>
    </div>
</details>
<script src="{{ asset('js/workspace-notifications-poll.js') }}" defer></script>
@endauth
