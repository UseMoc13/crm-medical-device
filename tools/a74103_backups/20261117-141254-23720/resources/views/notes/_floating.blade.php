@auth
<aside id="crmFloatingNotes" class="notes-floating" role="dialog" aria-label="Private floating notes"
      data-actor="{{ auth('web')->id() }}" hidden>
    <div class="notes-floating-head" id="crmNotesDragHandle">
        <div class="notes-floating-brand"><span aria-hidden="true">▤</span><div><strong>My Sticky Notes</strong><small>Private workspace · drag to move</small></div></div>
        <div class="notes-floating-head-actions">
            <button type="button" data-notes-action="minimize" title="Minimize note" aria-label="Minimize notes">−</button>
            <button type="button" data-notes-action="close" title="Close notes" aria-label="Close notes">×</button>
        </div>
    </div>
    <div class="notes-floating-content">
        <div class="notes-floating-toolbar">
            <label class="notes-floating-search-wrap"><span class="sr-only">Filter notes</span><input id="crmNotesFilter" type="search" placeholder="Search notes..." autocomplete="off"></label>
            <button type="button" class="btn primary notes-floating-new" id="crmNotesNew">+ New</button>
        </div>
        <div class="notes-floating-workspace">
            <div class="notes-floating-list" id="crmNotesList" role="listbox" aria-label="Personal notes"></div>
            <section class="notes-floating-editor" aria-label="Note editor" id="crmNotesEditor" hidden>
                <label class="notes-floating-label" for="crmNotesTitle">Title</label>
                <input type="text" id="crmNotesTitle" maxlength="150" placeholder="Untitled Note" autocomplete="off">
                <div class="notes-floating-editor-row"><label class="notes-floating-label" for="crmNotesBody">Note</label><span class="notes-floating-hint">Type @ to mention CRM records</span></div>
                <div class="notes-floating-textarea-wrap">
                    <textarea id="crmNotesBody" rows="7" maxlength="10000" placeholder="Write your note. Type @ to find an Activity, TO-DO, or Calendar event..."></textarea>
                    <div class="notes-mention-picker" id="crmNotesMentionPicker" hidden>
                        <div class="notes-mention-header"><strong>Mention a record</strong><select id="crmNotesMentionType" aria-label="Mention category"><option value="all">All</option><option value="activity">Activity</option><option value="todo">TO-DO</option><option value="calendar">Calendar</option></select></div>
                        <div id="crmNotesMentionResults" class="notes-mention-results"></div>
                    </div>
                </div>
                <div class="notes-floating-preview-caption">Clickable mention preview</div>
                <div class="notes-floating-preview" id="crmNotesPreview" aria-label="Linked note preview"></div>
                <div class="notes-floating-footer">
                    <span id="crmNotesSaveStatus" class="notes-floating-save-status" role="status" aria-live="polite">Ready</span>
                    <div class="notes-floating-editor-actions"><button class="btn notes-floating-delete" type="button" id="crmNotesDelete">Delete</button><a class="btn" id="crmNotesDetails" href="{{ route('notes.index') }}">Details</a><button class="btn primary" type="button" id="crmNotesSave">Save</button></div>
                </div>
                <div class="notes-floating-confirm" id="crmNotesConfirm" hidden role="group" aria-label="Confirm delete note"><strong>Delete this note?</strong><p>This action cannot be undone.</p><div><button type="button" class="btn" id="crmNotesCancelDelete">Cancel</button><button type="button" class="btn notes-floating-delete" id="crmNotesConfirmDelete">Delete Note</button></div></div>
            </section>
            <div class="notes-floating-empty" id="crmNotesNoSelection">Select a note or create a new one.</div>
        </div>
        <div class="notes-floating-bottom"><span><span class="notes-floating-private-icon">🔒</span> Visible only to you</span><a href="{{ route('notes.index') }}">View all notes</a></div>
    </div>
