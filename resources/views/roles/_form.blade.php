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

<div id="role-editor" class="role-form-shell">
    <div class="card role-form-card">
        <div class="card-head role-form-card-head">
            <div>
                <h3>{{ $isEdit ? 'Edit Role Configuration' : 'Role Configuration' }}</h3>
                <p>Configure the role identity and access permissions for CRM modules.</p>
            </div>
            <span class="role-required-note"><span>*</span> Required fields</span>
        </div>
        <div class="card-body role-form-card-body">
            <div class="role-editor-grid">
                <div class="role-editor-main">
                    <section class="role-form-section">
                        <div class="role-section-heading"><h4>Role Information</h4><p>Identify this role and its responsibilities.</p></div>
                        <div class="role-identity-fields">
                            <div class="role-field"><label for="role_name">Role Name <span class="role-required">*</span></label><input class="role-control" id="role_name" type="text" name="role_name" value="{{ old('role_name', $role->role_name ?? '') }}" maxlength="50" required placeholder="e.g. Sales Support" autocomplete="off"><small>Use a clear job title that is easy to recognize.</small></div>
                            <div class="role-field"><label for="description">Description</label><textarea class="role-control" id="description" name="description" rows="3" placeholder="Describe the responsibilities of this role">{{ old('description', $role->description ?? '') }}</textarea></div>
                        </div>
                    </section>
                    <section class="role-form-section">
                        <div class="role-section-heading"><h4>Access Configuration</h4><p>Start from a CRM template or select permissions by page.</p></div>
                        <div class="role-editor-modes" role="group" aria-label="Permission configuration mode">
                            <label class="role-editor-mode"><input type="radio" name="access_mode" value="template" @checked($initialMode === 'template')><span class="role-mode-check" aria-hidden="true"></span><span class="role-mode-content"><strong>CRM Template</strong><small>Use predefined division access</small></span></label>
                            <label class="role-editor-mode"><input type="radio" name="access_mode" value="custom" @checked($initialMode !== 'template')><span class="role-mode-check" aria-hidden="true"></span><span class="role-mode-content"><strong>Custom Permissions</strong><small>Configure pages individually</small></span></label>
                        </div>
                        <div id="crm-template-area" class="role-template-box">
                            <div class="role-field"><label for="access_template">Select CRM Template</label><select class="role-control" name="access_template" id="access_template"><option value="">Select a template…</option>@foreach($accessTemplates as $key => $label)<option value="{{ $key }}" @selected($initialTemplate === $key)>{{ $label }}</option>@endforeach</select></div>
                            <p class="role-help">Template permissions are applied when you save. Existing Custom selections remain unchanged until you explicitly copy the template.</p>
                            <button class="btn role-copy-template" type="button" id="copyTemplate">Copy Template to Custom</button>
                        </div>
                        <div id="crm-custom-area" class="role-custom-area">
                            <div class="role-matrix-intro"><div><strong>Page Permissions</strong><p class="role-help">Select View, Create, Edit and Delete separately. Use All CRUD for the entire page.</p></div><div class="role-matrix-toolbar"><button class="btn" type="button" id="expandModules">Expand All</button><button class="btn" type="button" id="collapseModules">Collapse All</button></div></div>
                            @foreach($groups as $groupKey => $group)
                                <div class="role-module" data-module="{{ $groupKey }}">
                                    <div class="role-module-head">
                                        <button class="role-module-toggle" type="button" aria-expanded="true" aria-controls="role-module-{{ $groupKey }}"><span class="role-chevron" aria-hidden="true">⌄</span><span class="role-module-title">{{ $group['label'] }}</span><small class="role-module-count" data-module-count>0 selected</small></button>
                                        <div class="role-module-actions"><button class="role-inline-button" type="button" data-module-all="{{ $groupKey }}">Select All</button><button class="role-inline-button" type="button" data-module-clear="{{ $groupKey }}">Clear</button></div>
                                    </div>
                                    <div class="role-module-body" id="role-module-{{ $groupKey }}">
                                        <div class="role-matrix-scroll">
                                            <table class="role-matrix"><thead><tr><th scope="col">Page / Feature</th>@foreach($accessActions as $action)<th scope="col">{{ $action === 'update' ? 'Edit' : ucfirst($action) }}</th>@endforeach<th scope="col" class="role-all-heading">All CRUD</th></tr></thead>
                                                <tbody>@foreach($group['pages'] as $slug => $page)
                                                    <tr data-page-row="{{ $slug }}"><th scope="row">{{ $page['label'] }}</th>@foreach($accessActions as $action)
                                                        <td><label class="role-checkbox-cell"><input type="checkbox" data-role-page="{{ $slug }}" data-role-action="{{ $action }}" name="page_access[{{ $slug }}][]" value="{{ $action }}" @checked(in_array($action, $chosen[$slug] ?? [], true)) aria-label="{{ $page['label'] }}: {{ $action === 'update' ? 'Edit' : ucfirst($action) }}"><span class="role-check-visual" aria-hidden="true"></span></label></td>
                                                    @endforeach<td class="role-all-cell"><label class="role-checkbox-cell"><input type="checkbox" data-select-page="{{ $slug }}" aria-label="Select all CRUD for {{ $page['label'] }}"><span class="role-check-visual" aria-hidden="true"></span></label></td></tr>
                                                @endforeach</tbody></table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <p class="role-help role-scope-note">Workflow permissions currently use Edit as a minimum. Detailed action permissions and data scope are implemented in a later phase.</p>
                        </div>
                    </section>
                </div>
                <aside class="role-editor-aside" aria-label="Live role preview">
                    <div class="role-preview-panel">
                        <div class="role-preview-label">ROLE PREVIEW</div>

                        <div class="role-preview-identity">
                            <div class="role-preview-avatar" aria-hidden="true" id="previewRoleInitial">R</div>
                            <div class="role-preview-primary">
                                <h3 id="previewRoleName">New Role</h3>
                                <span id="previewRoleSubtitle">CRM access configuration</span>
                            </div>
                        </div>

                        <div class="role-preview-divider"></div>

                        <div class="role-preview-detail">
                            <span>Configuration</span>
                            <strong id="previewMode">Custom Permissions</strong>
                        </div>
                        <div class="role-preview-detail" id="previewTemplateRow" hidden>
                            <span>CRM Template</span>
                            <strong id="previewTemplate">—</strong>
                        </div>
                        <div class="role-preview-detail">
                            <span>Accessible Pages</span>
                            <strong id="previewPages">0</strong>
                        </div>
                        <div class="role-preview-detail">
                            <span>Enabled Permissions</span>
                            <strong id="previewActions">0</strong>
                        </div>

                        <div class="role-preview-divider"></div>

                        <div class="role-preview-specification">
                            <div class="role-preview-section-title">Description</div>
                            <p id="previewRoleDescription">No description provided.</p>
                        </div>

                        <div class="role-preview-divider"></div>

                        <div class="role-preview-specification">
                            <div class="role-preview-section-title">Access by Module</div>
                            <div class="role-preview-modules" id="previewModules">
                                <p class="role-preview-empty">No permissions selected yet.</p>
                            </div>
                        </div>

                        <div class="role-preview-notice">
                            <strong>Role Information</strong>
                            <p>This preview updates automatically as you configure the role. Changes take effect after saving.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
        <div class="role-form-footer"><a class="btn" href="{{ $isEdit ? route('roles.show', $role) : route('roles.index') }}">Cancel</a><button class="btn primary" type="submit">{{ $isEdit ? 'Save Changes' : 'Create Role' }}</button></div>
    </div>
</div>
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
    document.getElementById('previewRoleInitial').textContent = (roleName.value.trim().charAt(0) || 'R').toUpperCase();
    document.getElementById('previewRoleDescription').textContent = roleDescription.value.trim() || 'No description provided.';
    document.getElementById('previewPages').textContent = included.length;
    document.getElementById('previewActions').textContent = total;
    document.getElementById('previewMode').textContent = mode() === 'template' ? 'CRM Template' : 'Custom Permissions';
    const templateLabel = mode() === 'template' && templateInput.value ? (templateInput.selectedOptions[0]?.textContent.trim() || '') : '';
    document.getElementById('previewTemplate').textContent = templateLabel || '—';
    document.getElementById('previewTemplateRow').hidden = mode() !== 'template';
    document.getElementById('previewRoleSubtitle').textContent = mode() === 'template' ? 'CRM template-based access' : 'Custom CRM permissions';
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
