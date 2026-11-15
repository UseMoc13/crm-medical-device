@php
    $record = $todo ?? null;
    $startDay = old('start_day', $record?->starts_at?->format('Y-m-d') ?? '');
    $startTime = old('start_time', $record?->starts_at?->format('H:i') ?? '');
    $dueDay = old('due_day', $record?->due_at?->format('Y-m-d') ?? '');
    $dueTime = old('due_time', $record?->due_at?->format('H:i') ?? '');
    $currentAssignees = $record?->assignments?->pluck('assigned_user_id')->all() ?? [];
    $currentRequired = $record?->assignments?->where('submission_required', true)->pluck('assigned_user_id')->all() ?? [];
    $currentMandatory = $record?->assignments?->where('completion_required', true)->pluck('assigned_user_id')->all() ?? [];
    $selectedAssignees = old('assignee_ids', $currentAssignees);
    $selectedRequired = old('required_assignee_ids', $currentRequired);
    $selectedMandatory = old('mandatory_assignee_ids', $currentMandatory);
@endphp
<div class="todo-form-layout">
    <div class="todo-form-main">
        <div class="todo-section"><h4>Task Information</h4><p>Organize and prioritize your work without exposing personal tasks to others.</p></div>
        <div class="todo-form-grid">
            <div class="todo-field full"><label for="todo-title">Task Title <span class="todo-required">*</span></label>
                <input id="todo-title" name="title" maxlength="150" required placeholder="e.g. Installation follow-up and checklist" value="{{ old('title', $record?->title ?? '') }}">@error('title')<small class="todo-error">{{ $message }}</small>@enderror</div>
            <div class="todo-field"><label for="todo-visibility">Category <span class="todo-required">*</span></label>
                <select id="todo-visibility" name="visibility" required><option value="personal" @selected(old('visibility', $record?->visibility ?? 'personal') === 'personal')>Personal — private</option><option value="work" @selected(old('visibility', $record?->visibility ?? '') === 'work')>Work — assignable</option></select>
                <small>Personal tasks are never shared.</small>@error('visibility')<small class="todo-error">{{ $message }}</small>@enderror</div>
            <div class="todo-field"><label for="todo-priority">Priority <span class="todo-required">*</span></label>
                <select id="todo-priority" name="priority" required>@foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $val=>$label)<option value="{{ $val }}" @selected(old('priority', $record?->priority ?? 'medium') === $val)>{{ $label }}</option>@endforeach</select></div>
            <div class="todo-field"><label for="todo-status">Task Status <span class="todo-required">*</span></label>
                <select id="todo-status" name="status" required>@foreach(['todo'=>'To Do','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $val=>$label)<option value="{{ $val }}" @selected(old('status', $record?->status ?? 'todo') === $val)>{{ $label }}</option>@endforeach</select>
                <small>Work tasks can be completed once all mandatory assignees finish.</small>@error('status')<small class="todo-error">{{ $message }}</small>@enderror</div>
        </div>

        <div class="todo-section"><h4>Schedule & Duration</h4><p>Set a start and end so the task spans the correct dates on Calendar. An end-only deadline is also supported.</p></div>
        <div class="todo-form-grid">
            <div class="todo-field"><label for="todo-start-day">Start Date</label><input type="date" id="todo-start-day" name="start_day" value="{{ $startDay }}">@error('start_day')<small class="todo-error">{{ $message }}</small>@enderror</div>
            <div class="todo-field"><label for="todo-start-time">Start Time</label><input type="time" id="todo-start-time" name="start_time" step="60" value="{{ $startTime }}">@error('start_time')<small class="todo-error">{{ $message }}</small>@enderror</div>
            <div class="todo-field"><label for="todo-day">Due Date / End</label><input type="date" id="todo-day" name="due_day" value="{{ $dueDay }}">@error('due_day')<small class="todo-error">{{ $message }}</small>@enderror</div>
            <div class="todo-field"><label for="todo-time">Due Time / End</label><input type="time" id="todo-time" name="due_time" step="60" value="{{ $dueTime }}">@error('due_time')<small class="todo-error">{{ $message }}</small>@enderror</div>
        </div>

        <div id="todo-work-only">
            <div class="todo-section"><h4>Assignment & Submission Requirements</h4><p>Set each assignee as Mandatory or Optional. Separately choose whether they must upload a file before marking their own assignment complete.</p></div>
            @if($assigneeOptions->isEmpty())
                <p class="todo-form-message">No active users are available in your branch.</p>
            @else
                <div class="todo-assignee-choices">
                    @foreach($assigneeOptions as $person)
                        @php $personId = (string) $person->user_id; @endphp
                        <div class="todo-assignee-choice">
                            <div class="todo-assignee-left">
                                <input type="checkbox" id="todo-assign-{{ $loop->index }}" name="assignee_ids[]" value="{{ $personId }}"
                                    data-assignee-name="{{ $person->name }}" @checked(in_array($personId, $selectedAssignees, true))>
                                <label for="todo-assign-{{ $loop->index }}"><span class="todo-assignee-avatar">{{ mb_strtoupper(mb_substr((string)$person->name,0,1)) }}</span><span><strong>{{ $person->name }}</strong><small>{{ $personId === (string)auth()->id() ? 'You' : 'Active colleague' }}</small></span></label>
                            </div>
                            <div class="todo-assignee-rules">
                                <label class="todo-file-required"><input type="checkbox" name="mandatory_assignee_ids[]" value="{{ $personId }}" @checked(in_array($personId,$selectedMandatory,true))><span>Mandatory</span></label>
                                <label class="todo-file-required"><input type="checkbox" name="required_assignee_ids[]" value="{{ $personId }}" @checked(in_array($personId,$selectedRequired,true))><span>File required</span></label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="todo-form-footnote">No assignee selected means a solo Work task. Assigned users can view the task and submit their own files, but cannot edit or delete the task.</p>
                @error('assignee_ids')<small class="todo-error">{{ $message }}</small>@enderror
                @error('required_assignee_ids')<small class="todo-error">{{ $message }}</small>@enderror
                @error('mandatory_assignee_ids')<small class="todo-error">{{ $message }}</small>@enderror
            @endif

            <div class="todo-section"><h4>Related CRM Activity</h4><p>Link this task to one of your Activity records, if relevant.</p></div>
            <div class="todo-field"><label for="todo-activity">Activity <span class="todo-optional">Optional</span></label>
                <select id="todo-activity" name="activity_id"><option value="">No related Activity</option>@foreach($activities as $activity)<option value="{{ $activity->getKey() }}" @selected((string) old('activity_id', $record?->activity_id ?? '') === (string)$activity->getKey())>{{ $activity->subject }} — {{ $activity->activity_date?->format('d M Y') ?? '-' }}</option>@endforeach</select>
                <small>Only Activities that you own can be linked.</small>@error('activity_id')<small class="todo-error">{{ $message }}</small>@enderror</div>
        </div>

        <div class="todo-section"><h4>Description</h4><p>Provide instructions and expected deliverables for assignees.</p></div>
        <div class="todo-field"><label for="todo-description">Task Notes</label><textarea id="todo-description" name="description" maxlength="5000" rows="6" placeholder="Explain the next action and documents to submit...">{{ old('description',$record?->description ?? '') }}</textarea>@error('description')<small class="todo-error">{{ $message }}</small>@enderror</div>
        <div class="todo-form-actions"><a href="{{ route('todos.index') }}" class="btn">Cancel</a><button type="submit" class="btn primary">{{ $record ? 'Save Changes' : 'Create To-Do' }}</button></div>
    </div>
    <aside class="todo-preview-panel" aria-label="Live task preview">
        <div class="todo-preview-heading">LIVE PREVIEW</div><div class="todo-preview-icon">☑</div>
        <h3 id="todo-preview-title">New Task</h3><p>Review the task and assignments before saving.</p>
        <div class="todo-preview-line"><span>Category</span><strong id="todo-preview-visibility">Personal</strong></div>
        <div class="todo-preview-line"><span>Priority</span><strong id="todo-preview-priority">Medium</strong></div>
        <div class="todo-preview-line"><span>Status</span><strong id="todo-preview-status">To Do</strong></div>
        <div class="todo-preview-line"><span>Schedule</span><strong id="todo-preview-due">Not scheduled</strong></div>
        <div class="todo-preview-line"><span>Assignees</span><strong id="todo-preview-assignees">Private</strong></div>
        <div class="todo-preview-line"><span>Mandatory assignees</span><strong id="todo-preview-mandatory">—</strong></div>
        <div class="todo-preview-line"><span>Required files</span><strong id="todo-preview-required">—</strong></div>
        <div class="todo-preview-line"><span>Related Activity</span><strong id="todo-preview-activity">Not linked</strong></div>
        <div class="todo-preview-note"><strong>Description</strong><p id="todo-preview-description">No description provided.</p></div>
    </aside>
</div>
<script>
(() => {
    const $ = id => document.getElementById(id);
    const form = $('todo-form'); if (!form) return;
    const label = id => { const el=$(id); return el?.options[el.selectedIndex]?.text || '—'; };
    const work = $('todo-work-only');
    const assigneeBoxes = [...form.querySelectorAll('input[name="assignee_ids[]"]')];
    const requiredBoxes = [...form.querySelectorAll('input[name="required_assignee_ids[]"]')];
    const mandatoryBoxes = [...form.querySelectorAll('input[name="mandatory_assignee_ids[]"]')];
    const fill = () => {
        const isWork = $('todo-visibility').value === 'work';
        work.hidden = !isWork;
        $('todo-activity').disabled = !isWork;
        for (const box of assigneeBoxes) box.disabled = !isWork;
        for (const box of mandatoryBoxes) {
            const selected = assigneeBoxes.some(a => a.value === box.value && a.checked);
            box.disabled = !isWork || !selected;
            if (!selected) box.checked = false;
        }
        for (const box of requiredBoxes) {
            const selected = assigneeBoxes.some(a => a.value === box.value && a.checked);
            box.disabled = !isWork || !selected;
            if (!selected) box.checked = false;
        }
        $('todo-preview-title').textContent = $('todo-title').value.trim() || 'New Task';
        $('todo-preview-visibility').textContent = label('todo-visibility');
        $('todo-preview-priority').textContent = label('todo-priority');
        $('todo-preview-status').textContent = label('todo-status');
        const start = $('todo-start-day').value;
        const end = $('todo-day').value;
        $('todo-preview-due').textContent = start && end
            ? `${start} ${$('todo-start-time').value} → ${end} ${$('todo-time').value}`
            : end ? `Deadline ${end} ${$('todo-time').value}` : 'Not scheduled';
        const recipients = assigneeBoxes.filter(box => box.checked && isWork);
        $('todo-preview-assignees').textContent = !isWork ? 'Only me' : recipients.length
            ? recipients.map(b=>b.dataset.assigneeName).join(', ') : 'Solo Work task';
        $('todo-preview-mandatory').textContent = !isWork ? 'Not applicable'
            : `${mandatoryBoxes.filter(b=>b.checked && !b.disabled).length} mandatory / ${recipients.length} assigned`;
        $('todo-preview-required').textContent = !isWork ? 'Not applicable'
            : `${requiredBoxes.filter(b=>b.checked && !b.disabled).length} required / ${recipients.length} assigned`;
        $('todo-preview-activity').textContent = isWork && $('todo-activity').value ? label('todo-activity') : 'Not linked';
        $('todo-preview-description').textContent = $('todo-description').value.trim() || 'No description provided.';
    };
    form.addEventListener('change', event => {
        if (event.target.matches('input[name="assignee_ids[]"]') && event.target.checked) {
            const box = mandatoryBoxes.find(b => b.value === event.target.value);
            if (box) box.checked = true;
        }
        if (event.target.id === 'todo-start-day' && event.target.value && !$('todo-start-time').value) $('todo-start-time').value = '09:00';
        if (event.target.id === 'todo-day' && event.target.value && !$('todo-time').value) $('todo-time').value = '17:00';
        fill();
    });
    form.addEventListener('input', fill); fill();
})();
</script>
