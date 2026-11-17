@php
    $editing = isset($note);
    $prefill = $defaults ?? [];
    $date = old('remind_day', $editing ? $note->remind_at?->format('Y-m-d') : ($prefill['remind_day'] ?? ''));
    $time = old('remind_time', $editing ? $note->remind_at?->format('H:i') : '09:00');
@endphp
@if($errors->any())<div class="alert error" role="alert"><strong>Please review the highlighted fields.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $editing ? route('notes.update', ['note'=>$note->getKey()]) : route('notes.store') }}" class="notes-editor-form">
    @csrf @if($editing) @method('PUT') @endif
    <div class="notes-editor-grid">
        <section class="card"><div class="card-head"><div><h3>Note Information</h3><p>A private note that only your account can read.</p></div></div><div class="card-body notes-fields">
            <div class="notes-field"><label for="personalNoteTitle">Title <span>*</span></label><input id="personalNoteTitle" name="title" type="text" maxlength="150" required value="{{ old('title', $editing ? $note->title : '') }}" placeholder="Example: Meeting preparation"><small>@error('title') {{ $message }} @enderror</small></div>
            <div class="notes-field"><label for="personalNoteBody">Note content</label><textarea id="personalNoteBody" name="body" rows="12" maxlength="10000" placeholder="Write your personal notes here...">{{ old('body', $editing ? $note->body : '') }}</textarea><small>@error('body') {{ $message }} @enderror</small></div>
            <label class="notes-check"><input type="checkbox" name="is_pinned" value="1" @checked((bool)old('is_pinned', $editing ? $note->is_pinned : false))><span>Pin note at the top of your personal notes</span></label>
        </div></section>
        <div class="notes-editor-side">
            <section class="card"><div class="card-head"><div><h3>Reminder</h3><p>Show this note on your Personal Calendar.</p></div></div><div class="card-body"><div class="notes-two-col"><div class="notes-field"><label for="notesRemindDay">Date</label><input id="notesRemindDay" type="date" name="remind_day" value="{{ $date }}"></div><div class="notes-field"><label for="notesRemindTime">Time</label><input id="notesRemindTime" type="time" name="remind_time" value="{{ $time }}"></div></div><p class="notes-help">Leave the date empty for an unscheduled note.</p></div></section>
            <section class="card"><div class="card-head"><div><h3>Link CRM Context</h3><p>References do not share this private note with collaborators.</p></div></div><div class="card-body">
                <div class="notes-field"><label for="notesActivity">Activity</label><select id="notesActivity" name="activity_id"><option value="">Not linked</option>@foreach($activities as $a)<option value="{{ $a->getKey() }}" @selected(old('activity_id', $editing ? $note->activity_id : ($prefill['activity_id'] ?? '')) === (string)$a->getKey())>{{ \Illuminate\Support\Str::limit($a->subject, 55) }}</option>@endforeach</select><small>@error('activity_id') {{ $message }} @enderror</small></div>
                <div class="notes-field"><label for="notesCalendar">Personal Calendar Event</label><select id="notesCalendar" name="calendar_event_id"><option value="">Not linked</option>@foreach($calendarEvents as $ce)<option value="{{ $ce->getKey() }}" @selected(old('calendar_event_id', $editing ? $note->calendar_event_id : ($prefill['calendar_event_id'] ?? '')) === (string)$ce->getKey())>{{ \Illuminate\Support\Str::limit($ce->title, 55) }}</option>@endforeach</select><small>@error('calendar_event_id') {{ $message }} @enderror</small></div>
                <p class="notes-help">Only activities you own and events on your personal calendar can be linked.</p>
            </div></section>
        </div>
    </div>
    <div class="notes-editor-actions"><a href="{{ $editing ? route('notes.show', ['note'=>$note->getKey()]) : route('notes.index') }}" class="btn">Cancel</a><button class="btn primary" type="submit">{{ $editing ? 'Save Changes' : 'Create Personal Note' }}</button></div>
</form>
