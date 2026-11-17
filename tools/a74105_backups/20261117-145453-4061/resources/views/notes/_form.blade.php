@php
    $editing = isset($note);
    $prefill = $defaults ?? [];
@endphp
@if($errors->any())
<div class="alert error" role="alert"><strong>Please review your note.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form method="POST" action="{{ $editing ? route('notes.update', ['note'=>$note->getKey()]) : route('notes.store') }}" id="personalNoteForm" class="notes-editor-form">
    @csrf
    @if($editing) @method('PUT') @endif
    <section class="card notes-editor-simple">
        <div class="card-head"><div><h3>Personal Note</h3><p>Write freely and type @ to link Activities, TO-DOs or Calendar events.</p></div></div>
        <div class="card-body notes-fields">
            <div class="notes-field"><label for="personalNoteTitle">Title <span>*</span></label><input id="personalNoteTitle" name="title" type="text" maxlength="150" required value="{{ old('title', $editing ? $note->title : '') }}" placeholder="Note title"><small>@error('title') {{ $message }} @enderror</small></div>
            <div class="notes-field"><label for="personalNoteBody">Note content</label>
                <div class="notes-page-body-wrap">
                    <textarea id="personalNoteBody" class="notes-raw-text" name="body" rows="14" maxlength="10000" placeholder="Write your note here. Type @ to mention CRM records...">{{ old('body', $editing ? $note->body : ($prefill['body'] ?? '')) }}</textarea>
                    <div id="personalNoteRichBody" class="notes-rich-editor notes-page-rich" data-placeholder="Write your note here. Type @ to mention CRM records..." aria-label="Personal note content" hidden></div>
                    <div class="notes-mention-picker" id="personalNoteMentionPicker" hidden>
                        <div class="notes-mention-header"><strong>Link a CRM record</strong><select id="personalNoteMentionType" aria-label="CRM record type"><option value="all">All</option><option value="activity">Activity</option><option value="todo">TO-DO</option><option value="calendar">Calendar</option></select></div>
                        <div class="notes-mention-results" id="personalNoteMentionResults"></div>
                    </div>
                </div>
                <p class="notes-help">Mentions appear as clickable text and link directly to a record. Your note remains private.</p>
                <small>@error('body') {{ $message }} @enderror</small>
            </div>
        </div>
    </section>
    <div class="notes-editor-actions"><a href="{{ $editing ? route('notes.show', ['note'=>$note->getKey()]) : route('notes.index') }}" class="btn">Cancel</a><button class="btn primary" type="submit">{{ $editing ? 'Save Changes' : 'Create Note' }}</button></div>
</form>
@include('notes._rich_runtime')
<script>
(() => {
    const form=document.getElementById('personalNoteForm');
    const textarea=document.getElementById('personalNoteBody');
    const body=document.getElementById('personalNoteRichBody');
    if (!form || !body || !window.CrmNotesRichEditor) return;
    const picker=document.getElementById('personalNoteMentionPicker');
    const results=document.getElementById('personalNoteMentionResults');
    const type=document.getElementById('personalNoteMentionType');
    let mention=null,timer=null,serial=0;
    const rich=window.CrmNotesRichEditor.editor(body,()=>{textarea.value=rich.getValue();schedule();});
    rich.setValue(textarea.value);
    textarea.classList.add('is-enhanced');body.hidden=false;
    const hide=()=>{picker.hidden=true;mention=null;serial++;};
    function schedule(){
        clearTimeout(timer);
        mention=rich.getMention();
        if(!mention){hide();return;}
        picker.hidden=false;
        timer=setTimeout(()=>{void search(mention.query);},200);
    }
    async function search(q){
        const i=++serial;results.textContent='Searching...';
        const url=@json(route('notes.mention-options'));
        try{
            const r=await fetch(`${url}?type=${encodeURIComponent(type.value)}&q=${encodeURIComponent(q)}`,{credentials:'same-origin',headers:{Accept:'application/json'}});
            if(!r.ok)throw new Error('Search unavailable');
            const data=await r.json();if(i!==serial||picker.hidden)return;
            results.replaceChildren();
            if(!data.items?.length){results.textContent='No accessible records found.';return;}
            for(const item of data.items){
                const b=document.createElement('button');b.type='button';
                const label=document.createElement('small');label.textContent=item.type==='todo'?'TO-DO':item.type==='calendar'?'Calendar':'Activity';
                const title=document.createElement('strong');title.textContent=item.title;
                b.append(label,title);
                b.addEventListener('mousedown',e=>e.preventDefault());
                b.addEventListener('click',()=>{const range=mention;hide();rich.insertMention(item,range);textarea.value=rich.getValue();});
                results.append(b);
            }
        }catch(error){if(i===serial)results.textContent=error.message;}
    }
    body.addEventListener('keyup',e=>{if(e.key==='Escape')hide();else schedule();});
    body.addEventListener('click',schedule);
    type.addEventListener('change',()=>{if(mention)void search(mention.query);});
    form.addEventListener('submit',()=>{textarea.value=rich.getValue();});
})();
</script>
