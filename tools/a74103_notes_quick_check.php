<?php
/** A7.4.10.3 local static verification; read-only, no database connection required. */
declare(strict_types=1);
$root=dirname(__DIR__);
$read=static function (string $file) use ($root): string {
    return (string) (@file_get_contents($root.'/'.$file) ?: '');
};
$files = [
    'resources/views/notes/_form.blade.php', 'resources/views/notes/_floating.blade.php',
    'resources/views/notes/_style.blade.php', 'resources/views/notes/_rich_runtime.blade.php',
    'resources/views/notes/create.blade.php', 'resources/views/notes/edit.blade.php',
    'resources/views/notes/index.blade.php', 'resources/views/notes/show.blade.php',
    'resources/views/notes/_linked.blade.php',
    'public/js/crm-notes-rich-editor.js',
    'app/Http/Controllers/WorkspacePersonalNoteController.php',
    'tests/Feature/WorkspacePersonalNoteHttpTest.php',
];
$tests=[];
foreach ($files as $file) $tests['File '.$file]=is_file($root.'/'.$file);
$form=$read('resources/views/notes/_form.blade.php');
$floating=$read('resources/views/notes/_floating.blade.php');
$js=$read('public/js/crm-notes-rich-editor.js');
$style=$read('resources/views/notes/_style.blade.php');
$controller=$read('app/Http/Controllers/WorkspacePersonalNoteController.php');
$tests['Note form has title + body'] = str_contains($form,'name="title"') && str_contains($form,'name="body"');
$tests['No old form reminder / single-link inputs'] = !str_contains($form,'name="remind_day"') && !str_contains($form,'name="activity_id"') && !str_contains($form,'name="calendar_event_id"');
$tests['No separate floating preview'] = !str_contains($floating,'crmNotesPreview') && !str_contains($floating,'Clickable mention preview');
$tests['Rich editor used in both pages'] = str_contains($form,'CrmNotesRichEditor.editor') && str_contains($floating,'CrmNotesRichEditor.editor');
$tests['Storage UUID token round trip'] = str_contains($js,'data-note-token') && str_contains($js,'getValue()') && str_contains($js,'insertMention(');
$tests['Inline clickable link opens CRM route safely'] = str_contains($js,'notes-inline-mention') && str_contains($js,"link.rel = 'noopener noreferrer'") && str_contains($js,'encodeURIComponent(id)');
$tests['Pasted HTML becomes plain text'] = str_contains($js,"getData('text/plain')") && str_contains($js,"e.preventDefault()");
$tests['Resizable split pane supports pointer and keyboard'] = str_contains($floating,'crmNotesSplit') && str_contains($floating,"ui.split.addEventListener('pointermove'") && str_contains($floating,"ui.split.addEventListener('keydown'");
$tests['Split width saved per user'] = str_contains($floating,"settings.split") && str_contains($floating,'sessionStorage.setItem(storageKey');
$tests['Module keeps a single stylesheet'] = substr_count($style,'<style>')===1 && substr_count($style,'</style>')===1 && str_contains($style,'.notes-floating-splitter');
$tests['Missing legacy inputs preserve old metadata'] = str_contains($controller,"\$request->exists('remind_day')") && str_contains($controller,"\$request->exists('is_pinned')");
$tests['Owner-only scoping retained'] = str_contains($controller,"->where('owner_user_id', \$this->actor())");
$tests['Mention write validation retained'] = str_contains($controller,'->synchronize(');
$tests['Related notes now resolve mention references'] = str_contains($read('resources/views/notes/_linked.blade.php'),"->orWhereHas('mentions'");
$tests['UI regression HTTP tests added'] = str_contains($read('tests/Feature/WorkspacePersonalNoteHttpTest.php'),'test_edit_page_uses_title_and_body_only_without_erasing_legacy_metadata');
$passed=0;
foreach($tests as $name=>$pass){echo ($pass?'[PASS] ':'[FAIL] ').$name."\n";$passed+=(int)$pass;}
echo "\nA7.4.10.3 static: {$passed}/".count($tests)." PASS.\n";
exit($passed===count($tests)?0:1);
