@php
    $current = $team ?? null;
    $chosenMembers = array_map('strval', old('member_user_ids', $current?->activeMemberships?->pluck('user_id')->all() ?? []));
    $type = old('team_type', $current?->team_type ?? 'permanent');
    $currentLeader = (string) old('leader_user_id', $current?->leader_user_id ?? '');
@endphp
<div class="crm-team-form-layout">
    <div class="card crm-team-panel crm-team-editor">
        <div class="card-head"><div><h3>{{ $current ? 'Edit Team Configuration' : 'Team Configuration' }}</h3><p>Team identity, organizational scope, leader and members.</p></div></div>
        <div class="card-body">
            <section class="crm-team-section"><div class="crm-team-section-head"><h4>Team Information</h4><p>Set a recognizable code and name for this team.</p></div>
                <div class="crm-team-form-grid">
                    <div class="crm-team-field"><label for="team-code">Team Code <b>*</b></label><input id="team-code" name="team_code" maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9_-]*" value="{{ old('team_code',$current?->team_code ?? '') }}" placeholder="e.g. SALES-JKT" required>@error('team_code')<small class="crm-team-error">{{ $message }}</small>@enderror</div>
                    <div class="crm-team-field"><label for="team-name">Team Name <b>*</b></label><input id="team-name" name="team_name" maxlength="150" value="{{ old('team_name',$current?->team_name ?? '') }}" placeholder="e.g. Sales Jakarta" required>@error('team_name')<small class="crm-team-error">{{ $message }}</small>@enderror</div>
                    <div class="crm-team-field"><label for="team-type">Team Type <b>*</b></label><select id="team-type" name="team_type" required><option value="permanent" @selected($type==='permanent')>Permanent / Operational</option><option value="project" @selected($type==='project')>Project / Cross-functional</option></select><small>Project teams may include users from multiple branches.</small>@error('team_type')<small class="crm-team-error">{{ $message }}</small>@enderror</div>
                    <div class="crm-team-field"><label for="team-status">Team Status <b>*</b></label><select id="team-status" name="status" required><option value="active" @selected(old('status',$current?->status ?? 'active')==='active')>Active</option><option value="archived" @selected(old('status',$current?->status ?? '')==='archived')>Archived</option></select><small>Archived teams cannot accept new members.</small>@error('status')<small class="crm-team-error">{{ $message }}</small>@enderror</div>
                    <div class="crm-team-field crm-team-wide"><label for="team-description">Description</label><textarea id="team-description" name="description" rows="3" maxlength="5000" placeholder="Describe the team's responsibilities...">{{ old('description',$current?->description ?? '') }}</textarea>@error('description')<small class="crm-team-error">{{ $message }}</small>@enderror</div>
                </div>
            </section>
            <section class="crm-team-section"><div class="crm-team-section-head"><h4>Organization & Schedule</h4><p>Choose the home branch and dates when applicable.</p></div>
                <div class="crm-team-form-grid">
                    <div class="crm-team-field"><label for="team-branch">{{ $type === 'project' ? 'Home Branch (Optional)' : 'Branch' }} <b id="team-branch-star">{{ $type === 'permanent' ? '*' : '' }}</b></label><select id="team-branch" name="branch_id"><option value="">{{ $type === 'project' ? 'Cross-branch / No home branch' : 'Select branch' }}</option>@foreach($branches as $branch)<option value="{{ $branch->branch_id }}" @selected((string)old('branch_id',$current?->branch_id ?? '')===(string)$branch->branch_id)>{{ $branch->branch_name }}</option>@endforeach</select><small>Permanent team members must belong to this branch.</small>@error('branch_id')<small class="crm-team-error">{{ $message }}</small>@enderror</div>
                    <div class="crm-team-field"><label for="team-leader">Team Leader</label><select id="team-leader" name="leader_user_id"><option value="">No leader selected</option>@foreach($users as $person)<option value="{{ $person->user_id }}" data-user-branch="{{ $person->branch_id ?? '' }}" data-active="{{ strtolower((string)$person->status)==='active' ? '1' : '0' }}" @selected($currentLeader===(string)$person->user_id)>{{ $person->name }} — {{ $person->branch?->branch_name ?? 'No branch' }}</option>@endforeach</select><small>Choosing a leader also selects that person as a team member.</small>@error('leader_user_id')<small class="crm-team-error">{{ $message }}</small>@enderror</div>
                    <div class="crm-team-field" data-project-dates><label for="team-start">Project Start Date</label><input type="date" name="starts_on" id="team-start" value="{{ old('starts_on',$current?->starts_on?->format('Y-m-d') ?? '') }}">@error('starts_on')<small class="crm-team-error">{{ $message }}</small>@enderror</div>
                    <div class="crm-team-field" data-project-dates><label for="team-end">Project End Date</label><input type="date" name="ends_on" id="team-end" value="{{ old('ends_on',$current?->ends_on?->format('Y-m-d') ?? '') }}">@error('ends_on')<small class="crm-team-error">{{ $message }}</small>@enderror</div>
                </div>
            </section>
            <section class="crm-team-section"><div class="crm-team-section-head"><h4>Team Members</h4><p>Filter by Role and Branch, then check individual users. Filtering does not automatically add members.</p></div>
                <div class="crm-team-member-controls">
                    <div class="crm-team-field"><label for="team-filter-role">Filter Role</label><select id="team-filter-role"><option value="">All Roles</option>@foreach($roles as $role)<option value="{{ $role->role_id }}">{{ $role->role_name }}</option>@endforeach</select></div>
                    <div class="crm-team-field"><label for="team-filter-branch">Filter Branch</label><select id="team-filter-branch"><option value="">All Branches</option>@foreach($branches as $branch)<option value="{{ $branch->branch_id }}">{{ $branch->branch_name }}</option>@endforeach</select></div>
                    <div class="crm-team-field"><label for="team-filter-name">Search User</label><input id="team-filter-name" type="search" placeholder="Search name or email..."></div>
                </div>
                <div class="crm-team-member-toolbar"><span id="team-found-count">{{ $users->count() }} eligible candidates</span><div><button type="button" class="btn" id="team-select-filtered">Select Filtered</button><button type="button" class="btn" id="team-clear-filtered">Clear Filtered</button></div></div>
                <div class="crm-team-member-list" id="team-member-list">
                    @forelse($users as $person)
                        <label class="crm-team-member-row" data-member-row data-user-id="{{ $person->user_id }}" data-user-branch="{{ $person->branch_id ?? '' }}" data-role-ids="{{ $person->roles->pluck('role_id')->join(',') }}" data-user-query="{{ strtolower($person->name.' '.$person->email) }}">
                            <input type="checkbox" name="member_user_ids[]" value="{{ $person->user_id }}" @checked(in_array((string)$person->user_id,$chosenMembers,true))>
                            <span class="crm-team-person-avatar">{{ strtoupper(substr($person->name,0,1)) }}</span>
                            <span class="crm-team-member-identity"><strong>{{ $person->name }}</strong><small>{{ $person->email }} · {{ $person->roles->pluck('role_name')->join(', ') ?: 'No Role' }}</small></span>
                            <span class="crm-team-member-branch">{{ $person->branch?->branch_name ?? 'No branch' }}</span>
                            @if(strtolower((string)$person->status)!=='active')<span class="crm-team-tag gray">Inactive</span>@endif
                        </label>
                    @empty
                        <div class="crm-team-empty"><strong>No available users</strong><p>Create active users first in Administration → Users.</p></div>
                    @endforelse
                </div>
                <p id="team-member-help" class="crm-team-member-help">For Permanent teams, only users in the selected branch may be members. Existing inactive members can be retained, but not chosen as leaders.</p>
                @error('member_user_ids')<small class="crm-team-error">{{ $message }}</small>@enderror
                @error('member_user_ids.*')<small class="crm-team-error">{{ $message }}</small>@enderror
            </section>
            <div class="crm-team-form-actions"><a href="{{ $current ? route('teams.show',$current) : route('teams.index') }}" class="btn">Cancel</a><button class="btn primary" type="submit">{{ $current ? 'Save Changes' : 'Create Team' }}</button></div>
        </div>
    </div>
    <aside class="card crm-team-preview">
        <div class="crm-team-preview-label">LIVE PREVIEW</div><div class="crm-team-preview-symbol">T</div><h3 id="team-preview-name">New Team</h3><p id="team-preview-code">Enter a code to identify the team.</p>
        <div class="crm-team-preview-detail"><span>Type</span><strong id="team-preview-type">Permanent</strong></div>
        <div class="crm-team-preview-detail"><span>Status</span><strong id="team-preview-status">Active</strong></div>
        <div class="crm-team-preview-detail"><span>Branch</span><strong id="team-preview-branch">Not selected</strong></div>
        <div class="crm-team-preview-detail"><span>Leader</span><strong id="team-preview-leader">Not selected</strong></div>
        <div class="crm-team-preview-detail"><span>Selected Members</span><strong id="team-preview-members">0</strong></div>
        <div class="crm-team-preview-notice"><strong>Privacy & access</strong><p>Team membership is organizational metadata and does not give automatic permissions to CRM records.</p></div>
    </aside>
