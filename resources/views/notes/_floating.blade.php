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
            <div class="notes-floating-splitter" id="crmNotesSplit" role="separator" aria-orientation="vertical" aria-label="Resize note list and editor" aria-valuemin="20" aria-valuemax="70" aria-valuenow="35" tabindex="0" title="Drag to resize the note list"></div>
            <section class="notes-floating-editor" aria-label="Note editor" id="crmNotesEditor" hidden>
                <label class="notes-floating-label" for="crmNotesTitle">Title</label>
                <input type="text" id="crmNotesTitle" maxlength="150" placeholder="Untitled Note" autocomplete="off">
                <div class="notes-floating-editor-row"><label class="notes-floating-label" for="crmNotesBody">Note</label><span class="notes-floating-hint">Type @ to mention CRM records</span></div>
                <div class="notes-floating-textarea-wrap">
                    <div id="crmNotesBody" class="notes-rich-editor notes-floating-rich" data-placeholder="Write your note. Type @ to link an Activity, TO-DO, or Calendar event..." aria-label="Note content, type at sign to link CRM records"></div>
                    <div class="notes-mention-picker" id="crmNotesMentionPicker" hidden>
                        <div class="notes-mention-header"><strong>Mention a record</strong><select id="crmNotesMentionType" aria-label="Mention category"><option value="all">All</option><option value="activity">Activity</option><option value="todo">TO-DO</option><option value="calendar">Calendar</option></select></div>
                        <div id="crmNotesMentionResults" class="notes-mention-results"></div>
                    </div>
                </div>
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
@include('notes._rich_runtime')
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
        body: document.getElementById('crmNotesBody'), split: document.getElementById('crmNotesSplit'),
        status: document.getElementById('crmNotesSaveStatus'), save: document.getElementById('crmNotesSave'),
        details: document.getElementById('crmNotesDetails'), remove: document.getElementById('crmNotesDelete'),
        confirm: document.getElementById('crmNotesConfirm'), cancel: document.getElementById('crmNotesCancelDelete'),
        yes: document.getElementById('crmNotesConfirmDelete'), picker: document.getElementById('crmNotesMentionPicker'),
        mentionType: document.getElementById('crmNotesMentionType'), results: document.getElementById('crmNotesMentionResults'),
    };
    const rich = window.CrmNotesRichEditor.editor(ui.body, () => {changed();schedulePicker();});
    let notes = [], currentId = null, dirty = false, saving = false, saveTimer = null, conflict = false;
    let pickerTimer = null, mentionRange = null, searchSerial = 0, activeRequest = null;
    let minimized = false, opened = false, dragging = null, splitting = null;
    let settings = {x: null, y: null, width: null, height: null, split:35, open: false, minimized: false, selected: null};
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
    const setPanelState = () => {
        panel.hidden = !opened; panel.classList.toggle('is-minimized', minimized);
        ui.list.parentElement.style.setProperty('--notes-list-width', `${clamp(Number(settings.split)||35,20,70)}%`);
        ui.split.setAttribute('aria-valuenow',String(Math.round(Number(settings.split)||35)));
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
            const excerpt = document.createElement('small'); excerpt.textContent = (window.CrmNotesRichEditor.excerpt(note.body).slice(0,72) || 'Empty note');
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
        ui.title.value = note.title;rich.setValue(note.body);
        ui.details.href = note.detail_url;
        status('Saved · ' + note.updated);items();
        // Visible links are inserted as DOM anchors, not literal storage tokens.
    };
    const saveNow = async () => {
        clearTimeout(saveTimer);
        if (!dirty || saving || !selected() || conflict) return;
        if (rich.getValue().length > 10000) { status('Note exceeds 10,000 characters. Shorten it before saving.',true); return; }
        const note = selected();
        const values = {title:ui.title.value, body:rich.getValue(),revision:note.revision};
        saving = true; status('Saving...');
        try {
            const result = await fetchJson(`${api.base}/${note.id}`, 'PATCH',values);
            note.title = result.note.title; note.body = result.note.body;note.revision = result.note.revision;
            note.updated = result.note.updated;
            dirty = (ui.title.value !== values.title || rich.getValue() !== values.body);
            items();status(dirty ? 'Unsaved changes' : 'Saved · '+note.updated);
            if (dirty) saveTimer = setTimeout(()=>{void saveNow();},500);
        } catch (error) {
            if (error.status === 409) conflict = true;
            status(error.status === 409 ? 'Conflict: another tab changed this note. Copy your draft, then reload.' : `Save failed: ${error.message}`,true);
        } finally {saving = false;}
    };
    const changed = () => {dirty = true; status('Unsaved changes');items();clearTimeout(saveTimer);saveTimer=setTimeout(()=>{void saveNow();},850);};
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
    function activeMention() {return rich.getMention();}
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
                    const range=mentionRange;
                    pickerHide();
                    rich.insertMention(item,range);
                });
                ui.results.append(b);
            }
        }catch(error){if(serial===searchSerial)text(ui.results,`Mention search failed: ${error.message}`);}
    }
    ui.mentionType.addEventListener('change',()=>{if (mentionRange)void searchMention(mentionRange.query);});
    // Divider inside the window: resize list vs editor without resizing the outer panel.
    const splitBounds=()=>{
        const width=ui.list.parentElement.getBoundingClientRect().width;
        if (!width) return [20,70];
        const mobile=window.innerWidth<=700;
        const min=Math.max(20,(mobile?82:110)/width*100);
        const max=Math.min(70,(width-(mobile?145:180)-8)/width*100);
        return [Math.min(min,max),Math.max(min,max)];
    };
    const updateSplit=(value)=>{
        const [min,max]=splitBounds();
        settings.split=clamp(Number(value)||35,min,max);
        ui.list.parentElement.style.setProperty('--notes-list-width',settings.split+'%');
        ui.split.setAttribute('aria-valuenow',String(Math.round(settings.split)));
        ui.split.setAttribute('aria-valuemin',String(Math.round(min)));
        ui.split.setAttribute('aria-valuemax',String(Math.round(max)));
    };
    ui.split.addEventListener('pointerdown',e=>{
        if (e.button!==0)return;
        const workspace=ui.list.parentElement.getBoundingClientRect();
        splitting={left:workspace.left,width:workspace.width};
        ui.split.setPointerCapture(e.pointerId);
        e.preventDefault();
    });
    ui.split.addEventListener('pointermove',e=>{
        if(!splitting)return;
        const minList=window.innerWidth<=700?82:110;
        const minEditor=window.innerWidth<=700?145:180;
        const minPct=Math.max(20,minList/splitting.width*100);
        const maxPct=Math.min(70,(splitting.width-minEditor-8)/splitting.width*100);
        updateSplit(clamp((e.clientX-splitting.left)/splitting.width*100,minPct,Math.max(minPct,maxPct)));
    });
    const endSplit=()=>{if(splitting){splitting=null;persist();}};
    if (typeof ResizeObserver !== 'undefined') {
        new ResizeObserver(()=>{if(opened && !minimized) updateSplit(settings.split);}).observe(ui.list.parentElement);
    }
    ui.split.addEventListener('pointerup',endSplit);
    ui.split.addEventListener('pointercancel',endSplit);
    ui.split.addEventListener('keydown',e=>{
        if(!['ArrowLeft','ArrowRight','Home','End'].includes(e.key))return;
        e.preventDefault();
        updateSplit(e.key==='Home'?20:e.key==='End'?70:settings.split+(e.key==='ArrowLeft'?-5:5));
        persist();
    });
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
        data.append('title',ui.title.value);data.append('body',rich.getValue());data.append('revision',String(selected().revision));
        navigator.sendBeacon?.(`${api.base}/${selected().id}`,data);
    });
    if (settings.open) {opened=true;minimized=!!settings.minimized;setPanelState();if(!minimized)void refresh().catch(e=>status(`Unable to restore: ${e.message}`,true));}
})();
</script>
@endauth
