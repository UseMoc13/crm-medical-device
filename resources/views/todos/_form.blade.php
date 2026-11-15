@php
    $record = $todo ?? null;
    $dueDay = old('due_day', $record?->due_at?->format('Y-m-d') ?? '');
    $dueTime = old('due_time', $record?->due_at?->format('H:i') ?? '');
@endphp
<div class="todo-form-layout">
    <div class="todo-form-main">
        <div class="todo-section"><h4>Task Information</h4><p>Organize your personal tasks and CRM-related work.</p></div>
        <div class="todo-form-grid">
            <div class="todo-field full"><label for="todo-title">Task Title <span class="todo-required">*</span></label><input id="todo-title" type="text" name="title" maxlength="150" required placeholder="e.g. Follow up a quotation" value="{{ old('title', $record?->title ?? '') }}">@error('title')<small class="todo-error">{{ $message }}</small>@enderror</div>
            <div class="todo-field"><label for="todo-visibility">Task Category <span class="todo-required">*</span></label><select id="todo-visibility" name="visibility" required><option value="personal" @selected(old('visibility', $record?->visibility ?? 'personal') === 'personal')>Personal</option><option value="work" @selected(old('visibility', $record?->visibility ?? '') === 'work')>Work</option></select><small>Both categories remain private to you in this first release.</small></div>
            <div class="todo-field"><label for="todo-priority">Priority <span class="todo-required">*</span></label><select id="todo-priority" name="priority" required>@foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $val=>$label)<option value="{{ $val }}" @selected(old('priority', $record?->priority ?? 'medium') === $val)>{{ $label }}</option>@endforeach</select></div>
            <div class="todo-field"><label for="todo-status">Status <span class="todo-required">*</span></label><select id="todo-status" name="status" required>@foreach(['todo'=>'To Do','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $val=>$label)<option value="{{ $val }}" @selected(old('status', $record?->status ?? 'todo') === $val)>{{ $label }}</option>@endforeach</select></div>
            <div class="todo-field"><label for="todo-day">Due Date</label><input id="todo-day" name="due_day" type="date" value="{{ $dueDay }}">@error('due_day')<small class="todo-error">{{ $message }}</small>@enderror</div>
            <div class="todo-field"><label for="todo-time">Due Time</label><input id="todo-time" name="due_time" type="time" step="60" value="{{ $dueTime }}">@error('due_time')<small class="todo-error">{{ $message }}</small>@enderror</div>
        </div>
        <div class="todo-section"><h4>Related CRM Activity</h4><p>Link a work task to one of your Activities, if relevant.</p></div>
        <div class="todo-form-grid"><div class="todo-field full"><label for="todo-activity">Activity <span class="todo-optional">Optional</span></label><select id="todo-activity" name="activity_id"><option value="">No related Activity</option>@foreach($activities as $activity)<option value="{{ $activity->getKey() }}" @selected((string) old('activity_id', $record?->activity_id ?? '') === (string) $activity->getKey())>{{ $activity->subject }} — {{ $activity->activity_date?->format('d M Y') ?? '-' }}</option>@endforeach</select><small>Choose Work category to link an Activity. Only your own Activities are listed.</small>@error('activity_id')<small class="todo-error">{{ $message }}</small>@enderror</div></div>
        <div class="todo-section"><h4>Additional Details</h4><p>Include instructions or supporting information for this task.</p></div>
        <div class="todo-form-grid"><div class="todo-field full"><label for="todo-description">Description</label><textarea id="todo-description" name="description" rows="6" maxlength="5000" placeholder="Add task details or progress notes...">{{ old('description', $record?->description ?? '') }}</textarea>@error('description')<small class="todo-error">{{ $message }}</small>@enderror</div></div>
        <div class="todo-form-actions"><a class="btn" href="{{ route('todos.index') }}">Cancel</a><button type="submit" class="btn primary">{{ $record ? 'Save Changes' : 'Create To-Do' }}</button></div>
    </div>
    <aside class="todo-preview-panel" aria-label="Live task preview">
        <div class="todo-preview-heading">LIVE PREVIEW</div>
        <div class="todo-preview-icon">☑</div>
        <h3 id="todo-preview-title">New Task</h3><p>Review the information before saving.</p>
        <div class="todo-preview-line"><span>Category</span><strong id="todo-preview-visibility">Personal</strong></div>
        <div class="todo-preview-line"><span>Priority</span><strong id="todo-preview-priority">Medium</strong></div>
        <div class="todo-preview-line"><span>Status</span><strong id="todo-preview-status">To Do</strong></div>
        <div class="todo-preview-line"><span>Due Date & Time</span><strong id="todo-preview-due">Not scheduled</strong></div>
        <div class="todo-preview-line"><span>Related Activity</span><strong id="todo-preview-activity">Not linked</strong></div>
        <div class="todo-preview-note"><strong>Description</strong><p id="todo-preview-description">No description provided.</p></div>
    </aside>
</div>
<script>
(() => {
 const $ = id => document.getElementById(id);
 const form = $('todo-form'); if (!form) return;
 const label = id => { const el=$(id); return el.options[el.selectedIndex]?.text || '-'; };
 const fill = () => {
  $('todo-preview-title').textContent=$('todo-title').value.trim()||'New Task';
  $('todo-preview-visibility').textContent=label('todo-visibility');
  $('todo-preview-priority').textContent=label('todo-priority');
  $('todo-preview-status').textContent=label('todo-status');
  $('todo-preview-due').textContent=[$('todo-day').value, $('todo-day').value ? $('todo-time').value : ''].filter(Boolean).join(' · ')||'Not scheduled';
  $('todo-preview-activity').textContent=$('todo-visibility').value==='work' ? ($('todo-activity').value ? label('todo-activity') : 'Not linked') : 'Not applicable (personal)';
  $('todo-preview-description').textContent=$('todo-description').value.trim()||'No description provided.';
  $('todo-activity').disabled=$('todo-visibility').value!=='work';
 };
 form.addEventListener('input',fill);form.addEventListener('change',fill);fill();
})();
</script>