</div>
<script>
(() => {
    const form = document.getElementById('crmTeamForm');
    if (!form) return;
    const $ = id => document.getElementById(id);
    const type = $('team-type'), branch = $('team-branch'), leader = $('team-leader');
    const roleFilter = $('team-filter-role'), branchFilter = $('team-filter-branch'), nameFilter = $('team-filter-name');
    const rows = Array.from(form.querySelectorAll('[data-member-row]'));
    const selected = () => rows.filter(row => row.querySelector('input[type="checkbox"]').checked);
    const label = select => select.options[select.selectedIndex]?.text || 'Not selected';
    const update = () => {
        const permanent = type.value === 'permanent';
        branch.required = permanent;
        $('team-branch-star').textContent = permanent ? '*' : '';
        form.querySelectorAll('[data-project-dates]').forEach(el => { el.hidden = permanent; });
        const term = nameFilter.value.trim().toLowerCase();
        let visible = 0;
        for (const row of rows) {
            const branchId = row.dataset.userBranch || '';
            const roleIds = (row.dataset.roleIds || '').split(',');
            const compatible = !permanent || (branch.value !== '' && branchId === branch.value);
            const match = compatible && (!roleFilter.value || roleIds.includes(roleFilter.value))
                && (!branchFilter.value || branchId === branchFilter.value)
                && (!term || (row.dataset.userQuery || '').includes(term));
            row.hidden = !match;
            if (match) visible++;
        }
        for (const opt of Array.from(leader.options).slice(1)) {
            opt.disabled = opt.dataset.active !== '1' || (permanent && (!branch.value || opt.dataset.userBranch !== branch.value));
        }
        if (leader.selectedOptions[0]?.disabled) leader.value = '';
        const invalid = selected().filter(row => permanent && (!branch.value || row.dataset.userBranch !== branch.value)).length;
        $('team-found-count').textContent = `${visible} visible · ${selected().length} selected`;
        $('team-member-help').textContent = invalid
            ? `${invalid} selected member(s) belong to a different branch. Uncheck them or select a Project team before saving.`
            : permanent ? 'Permanent teams only include users from the selected branch. Filters preserve existing selections.'
            : 'Project teams can include users across branches. Filters preserve existing selections.';
        $('team-member-help').classList.toggle('invalid', invalid > 0);
        $('team-preview-name').textContent = $('team-name').value.trim() || 'New Team';
        $('team-preview-code').textContent = $('team-code').value.trim().toUpperCase() || 'Enter a code to identify the team.';
        $('team-preview-type').textContent = label(type);
        $('team-preview-status').textContent = label($('team-status'));
        $('team-preview-branch').textContent = branch.value ? label(branch) : 'Cross-branch / Not selected';
        $('team-preview-leader').textContent = leader.value ? label(leader) : 'Not selected';
        $('team-preview-members').textContent = String(selected().length);
    };
    $('team-select-filtered').addEventListener('click', () => { rows.filter(row => !row.hidden).forEach(row => { row.querySelector('input[type="checkbox"]').checked = true; }); update(); });
    $('team-clear-filtered').addEventListener('click', () => {
        rows.filter(row => !row.hidden).forEach(row => { row.querySelector('input[type="checkbox"]').checked = false; });
        if (leader.value && !selected().some(row => row.dataset.userId === leader.value)) leader.value = '';
        update();
    });
    leader.addEventListener('change', () => {
        if (leader.value) {
            const row = rows.find(r => r.dataset.userId === leader.value);
            if (row) row.querySelector('input[type="checkbox"]').checked = true;
        }
        update();
    });
    rows.forEach(row => row.querySelector('input[type="checkbox"]').addEventListener('change', () => {
        if (row.dataset.userId === leader.value && !row.querySelector('input[type="checkbox"]').checked) leader.value = '';
        update();
    }));
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
})();
</script>
