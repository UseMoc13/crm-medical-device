@php
    $isEdit = isset($role) && $role;
    $chosen = old('page_access', $accessSelected ?? []);
    $initialMode = old('access_mode', 'custom');
    $initialTemplate = old('access_template', $defaultTemplate ?? '');
    $groups = [];
    foreach ($accessPages as $slug => $page) {
        $key = $page['group'];
        if (!isset($groups[$key])) $groups[$key] = ['label' => $page['group_label'], 'pages' => []];
        $groups[$key]['pages'][$slug] = $page;
    }
@endphp
<div class="role-editor-grid" id="role-editor">
    <div class="role-editor-main">
        <div class="card role-editor-card">
            <div class="card-head"><div><h3>Role Information</h3><p>Define the role identity and description.</p></div></div>
            <div class="card-body">
                <div class="form-group"><label for="role_name">Role Name <span class="role-required">*</span></label><input id="role_name" type="text" name="role_name" value="{{ old('role_name', $role->role_name ?? '') }}" maxlength="50" required placeholder="e.g. Sales Support"></div>
                <div class="form-group"><label for="description">Description</label><textarea id="description" name="description" rows="3" placeholder="Describe this role's responsibilities">{{ old('description', $role->description ?? '') }}</textarea></div>
            </div>
        </div>
        <div class="card role-editor-card">
            <div class="card-head"><div><h3>Page & Module Permissions</h3><p>Choose a CRM template or configure each page individually.</p></div></div>
            <div class="card-body">
                <div class="role-editor-modes" role="group" aria-label="Permission configuration mode">
                    <label class="role-editor-mode"><input type="radio" name="access_mode" value="template" @checked($initialMode === 'template')><span><strong>CRM Template</strong><small>Predefined role permissions</small></span></label>
                    <label class="role-editor-mode"><input type="radio" name="access_mode" value="custom" @checked($initialMode !== 'template')><span><strong>Custom</strong><small>Choose permissions per page</small></span></label>
                </div>
                <div id="crm-template-area" class="role-template-box">
                    <label for="access_template">CRM Role Template</label>
                    <select name="access_template" id="access_template"><option value="">Select a template…</option>@foreach($accessTemplates as $key => $label)<option value="{{ $key }}" @selected($initialTemplate === $key)>{{ $label }}</option>@endforeach</select>
                    <p class="role-help">The selected template is applied when you save in Template mode. Existing Custom selections are not overwritten unless you choose “Copy template to Custom”.</p>
                    <button class="btn role-copy-template" type="button" id="copyTemplate">Copy template to Custom</button>
                </div>
                <div id="crm-custom-area" class="role-custom-area">
                    <div class="role-matrix-intro"><div><strong>Permission Matrix</strong><p class="role-help">Each page supports View, Create, Edit and Delete. Choose all four using “All CRUD”.</p></div><div class="role-matrix-toolbar"><button class="btn" type="button" id="expandModules">Expand All</button><button class="btn" type="button" id="collapseModules">Collapse All</button></div></div>
                    @foreach($groups as $groupKey => $group)
                    <div class="role-module" data-module="{{ $groupKey }}">
                        <div class="role-module-head">
                            <button class="role-module-toggle" type="button" aria-expanded="true" aria-controls="role-module-{{ $groupKey }}"><span class="role-chevron">⌄</span><span>{{ $group['label'] }}</span><small class="role-module-count" data-module-count>0 permissions</small></button>
                            <div class="role-module-actions"><button class="role-inline-button" type="button" data-module-all="{{ $groupKey }}">Select all</button><button class="role-inline-button" type="button" data-module-clear="{{ $groupKey }}">Clear</button></div>
                        </div>
                        <div class="role-module-body" id="role-module-{{ $groupKey }}">
                            <div class="role-matrix-scroll"><table class="role-matrix"><thead><tr><th scope="col">Page</th>@foreach($accessActions as $action)<th scope="col">{{ $action === 'update' ? 'Edit' : ucfirst($action) }}</th>@endforeach<th scope="col">All CRUD</th></tr></thead><tbody>
                                @foreach($group['pages'] as $slug => $page)
                                <tr data-page-row="{{ $slug }}"><th scope="row">{{ $page['label'] }}</th>@foreach($accessActions as $action)<td><input type="checkbox" data-role-page="{{ $slug }}" data-role-action="{{ $action }}" name="page_access[{{ $slug }}][]" value="{{ $action }}" @checked(in_array($action, $chosen[$slug] ?? [], true)) aria-label="{{ $page['label'] }}: {{ $action === 'update' ? 'Edit' : ucfirst($action) }}"></td>@endforeach<td><input type="checkbox" data-select-page="{{ $slug }}" aria-label="Select all CRUD for {{ $page['label'] }}"></td></tr>
                                @endforeach
                            </tbody></table></div>
                        </div>
                    </div>
                    @endforeach
                    <p class="role-help">Workflow actions currently require Edit as a minimum. Data Scope and some special endpoints are scheduled for the next authorization phase.</p>
                </div>
            </div>
        </div>
    </div>
    <aside class="role-editor-aside" aria-label="Role preview">
        <div class="card role-preview-card">
            <div class="card-head"><div><h3>Role Preview</h3><p>Updates as you configure access.</p></div></div>
            <div class="card-body">
                <div class="role-preview-identity"><div class="role-preview-avatar" aria-hidden="true">R</div><div><strong id="previewRoleName">New Role</strong><small id="previewRoleDescription">No description provided</small></div></div>
                <div class="role-preview-status"><span id="previewMode">Custom Permissions</span><span id="previewTemplate"></span></div>
                <div class="role-preview-stats"><div><strong id="previewPages">0</strong><span>Pages allowed</span></div><div><strong id="previewActions">0</strong><span>Permissions</span></div></div>
                <h4>Access by module</h4>
                <div class="role-preview-modules" id="previewModules"><p class="role-preview-empty">No permissions selected yet.</p></div>
                <div class="role-preview-notice">Preview only. Access is enforced by backend permission checks after saving.</div>
            </div>
        </div>
    </aside>