</aside>
<script>
(() => {
    'use strict';
    const panel = document.getElementById('crmFloatingNotes');
    const openButton = document.getElementById('crmNotesOpen');
    if (!panel || !openButton) return;
    const actor = panel.dataset.actor;
    const storageKey = `crm-sticky-v1:${actor}`;
    const api = {
        index: @json(route('notes.floating.index')),
        create: @json(route('notes.floating.create')),
        mentions: @json(route('notes.mention-options')),
        base: @json(url('/notes/floating/data')),
        activity: @json(url('/activities')),
        todo: @json(url('/todos')),
        calendar: @json(url('/calendar/events')),
    };
    const token = @json(csrf_token());
    const ui = {
        handle: document.getElementById('crmNotesDragHandle'),
        list: document.getElementById('crmNotesList'), filter: document.getElementById('crmNotesFilter'),
        new: document.getElementById('crmNotesNew'), editor: document.getElementById('crmNotesEditor'),
        empty: document.getElementById('crmNotesNoSelection'), title: document.getElementById('crmNotesTitle'),
        body: document.getElementById('crmNotesBody'), preview: document.getElementById('crmNotesPreview'),
        status: document.getElementById('crmNotesSaveStatus'), save: document.getElementById('crmNotesSave'),
        details: document.getElementById('crmNotesDetails'), remove: document.getElementById('crmNotesDelete'),
        confirm: document.getElementById('crmNotesConfirm'), cancel: document.getElementById('crmNotesCancelDelete'),
        yes: document.getElementById('crmNotesConfirmDelete'), picker: document.getElementById('crmNotesMentionPicker'),
        mentionType: document.getElementById('crmNotesMentionType'), results: document.getElementById('crmNotesMentionResults'),
    };
    let notes = [], currentId = null, dirty = false, saving = false, saveTimer = null, conflict = false;
    let pickerTimer = null, mentionRange = null, searchSerial = 0, activeRequest = null;
    let minimized = false, opened = false, dragging = null;
    const emptyHtml = '<p>No content yet. Type a note to get started.</p>';
    let settings = {x: null, y: null, width: null, height: null, open: false, minimized: false, selected: null};
    try { settings = {...settings, ...JSON.parse(sessionStorage.getItem(storageKey) || '{}')}; } catch (_) {}
    const persist = () => { try {sessionStorage.setItem(storageKey, JSON.stringify(settings));} catch (_) {} };
    const selected = () => notes.find(n => n.id === currentId) || null;
    const fetchJson = async (url, method='GET', data=null) => {
        const response = await fetch(url, {
            method, credentials: 'same-origin', cache: 'no-store',
            headers: {'Accept':'application/json', 'X-CSRF-TOKEN':token, ...(data ? {'Content-Type':'application/json'} : {})},
            body: data ? JSON.stringify(data) : undefined,
        });
        let result;
        try { result = await response.json(); } catch (_) { throw new Error(`Unexpected server response (HTTP ${response.status}).`); }
        if (!response.ok) {
            const err = new Error(result.message || Object.values(result.errors || {}).flat()[0] || `HTTP ${response.status}`);
            err.status = response.status; throw err;
        }
        return result;
    };
    const status = (value, problem=false) => {ui.status.textContent = value; ui.status.classList.toggle('error', problem);};
    const text = (el, value) => { el.textContent = value == null ? '' : String(value); };
    const tokenRegex = /@\[(Activity|TO-DO|Calendar):([^\]\r\n]{1,120})\]\((activity|todo|calendar):([0-9a-fA-F-]{36})\)/g;
    const renderPreview = (value) => {
        ui.preview.replaceChildren();
        if (!value.trim()) {ui.preview.innerHTML = emptyHtml; return;}
        let pos = 0;
        for (const m of value.matchAll(tokenRegex)) {
            if (m.index > pos) ui.preview.append(document.createTextNode(value.slice(pos, m.index)));
            const expected = m[1].toLowerCase() === 'to-do' ? 'todo' : m[1].toLowerCase();
            const target = m[3];
            const label = `@${m[1]}: ${m[2]}`;
            if (expected === target && /^[0-9a-fA-F-]{36}$/.test(m[4])) {
                const a = document.createElement('a'); a.textContent = label;
                a.href = `${api[target]}/${encodeURIComponent(m[4])}`;
                a.className = 'notes-mention-link'; a.title = 'Open linked CRM record';
                ui.preview.append(a);
            } else ui.preview.append(document.createTextNode(m[0]));
            pos = m.index + m[0].length;
        }
        if (pos < value.length) ui.preview.append(document.createTextNode(value.slice(pos)));
    };
    const setPanelState = () => {
        panel.hidden = !opened; panel.classList.toggle('is-minimized', minimized);
        openButton.setAttribute('aria-expanded', String(opened));
        settings.open = opened; settings.minimized = minimized; persist();
        if (opened) restoreGeometry();
    };
    const clamp = (v, min, max) => Math.min(Math.max(v, min), Math.max(min, max));
    const restoreGeometry = () => {
        if (window.matchMedia('(max-width: 700px)').matches) {
            panel.style.left = ''; panel.style.top = ''; panel.style.right = ''; panel.style.width = ''; panel.style.height = '';
            return;
        }
        panel.style.right = 'auto';
        if (settings.width) panel.style.width = `${clamp(settings.width, 340, Math.min(840, window.innerWidth-20))}px`;
        if (settings.height) panel.style.height = `${clamp(settings.height, 230, window.innerHeight-20)}px`;
        const width = panel.offsetWidth || 590;
        const height = minimized ? 52 : (panel.offsetHeight || 540);
        panel.style.left = `${clamp(settings.x ?? (window.innerWidth - width - 32), 0, window.innerWidth - Math.min(width, 140))}px`;
        panel.style.top = `${clamp(settings.y ?? 90, 0, window.innerHeight - Math.min(height, 50))}px`;
    };
    const items = () => {
        ui.list.replaceChildren();
        const q = ui.filter.value.trim().toLowerCase();
        const shown = notes.filter(n => !q || n.title.toLowerCase().includes(q) || n.body.toLowerCase().includes(q));
        if (!shown.length) {const e = document.createElement('p'); e.className = 'notes-floating-list-empty';e.textContent = 'No matching notes.'; ui.list.append(e);}
        for (const note of shown) {
            const b = document.createElement('button'); b.type='button';
            b.className = 'notes-floating-list-item' + (note.id === currentId ? ' active' : '');
            b.setAttribute('role','option'); b.setAttribute('aria-selected', String(note.id === currentId));
            const title = document.createElement('strong'); title.textContent = (note.is_pinned ? '★ ' : '') + note.title;
            const excerpt = document.createElement('small'); excerpt.textContent = note.body.slice(0,72) || 'Empty note';
            b.append(title,excerpt); b.addEventListener('click',()=>{ void choose(note.id); }); ui.list.append(b);
        }
    };
    const pickerHide = () => {ui.picker.hidden = true;mentionRange = null;searchSerial++;};
    const choose = async (id, force=false) => {
        if (!force && currentId === id) return;
        if (dirty) {await saveNow(); if (dirty) {status('Save this note or resolve the error before switching.',true);return;}}
        currentId = id; settings.selected = id;persist(); conflict = false;pickerHide();ui.confirm.hidden = true;
        const note = selected();
        ui.editor.hidden = !note; ui.empty.hidden = !!note;
        if (!note) {items(); return;}
        ui.title.value = note.title;ui.body.value = note.body;ui.preview.classList.remove('is-pending');
        ui.details.href = note.detail_url; renderPreview(note.body);
        status('Saved · ' + note.updated);items();
        // The server renders the authoritative, permission-filtered links.
        try {
            const detail = await fetchJson(`${api.base}/${id}`);
            if (currentId === id && !dirty) ui.preview.innerHTML = detail.note.preview_html;
        } catch (_) { /* The editable text remains available; errors appear on save. */ }
    };
    const saveNow = async () => {
        clearTimeout(saveTimer);
        if (!dirty || saving || !selected() || conflict) return;
        const note = selected();
        const values = {title:ui.title.value, body:ui.body.value,revision:note.revision};
        saving = true; status('Saving...');
        try {
            const result = await fetchJson(`${api.base}/${note.id}`, 'PATCH',values);
            note.title = result.note.title; note.body = result.note.body;note.revision = result.note.revision;
            note.updated = result.note.updated;
            dirty = (ui.title.value !== values.title || ui.body.value !== values.body);
            items();status(dirty ? 'Unsaved changes' : 'Saved · '+note.updated);
            if (!dirty) { ui.preview.classList.remove('is-pending'); ui.preview.innerHTML = result.note.preview_html; }
            if (dirty) saveTimer = setTimeout(()=>{void saveNow();},500);
        } catch (error) {
            if (error.status === 409) conflict = true;
            status(error.status === 409 ? 'Conflict: another tab changed this note. Copy your draft, then reload.' : `Save failed: ${error.message}`,true);
        } finally {saving = false;}
    };
    const changed = () => {dirty = true; status('Unsaved changes');renderPreview(ui.body.value);ui.preview.classList.add('is-pending');items();clearTimeout(saveTimer);saveTimer=setTimeout(()=>{void saveNow();},850);};
    const refresh = async (preferred=null) => {
        const result = await fetchJson(api.index);
        notes = result.notes || [];
        const next = preferred && notes.some(n=>n.id===preferred) ? preferred : notes.some(n=>n.id===settings.selected) ? settings.selected : (notes[0]?.id || null);
        currentId = null;dirty = false; conflict=false;items();await choose(next,true);
    };
    const reveal = async () => {
        opened = true;minimized = false;setPanelState();
        try {if (!notes.length) await refresh();} catch (error) {status(`Unable to load notes: ${error.message}`,true);}
    };
    openButton.addEventListener('click',()=>{if (!opened || minimized) void reveal();else {opened=false;setPanelState();}});
    panel.querySelector('[data-notes-action="close"]').addEventListener('click',()=>{void saveNow();opened=false;setPanelState();});
    panel.querySelector('[data-notes-action="minimize"]').addEventListener('click',()=>{void saveNow();minimized=!minimized;setPanelState();});
    ui.new.addEventListener('click', async()=>{
        if (dirty) {await saveNow();if(dirty)return;}
        try {const result = await fetchJson(api.create,'POST');notes.unshift(result.note);await choose(result.note.id,true);ui.title.focus();ui.title.select();}
        catch(error){status(`Unable to create note: ${error.message}`,true);}
    });
    ui.filter.addEventListener('input',items);
    ui.title.addEventListener('input',changed);
    ui.body.addEventListener('input',()=>{changed();schedulePicker();});
    ui.body.addEventListener('click',schedulePicker);
    ui.body.addEventListener('keyup',e=>{if (e.key === 'Escape') pickerHide(); else schedulePicker();});
    ui.save.addEventListener('click',()=>{void saveNow();});
    ui.remove.addEventListener('click',()=>{ui.confirm.hidden=false;ui.cancel.focus();});
    ui.cancel.addEventListener('click',()=>{ui.confirm.hidden=true;ui.remove.focus();});
    ui.yes.addEventListener('click',async()=>{
        if (!selected()) return;
        const id = currentId;clearTimeout(saveTimer);dirty=false;
        try {await fetchJson(`${api.base}/${id}`,'DELETE');notes=notes.filter(n=>n.id!==id);currentId=null;settings.selected=null;await choose(notes[0]?.id||null,true);status('Note deleted.');}
        catch(error){status(`Delete failed: ${error.message}`,true);}
        ui.confirm.hidden=true;
    });
    function activeMention() {
        const caret = ui.body.selectionStart;
        const before = ui.body.value.slice(0, caret);
        const match = before.match(/(?:^|\s)@([^@\s]{0,60})$/);
        if (!match) return null;
        return {start: caret - match[1].length - 1, end:caret,query:match[1]};
    }
    function schedulePicker() {
        clearTimeout(pickerTimer);
        const range = activeMention();
        if (!range || !selected()) {pickerHide();return;}
        mentionRange = range;ui.picker.hidden = false;pickerTimer=setTimeout(()=>{void searchMention(range.query);},220);
    }
    async function searchMention(q) {
        const serial=++searchSerial;
        text(ui.results,'Searching...');
        try {
            const url = `${api.mentions}?type=${encodeURIComponent(ui.mentionType.value)}&q=${encodeURIComponent(q)}`;
            const response=await fetchJson(url);
            if (serial!==searchSerial || ui.picker.hidden)return;
            ui.results.replaceChildren();
            if(!response.items.length){text(ui.results,'No accessible matching records.');return;}
            for (const item of response.items) {
                const b=document.createElement('button'); b.type='button';
                const typeName = item.type==='todo'?'TO-DO':item.type==='calendar'?'Calendar':'Activity';
                const title=document.createElement('strong');title.textContent=item.title;
                const type=document.createElement('small');type.textContent=typeName;
                b.append(type,title);
                // mousedown prevents blur; click inserts the token at the previous caret.
                b.addEventListener('mousedown',e=>e.preventDefault());
                b.addEventListener('click',()=>{
                    if (!mentionRange)return;
                    const safeLabel=String(item.title).replace(/[\]\r\n]/g,' ').slice(0,100).trim() || 'Untitled';
                    const token=`@[${typeName}:${safeLabel}](${item.type}:${item.id})`;
                    const {start,end}=mentionRange;
                    ui.body.value=ui.body.value.slice(0,start)+token+ui.body.value.slice(end);
                    ui.body.focus();ui.body.setSelectionRange(start+token.length,start+token.length);
                    pickerHide();changed();
                });
                ui.results.append(b);
            }
        }catch(error){if(serial===searchSerial)text(ui.results,`Mention search failed: ${error.message}`);}
    }
    ui.mentionType.addEventListener('change',()=>{if (mentionRange)void searchMention(mentionRange.query);});
    document.addEventListener('keydown',e=>{
        if (e.key === 'Escape') {if(!ui.picker.hidden){pickerHide();return;}if(opened){minimized=true;setPanelState();openButton.focus();}}
    });
    // Drag only the header, never on mobile, and do not grab header buttons.
    ui.handle.addEventListener('pointerdown',e=>{
        if(e.button!==0 || e.target.closest('button') || window.matchMedia('(max-width: 700px)').matches)return;
        const rect=panel.getBoundingClientRect();
        dragging={startX:e.clientX,startY:e.clientY,x:rect.left,y:rect.top};
        ui.handle.setPointerCapture(e.pointerId);
    });
    ui.handle.addEventListener('pointermove',e=>{
        if(!dragging)return;
        settings.x=clamp(dragging.x+e.clientX-dragging.startX,0,window.innerWidth-140);
        settings.y=clamp(dragging.y+e.clientY-dragging.startY,0,window.innerHeight-50);
        panel.style.left=`${settings.x}px`;panel.style.top=`${settings.y}px`;
    });
    ui.handle.addEventListener('pointerup',()=>{if(dragging){dragging=null;persist();}});
    ui.handle.addEventListener('pointercancel',()=>{dragging=null;});
    panel.addEventListener('pointerup',()=>{
        if (window.matchMedia('(max-width: 700px)').matches || minimized) return;
        const rect=panel.getBoundingClientRect();settings.width=Math.round(rect.width);settings.height=Math.round(rect.height);persist();
    });
    window.addEventListener('resize',()=>{if(opened)restoreGeometry();});
    window.addEventListener('pagehide',()=>{
        if(!dirty || !selected() || conflict)return;
        // Before page navigation: best-effort send. Do not cache private note bodies in browser storage.
        const data=new FormData();data.append('_token',token);data.append('_method','PATCH');
        data.append('title',ui.title.value);data.append('body',ui.body.value);data.append('revision',String(selected().revision));
        navigator.sendBeacon?.(`${api.base}/${selected().id}`,data);
    });
    if (settings.open) {opened=true;minimized=!!settings.minimized;setPanelState();if(!minimized)void refresh().catch(e=>status(`Unable to restore: ${e.message}`,true));}
})();
</script>
@endauth
