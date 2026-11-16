@php
    $record = $todo ?? null;
    $startDay = old('start_day', $record?->starts_at?->format('Y-m-d') ?? '');
    $startTime = old('start_time', $record?->starts_at?->format('H:i') ?? '');
    $dueDay = old('due_day', $record?->due_at?->format('Y-m-d') ?? '');
    $dueTime = old('due_time', $record?->due_at?->format('H:i') ?? '');
    $currentAssignees = $record?->assignments?->pluck('assigned_user_id')->all() ?? [];
    $currentRequired = $record?->assignments?->where('submission_required', true)->pluck('assigned_user_id')->all() ?? [];
    $currentMandatory = $record?->assignments?->where('completion_required', true)->pluck('assigned_user_id')->all() ?? [];
    // Restore explicitly cleared checkboxes after validation errors.
    $pickerResubmitted = (bool) old('_recipient_picker_submitted', false);
    $selectedAssignees = $pickerResubmitted ? old('assignee_ids', []) : $currentAssignees;
    $selectedRequired = $pickerResubmitted ? old('required_assignee_ids', []) : $currentRequired;
    $selectedMandatory = $pickerResubmitted ? old('mandatory_assignee_ids', []) : $currentMandatory;
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
            <div class="todo-recipient-picker" id="todo-recipient-picker" data-url="{{ route('todos.recipient-options') }}">
                <input type="hidden" name="_recipient_picker_submitted" value="1">
                <div class="todo-recipient-picker-intro">
                    <div><strong>Choose Recipients</strong><p>Filter by Role, Branch or Team. Then tick individual users. Filtering never assigns anyone automatically.</p></div>
                    <span class="todo-recipient-limit">Maximum 20 assignees</span>
                </div>
                <div class="todo-recipient-filter-grid">
                    <div class="todo-field"><label for="todo-filter-role">Role</label>
                        <select id="todo-filter-role"><option value="">All roles</option>
                            @foreach($recipientFilters['roles'] as $role)
                                <option value="{{ $role->role_id }}">{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="todo-field"><label for="todo-filter-branch">Branch</label>
                        <select id="todo-filter-branch"><option value="">All permitted branches</option>
                            @foreach($recipientFilters['branches'] as $branch)
                                <option value="{{ $branch->branch_id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="todo-field"><label for="todo-filter-team">Team</label>
                        <select id="todo-filter-team"><option value="">All visible teams</option>
                            @foreach($recipientFilters['teams'] as $team)
                                <option value="{{ $team->team_id }}">{{ $team->team_name }} ({{ ucfirst($team->team_type) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="todo-field"><label for="todo-filter-user">Search name or email</label>
                        <input id="todo-filter-user" type="search" maxlength="150" autocomplete="off" placeholder="Find a colleague...">
                    </div>
                </div>
                <div class="todo-recipient-toolbar">
                    <span id="todo-recipient-result-count" aria-live="polite">Loading available users…</span>
                    <div>
                        <button type="button" class="btn" id="todo-recipient-select-page">Select displayed</button>
                        <button type="button" class="btn" id="todo-recipient-reset-filters">Reset filters</button>
                    </div>
                </div>
                <div id="todo-recipient-results" class="todo-recipient-results" aria-live="polite" aria-busy="true"></div>
                <div class="todo-recipient-pagination">
                    <button type="button" class="btn" id="todo-recipient-prev">← Previous</button>
                    <span id="todo-recipient-page-label">Page 1</span>
                    <button type="button" class="btn" id="todo-recipient-next">Next →</button>
                </div>
                <div class="todo-selected-head">
                    <div><h5>Selected Assignees <span id="todo-selected-count">0</span>/20</h5>
                        <p>Manage Mandatory / Optional completion and File Required / Optional for each recipient.</p></div>
                    <button type="button" class="btn" id="todo-recipient-clear">Clear selection</button>
                </div>
                <div id="todo-recipient-selected" class="todo-recipient-selected"></div>
                <div id="todo-recipient-hidden-inputs"></div>
                <p class="todo-recipient-error" role="alert" id="todo-recipient-error" hidden></p>
            </div>
            <p class="todo-form-footnote">No assignee selected means a solo Work task. Creator can update task details; each assignee can submit their own files. Team membership alone does not grant access to this task.</p>
            @error('assignee_ids')<small class="todo-error">{{ $message }}</small>@enderror
            @error('assignee_ids.*')<small class="todo-error">{{ $message }}</small>@enderror
            @error('required_assignee_ids')<small class="todo-error">{{ $message }}</small>@enderror
            @error('mandatory_assignee_ids')<small class="todo-error">{{ $message }}</small>@enderror

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
    const picker = $('todo-recipient-picker');
    const work = $('todo-work-only');
    const maxAssignees = 20;
    const selected = new Map();
    const initialPeople = {{ \Illuminate\Support\Js::from($selectedRecipients ?? []) }};
    const initialMandatory = {{ \Illuminate\Support\Js::from(array_values($selectedMandatory ?? [])) }};
    const initialRequired = {{ \Illuminate\Support\Js::from(array_values($selectedRequired ?? [])) }};
    for (const person of initialPeople) {
        selected.set(String(person.id), {
            ...person, mandatory: initialMandatory.includes(String(person.id)),
            required: initialRequired.includes(String(person.id))
        });
    }

    let visiblePeople = [];
    let page = 1;
    let lastPage = 1;
    let total = 0;
    let loading = false;
    let requestId = 0;
    let searchTimer;

    function showError(message) {
        const node = $('todo-recipient-error');
        node.textContent = message || '';
        node.hidden = !message;
    }
    function label(id) {
        const el = $(id);
        return el?.options?.[el.selectedIndex]?.text || '—';
    }
    function el(tag, cls, txt) {
        const node = document.createElement(tag);
        if (cls) node.className = cls;
        if (txt !== undefined) node.textContent = txt;
        return node;
    }
    function inputField(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = name; input.value = value;
        return input;
    }
    function isWork() { return $('todo-visibility').value === 'work'; }
    function updateHiddenInputs() {
        const wrap = $('todo-recipient-hidden-inputs');
        wrap.replaceChildren();
        if (!isWork()) return;
        selected.forEach((person, id) => {
            wrap.appendChild(inputField('assignee_ids[]', id));
            if (person.mandatory) wrap.appendChild(inputField('mandatory_assignee_ids[]', id));
            if (person.required) wrap.appendChild(inputField('required_assignee_ids[]', id));
        });
    }
    function updatePreview() {
        const personal = !isWork();
        work.hidden = personal;
        $('todo-activity').disabled = personal;
        $('todo-preview-title').textContent = $('todo-title').value.trim() || 'New Task';
        $('todo-preview-visibility').textContent = label('todo-visibility');
        $('todo-preview-priority').textContent = label('todo-priority');
        $('todo-preview-status').textContent = label('todo-status');
        const start = $('todo-start-day').value;
        const end = $('todo-day').value;
        $('todo-preview-due').textContent = start && end
            ? `${start} ${$('todo-start-time').value} → ${end} ${$('todo-time').value}`
            : start ? `From ${start} ${$('todo-start-time').value}`
            : end ? `Deadline ${end} ${$('todo-time').value}` : 'Not scheduled';
        const recipients = [...selected.values()];
        $('todo-preview-assignees').textContent = personal ? 'Only me' : recipients.length
            ? recipients.map(p => p.name).join(', ') : 'Solo Work task';
        $('todo-preview-mandatory').textContent = personal ? 'Not applicable'
            : `${recipients.filter(p=>p.mandatory).length} mandatory / ${recipients.length} assigned`;
        $('todo-preview-required').textContent = personal ? 'Not applicable'
            : `${recipients.filter(p=>p.required).length} required / ${recipients.length} assigned`;
        $('todo-preview-activity').textContent = !personal && $('todo-activity').value
            ? label('todo-activity') : 'Not linked';
        $('todo-preview-description').textContent = $('todo-description').value.trim() || 'No description provided.';
        updateHiddenInputs();
    }
    function togglePerson(person, checked) {
        const id = String(person.id);
        if (checked) {
            if (!selected.has(id) && selected.size >= maxAssignees) {
                showError(`Maximum ${maxAssignees} recipients per task. Remove a recipient first.`);
                return false;
            }
            if (!selected.has(id)) selected.set(id, {...person, mandatory: true, required: false});
        } else {
            selected.delete(id);
        }
        showError('');
        renderSelected();
        renderResults();
        return true;
    }
    function renderResults() {
        const root = $('todo-recipient-results');
        root.replaceChildren();
        if (loading) {
            root.appendChild(el('p', 'todo-recipient-message', 'Loading recipients…'));
        } else if (!visiblePeople.length) {
            root.appendChild(el('p', 'todo-recipient-message', 'No eligible users match your filters. Try another role, branch or team.'));
        } else {
            visiblePeople.forEach(person => {
                const row = el('div', 'todo-recipient-row');
                const mark = el('input');
                mark.type = 'checkbox'; mark.checked = selected.has(String(person.id));
                mark.setAttribute('aria-label', `Assign ${person.name}`);
                mark.addEventListener('change', () => {
                    if (!togglePerson(person, mark.checked)) mark.checked = false;
                });
                const avatar = el('span', 'todo-recipient-avatar', (person.name || '?').charAt(0).toUpperCase());
                const detail = el('div', 'todo-recipient-person');
                detail.appendChild(el('strong', '', person.name));
                detail.appendChild(el('small', '', [person.roles.join(' / ') || 'No role', person.branch].join(' · ')));
                row.append(mark, avatar, detail);
                root.appendChild(row);
            });
        }
        $('todo-recipient-result-count').textContent = `${total} matching users · ${selected.size} selected`;
        $('todo-recipient-page-label').textContent = `Page ${page} of ${lastPage}`;
        $('todo-recipient-prev').disabled = loading || page <= 1;
        $('todo-recipient-next').disabled = loading || page >= lastPage;
        $('todo-recipient-select-page').disabled = loading || visiblePeople.length === 0;
        root.setAttribute('aria-busy', loading ? 'true' : 'false');
    }
    function makeFlag(person, key, labelText) {
        const labelNode = el('label', 'todo-recipient-flag');
        const checkbox = el('input'); checkbox.type='checkbox'; checkbox.checked=!!person[key];
        checkbox.addEventListener('change', () => {
            person[key] = checkbox.checked;
            updatePreview();
        });
        labelNode.append(checkbox, document.createTextNode(labelText));
        return labelNode;
    }
    function renderSelected() {
        const root = $('todo-recipient-selected');
        root.replaceChildren();
        if (!selected.size) {
            root.appendChild(el('p','todo-recipient-message','No recipients selected. This will be your own solo Work task.'));
        } else {
            selected.forEach((person, id) => {
                const row = el('div', 'todo-selected-person');
                const info = el('div', 'todo-selected-person-info');
                info.appendChild(el('strong','',person.name));
                info.appendChild(el('small','',[person.roles?.join(' / ') || 'No role',person.branch].join(' · ')));
                if (person.retained_only) info.appendChild(el('small','todo-recipient-stale','Previously assigned · no longer eligible for new assignment'));
                const flags = el('div','todo-selected-person-flags');
                flags.append(makeFlag(person, 'mandatory', 'Mandatory'), makeFlag(person, 'required', 'File required'));
                const remove = el('button','todo-recipient-remove','Remove');
                remove.type = 'button'; remove.setAttribute('aria-label',`Remove ${person.name}`);
                remove.addEventListener('click',()=>togglePerson(person,false));
                row.append(info,flags,remove);
                root.appendChild(row);
            });
        }
        $('todo-selected-count').textContent = String(selected.size);
        updatePreview();
    }
    async function loadRecipients() {
        const ticket = ++requestId;
        loading = true;
        renderResults();
        const params = new URLSearchParams({page:String(page)});
        for (const [name, field] of [['search','todo-filter-user'],['role_id','todo-filter-role'],
            ['branch_id','todo-filter-branch'],['team_id','todo-filter-team']]) {
            const value = $(field).value.trim();
            if (value) params.set(name, value);
        }
        try {
            const response = await fetch(`${picker.dataset.url}?${params.toString()}`, {
                credentials:'same-origin', headers:{Accept:'application/json', 'X-Requested-With':'XMLHttpRequest'}
            });
            if (!response.ok) throw new Error(`Cannot load recipients (HTTP ${response.status}).`);
            const payload = await response.json();
            if (ticket !== requestId) return;
            visiblePeople = payload.data || [];
            total = payload.pagination?.total || 0;
            page = payload.pagination?.page || 1;
            lastPage = payload.pagination?.last_page || 1;
            showError('');
        } catch (err) {
            if (ticket !== requestId) return;
            visiblePeople = []; total = 0; lastPage = 1;
            showError(err.message || 'Cannot load recipients. Refresh the page and try again.');
        } finally {
            if (ticket === requestId) { loading = false; renderResults(); }
        }
    }
    function resetAndLoad() { page=1; loadRecipients(); }
    for (const id of ['todo-filter-role','todo-filter-branch','todo-filter-team']) {
        $(id).addEventListener('change', resetAndLoad);
    }
    $('todo-filter-user').addEventListener('input',()=>{
        clearTimeout(searchTimer);
        searchTimer = setTimeout(resetAndLoad, 250);
    });
    $('todo-recipient-reset-filters').addEventListener('click',()=>{
        ['todo-filter-role','todo-filter-branch','todo-filter-team','todo-filter-user'].forEach(id=>$(id).value='');
        resetAndLoad();
    });
    $('todo-recipient-select-page').addEventListener('click',()=>{
        const newOnes = visiblePeople.filter(p => !selected.has(String(p.id)));
        if (newOnes.length + selected.size > maxAssignees) {
            showError(`Selecting this page would exceed the ${maxAssignees}-assignee limit. Choose recipients individually.`);
            return;
        }
        newOnes.forEach(p=>selected.set(String(p.id),{...p,mandatory:true,required:false}));
        showError('');renderSelected();renderResults();
    });
    $('todo-recipient-clear').addEventListener('click',()=>{
        selected.clear();showError('');renderSelected();renderResults();
    });
    $('todo-recipient-prev').addEventListener('click',()=>{ if(page>1){page--;loadRecipients();} });
    $('todo-recipient-next').addEventListener('click',()=>{ if(page<lastPage){page++;loadRecipients();} });
    form.addEventListener('change', event => {
        if (event.target.id === 'todo-start-day' && event.target.value && !$('todo-start-time').value) $('todo-start-time').value = '09:00';
        if (event.target.id === 'todo-day' && event.target.value && !$('todo-time').value) $('todo-time').value = '17:00';
        updatePreview();
    });
    form.addEventListener('input', updatePreview);
    // If a filter change was queued, do not silently submit an outdated search selection.
    form.addEventListener('submit',updateHiddenInputs);
    renderSelected();
    loadRecipients();
})();
</script>