</div>
<div class="role-editor-footer"><a class="btn" href="{{ route('roles.index') }}">Cancel</a><button class="btn primary" type="submit">{{ $isEdit ? 'Save Changes' : 'Create Role' }}</button></div>
@include('roles._style')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const editor = document.getElementById('role-editor');
  if (!editor) return;
  const pages = @json($accessPages);
  const actions = @json($accessActions);
  const templates = @json($templateOptions);
  const roleName = document.getElementById('role_name');
  const roleDescription = document.getElementById('description');
  const radios = [...document.querySelectorAll('input[name="access_mode"]')];
  const templateInput = document.getElementById('access_template');
  const boxes = [...editor.querySelectorAll('input[data-role-page]')];
  const selectPages = [...editor.querySelectorAll('input[data-select-page]')];
  const mode = () => editor.querySelector('input[name="access_mode"]:checked')?.value || 'custom';
  function customSelection() {
    const chosen = {};
    boxes.forEach(el => { if (el.checked) (chosen[el.dataset.rolePage] ||= []).push(el.dataset.roleAction); });
    return chosen;
  }
  function selectedAccess() { return mode() === 'template' ? (templates[templateInput.value] || {}) : customSelection(); }
  function updateCheckboxHeaders() {
    selectPages.forEach(el => {
      const children = boxes.filter(b => b.dataset.rolePage === el.dataset.selectPage);
      const selected = children.filter(b => b.checked).length;
      el.checked = selected === children.length;
      el.indeterminate = selected > 0 && selected < children.length;
    });
    editor.querySelectorAll('[data-module]').forEach(el => {
      const checked = el.querySelectorAll('input[data-role-page]:checked').length;
      const count = el.querySelector('[data-module-count]');
      if (count) count.textContent = checked + ' permissions';
    });
  }
  function updatePreview() {
    const selection = selectedAccess();
    const included = Object.entries(pages).filter(([slug]) => (selection[slug] || []).length);
    const total = included.reduce((n,[slug]) => n + (selection[slug] || []).length,0);
    document.getElementById('previewRoleName').textContent = roleName.value.trim() || 'New Role';
    document.getElementById('previewRoleDescription').textContent = roleDescription.value.trim() || 'No description provided';
    document.getElementById('previewPages').textContent = included.length;
    document.getElementById('previewActions').textContent = total;
    document.getElementById('previewMode').textContent = mode() === 'template' ? 'CRM Template' : 'Custom Permissions';
    document.getElementById('previewTemplate').textContent = mode() === 'template' ? (templateInput.selectedOptions[0]?.textContent === 'Select a template…' ? '' : templateInput.selectedOptions[0]?.textContent || '') : '';
    const groups = {};
    included.forEach(([slug,config]) => { (groups[config.group] ||= {label:config.group_label, pages:[], count:0}).pages.push(config.label); groups[config.group].count += selection[slug].length; });
    const target = document.getElementById('previewModules');
    target.replaceChildren();
    if (!Object.keys(groups).length) { const e = document.createElement('p');e.className='role-preview-empty';e.textContent='No permissions selected yet.'; target.append(e); }
    Object.values(groups).forEach(group => { const block=document.createElement('div');block.className='role-preview-module';const heading=document.createElement('div');heading.className='role-preview-module-head';const title=document.createElement('strong');title.textContent=group.label;const count=document.createElement('span');count.textContent=group.count+' permissions';heading.append(title,count); const listing=document.createElement('p');listing.textContent=group.pages.join(' · ');block.append(heading,listing);target.append(block); });
    updateCheckboxHeaders();
  }
  function setMode() {
    const isTemplate=mode()==='template';
    document.getElementById('crm-template-area').hidden=!isTemplate;
    document.getElementById('crm-custom-area').hidden=isTemplate;
    boxes.forEach(el => el.disabled=isTemplate);
    updatePreview();
  }
  selectPages.forEach(el => el.addEventListener('change', () => { boxes.filter(b => b.dataset.rolePage === el.dataset.selectPage).forEach(b => b.checked=el.checked);updatePreview(); }));
  editor.querySelectorAll('[data-module-all]').forEach(el => el.addEventListener('click', () => { const module=editor.querySelector('[data-module="'+el.dataset.moduleAll+'"]'); module?.querySelectorAll('input[data-role-page]').forEach(b=>b.checked=true);updatePreview(); }));
  editor.querySelectorAll('[data-module-clear]').forEach(el => el.addEventListener('click', () => { const module=editor.querySelector('[data-module="'+el.dataset.moduleClear+'"]'); module?.querySelectorAll('input[data-role-page]').forEach(b=>b.checked=false);updatePreview(); }));
  editor.querySelectorAll('.role-module-toggle').forEach(btn => btn.addEventListener('click', () => { const body=document.getElementById(btn.getAttribute('aria-controls'));const expanded=btn.getAttribute('aria-expanded')==='true';btn.setAttribute('aria-expanded',String(!expanded));body.hidden=expanded; }));
  document.getElementById('expandModules').addEventListener('click',()=>editor.querySelectorAll('.role-module-toggle').forEach(btn=>{btn.setAttribute('aria-expanded','true');document.getElementById(btn.getAttribute('aria-controls')).hidden=false;}));
  document.getElementById('collapseModules').addEventListener('click',()=>editor.querySelectorAll('.role-module-toggle').forEach(btn=>{btn.setAttribute('aria-expanded','false');document.getElementById(btn.getAttribute('aria-controls')).hidden=true;}));
  document.getElementById('copyTemplate').addEventListener('click', () => {
    if (!templateInput.value) { templateInput.focus();return; }
    const selected=templates[templateInput.value]||{};
    boxes.forEach(el=>el.checked=(selected[el.dataset.rolePage]||[]).includes(el.dataset.roleAction));
    const custom=radios.find(el=>el.value==='custom');custom.checked=true;setMode();
  });
  boxes.forEach(el => el.addEventListener('change',updatePreview));
  radios.forEach(el => el.addEventListener('change',setMode));
  templateInput.addEventListener('change',updatePreview);
  roleName.addEventListener('input',updatePreview);
  roleDescription.addEventListener('input',updatePreview);
  setMode();
});
</script>
