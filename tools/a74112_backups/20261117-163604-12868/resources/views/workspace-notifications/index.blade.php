@extends('layouts.app')
@section('title','Notifications')
@section('content')
<div class="page-head">
    <div><h1>Notifications</h1><p>Review workspace updates, approvals, and upcoming reminders.</p></div>
    <div class="actions"><a class="btn" href="{{ route('workspace-notifications.index', ['filter'=>'unread']) }}">Unread ({{ $unreadCount }})</a><a class="btn" href="{{ route('workspace-notifications.index') }}">All</a></div>
</div>
<section class="card wsn-inbox">
    <div class="card-head wsn-card-head"><div><h3>{{ $filter === 'unread' ? 'Unread Notifications' : 'Notification Inbox' }}</h3><p>{{ $notifications->total() }} items</p></div>
        <form method="POST" action="{{ route('workspace-notifications.read-all') }}">@csrf @method('PATCH')<button class="btn" type="submit" {{ $unreadCount === 0 ? 'disabled' : '' }}>Mark All Read</button></form>
    </div>
    <div class="card-body wsn-inbox-body">
        @forelse($notifications as $notice)
            <div class="wsn-inbox-item {{ $notice->read_at ? '' : 'is-unread' }}">
                <div class="wsn-inbox-dot" aria-hidden="true"></div>
                <div class="wsn-inbox-copy">
                    <strong>{{ $notice->title }}</strong><p>{{ $notice->message }}</p>
                    <small>{{ $notice->created_at?->format('d M Y, H:i') }} · {{ $notice->read_at ? 'Read' : 'Unread' }}</small>
                </div>
                <div class="wsn-actions">
                    <form method="POST" action="{{ route('workspace-notifications.open', ['notification'=>$notice->getKey()]) }}">@csrf<button type="submit" class="btn primary">Open</button></form>
                    @unless($notice->read_at)
                        <form method="POST" action="{{ route('workspace-notifications.read', ['notification'=>$notice->getKey()]) }}">@csrf @method('PATCH')<button type="submit" class="btn">Mark Read</button></form>
                    @endunless
                </div>
            </div>
        @empty
            <div class="wsn-inbox-empty">@include('workspace-notifications._bell_icon')<h4>All caught up</h4><p>New assignments, collaboration invitations, review decisions and reminders will appear here.</p></div>
        @endforelse
    </div>
    @if($notifications->hasPages())<div class="wsn-pagination">{{ $notifications->links() }}</div>@endif
</section>
@endsection
