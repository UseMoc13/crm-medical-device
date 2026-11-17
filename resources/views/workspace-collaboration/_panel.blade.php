@php
    /** @var \App\Models\Activity|\App\Models\WorkspaceTask $entity */
    $a748 = app(\App\Services\WorkspaceCollaborationAccess::class);
    $entityType = $entityType ?? $a748->type($entity);
    $a748Role = $a748->assertVisible($entity);
    $a748Creator = $a748->isOwner($entity);
    $a748Entries = $a748->entries($entity)->with('author')->orderByDesc('created_at')->limit(50)->get();
    $a748Members = $a748Creator ? $a748->members($entity)->with('user')->orderBy('created_at')->get() : collect();
    $a748Prefix = $entityType === 'activity' ? 'activities' : 'todos';
    $a748Key = $entityType === 'activity' ? 'activity' : 'todo';
    $a748Params = [$a748Key => $entity->getKey()];
@endphp
@include('workspace-collaboration._style')
<section class="a748-shell" aria-label="Activity and To-Do collaboration">
    @if($errors->any())
        <div class="a748-alert" role="alert"><strong>Unable to save collaboration update</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="a748-layout">
        <div class="card">
            <div class="card-head"><div><h3>Collaboration &amp; Progress</h3><p>Discuss and track work without exposing private submissions.</p></div><span class="a748-badge">{{ ucfirst($a748Role) }}</span></div>
            <div class="a748-body">
                <div class="a748-row"><span class="a748-stat"><strong>{{ $a748Entries->count() }}</strong> recent updates</span><span class="a748-mini">Latest 50 shown</span></div>
                @if(in_array($a748Role, ['creator', 'assignee', 'contributor'], true))
                    <form class="a748-form" method="POST" action="{{ route($a748Prefix.'.collaboration.entry', $a748Params) }}">
                        @csrf
                        <div><label for="a748-kind-{{ $entity->getKey() }}">Update type</label><select id="a748-kind-{{ $entity->getKey() }}" name="entry_type" required><option value="comment">Comment</option><option value="progress">Progress update</option></select></div>
                        <div><label for="a748-body-{{ $entity->getKey() }}">Message</label><textarea id="a748-body-{{ $entity->getKey() }}" name="body" maxlength="3000" placeholder="Write a comment or report progress..." required>{{ old('body') }}</textarea></div>
                        <div><button class="btn primary" type="submit">Post Update</button></div>
                    </form>
                @else
                    <p class="a748-caption">Viewer access is read-only. You can read progress but cannot post updates.</p>
                @endif
                <ol class="a748-timeline">
                    @forelse($a748Entries as $entry)
                        <li class="a748-entry"><div class="a748-entry-head"><strong class="a748-strong">{{ $entry->author?->name ?? 'Former user' }}</strong><span class="a748-badge {{ $entry->entry_type === 'comment' ? 'muted' : '' }}">{{ $entry->entry_type === 'progress' ? 'Progress' : 'Comment' }}</span><small>{{ $entry->created_at?->format('d M Y, H:i') }}</small></div><p>{{ $entry->body }}</p></li>
                    @empty
                        <li class="a748-empty">No collaboration updates yet.</li>
                    @endforelse
                </ol>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div><h3>Access &amp; Participants</h3><p>Only explicitly invited users receive collaboration access.</p></div></div>
            <div class="a748-body">
                <div class="a748-row"><span class="a748-badge muted">Creator</span><span class="a748-badge muted">Viewer</span><span class="a748-badge muted">Contributor</span>@if($entityType === 'todo')<span class="a748-badge muted">Assignee</span>@endif</div>
                <p class="a748-caption">Team membership alone never grants access. Personal tasks cannot be shared. Collaboration does not grant access to customer records or another person's submission files.</p>
                @if($a748Creator)
                    <div style="margin:16px 0"><a href="{{ route($a748Prefix.'.collaboration.manage', $a748Params) }}" class="btn primary">Manage Collaborators</a></div>
                    <ul class="a748-member-list">
                        @forelse($a748Members as $member)
                            <li class="a748-member"><div><strong class="a748-strong">{{ $member->user?->name ?? 'User' }}</strong><small>{{ $member->user?->email }}</small></div><span class="a748-badge">{{ ucfirst($member->access_role) }}</span></li>
                        @empty
                            <li class="a748-empty">No additional collaborators. Use Manage Collaborators to invite someone.</li>
                        @endforelse
                    </ul>
                @else
                    <p class="a748-caption" style="margin-top:15px">Your effective access: <strong>{{ ucfirst($a748Role) }}</strong>. Contact the creator if you need a different permission.</p>
                @endif
            </div>
        </div>
    </div>
</section>
