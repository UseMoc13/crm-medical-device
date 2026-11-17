{{-- A7.4.9 Review, Approval & Revision Workflow — To-Do only; no file sharing changes. --}}
@if($todo->visibility === 'work' && $assignments->isNotEmpty())
@php
    $reviewLabels = [
        'not_submitted' => 'Not submitted',
        'pending_review' => 'Awaiting review',
        'approved' => 'Approved',
        'revision_requested' => 'Revision requested',
        'legacy_accepted' => 'Legacy completed',
    ];
@endphp
<section class="card todo-review-card" aria-labelledby="todo-review-heading">
    <div class="card-head">
        <div>
            <h3 id="todo-review-heading">Submission Review &amp; Revision</h3>
            <p>{{ $isOwner ? 'Approve submitted work or request specific revisions.' : 'Track your submission review and any revision requests.' }}</p>
        </div>
    </div>
    <div class="card-body">
        <div class="todo-review-list">
            @foreach($assignments as $assignment)
                @php
                    $reviewState = (string) ($assignment->review_status ?: 'not_submitted');
                    $canReview = $isOwner
                        && $reviewState === 'pending_review'
                        && $assignment->status === 'completed'
                        && !in_array($todo->status, ['completed', 'cancelled'], true);
                @endphp
                <article class="todo-review-item">
                    <div class="todo-review-item-head">
                        <div class="todo-review-person">
                            <div class="todo-review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr((string) ($assignment->assignee?->name ?? 'U'), 0, 1)) }}</div>
                            <div>
                                <strong>{{ $assignment->assignee?->name ?? 'User' }}</strong>
                                <small>{{ $assignment->completion_required ? 'Mandatory assignment' : 'Optional assignment' }} · {{ $assignment->files->count() }} submitted file(s)</small>
                            </div>
                        </div>
                        <span class="todo-review-state todo-review-state-{{ $reviewState }}">{{ $reviewLabels[$reviewState] ?? 'Unknown' }}</span>
                    </div>

                    @if($reviewState === 'legacy_accepted')
                        <p class="todo-review-hint">Completed before the approval workflow was introduced. Existing completion remains valid.</p>
                    @elseif($reviewState === 'pending_review')
                        <p class="todo-review-hint">Submission received. Awaiting the task creator's decision.</p>
                    @elseif($reviewState === 'revision_requested')
                        <div class="todo-review-feedback">
                            <strong>Revision requested</strong>
                            <p>{{ $assignment->review_note ?: 'Please update your submission and send it again.' }}</p>
                            <small>Previously uploaded files remain available to the creator and assigned user.</small>
                        </div>
                    @elseif($reviewState === 'approved')
                        <p class="todo-review-hint">Accepted{{ $assignment->reviewed_at ? ' on '.\Illuminate\Support\Carbon::parse($assignment->reviewed_at)->format('d M Y, H:i') : '' }}.</p>
                        @if($assignment->review_note)
                            <p class="todo-review-hint">Reviewer note: {{ $assignment->review_note }}</p>
                        @endif
                    @else
                        <p class="todo-review-hint">The assignee can complete the assignment from the submission section above.</p>
                    @endif

                    @if($canReview)
                        <form class="todo-review-form" action="{{ route('todos.assignments.review', ['todo'=>$todo->getKey(), 'assignment'=>$assignment->getKey()]) }}" method="POST">
                            @csrf
                            <label for="todo-review-note-{{ $assignment->getKey() }}">Review note <span>(required for revision, at least 10 characters)</span></label>
                            <textarea id="todo-review-note-{{ $assignment->getKey() }}" name="note" maxlength="2000" rows="3" placeholder="Describe what needs to be changed, or add an optional approval note."></textarea>
                            <div class="todo-review-actions">
                                <button class="btn primary" type="submit" name="decision" value="approve">✓ Approve Submission</button>
                                <button class="btn" type="submit" name="decision" value="request_revision">Request Revision</button>
                            </div>
                        </form>
                    @endif
                </article>
            @endforeach
        </div>

        @if(isset($reviewHistory) && $reviewHistory->isNotEmpty())
            <div class="todo-review-history">
                <h4>Review History</h4>
                <p>Every approval and revision request remains recorded.</p>
                <ol class="todo-review-history-list">
                    @foreach($reviewHistory as $event)
                        <li>
                            <span class="todo-review-history-mark" aria-hidden="true">{{ $event->decision === 'approved' ? '✓' : '↻' }}</span>
                            <div>
                                <strong>{{ $event->decision === 'approved' ? 'Approved' : 'Revision requested' }} — {{ $event->assignment?->assignee?->name ?? 'Assignee' }}</strong>
                                <small>{{ $event->actor?->name ?? 'Reviewer' }} · {{ $event->created_at?->format('d M Y, H:i') ?? 'Unknown date' }}</small>
                                @if($event->note)<p>{{ $event->note }}</p>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif
    </div>
</section>
@endif
