<?php
/** Read-only static check — actual HTTP/DB/browser tests are separate. */
declare(strict_types=1);
$root=dirname(__DIR__);$ok=0;$fail=0;
$checks=[
    'Global floating component'=>'resources/views/notes/_floating.blade.php',
    'Topbar button'=>'resources/views/notes/_topbar.blade.php',
    'One Notes module stylesheet'=>'resources/views/notes/_style.blade.php',
    'Floating JSON controller'=>'app/Http/Controllers/WorkspaceFloatingNotesController.php',
    'Private note controller'=>'app/Http/Controllers/WorkspacePersonalNoteController.php',
    'Mention service'=>'app/Services/WorkspaceNoteMentions.php',
    'Note model'=>'app/Models/WorkspacePersonalNote.php',
    'Mention model'=>'app/Models/WorkspaceNoteMention.php',
    'Notes routes'=>'routes/notes.php',
    'Additive schema'=>'database/migrations/2026_11_17_220000_create_workspace_note_mentions_and_revision.php',
    'Floating HTTP tests'=>'tests/Feature/WorkspaceFloatingNoteHttpTest.php',
    'Existing Notes regression fixture'=>'tests/Feature/WorkspacePersonalNoteHttpTest.php',
];
$check=function(string $desc, bool $valid) use(&$ok,&$fail):void {echo ($valid?'[PASS] ':'[FAIL] ').$desc.PHP_EOL;$valid?$ok++:$fail++;};
foreach($checks as $desc=>$path)$check($desc,is_file($root.'/'.$path));
$read=fn(string $p)=>is_file($root.'/'.$p)?(string)file_get_contents($root.'/'.$p):'';
$layout=$read('resources/views/layouts/app.blade.php');
$controller=$read('app/Http/Controllers/WorkspaceFloatingNotesController.php');
$service=$read('app/Services/WorkspaceNoteMentions.php');
$model=$read('app/Models/WorkspacePersonalNote.php');
$routes=$read('routes/notes.php');
$css=$read('resources/views/notes/_style.blade.php');
$check('Global layout include exactly once', substr_count($layout,"@include('notes._floating')")===1);
$check('Topbar Notes entry retained', substr_count($layout,"@include('notes._topbar')")===1);
$check('Style stays in one Notes stylesheet',str_contains($css,'.notes-floating{') && substr_count($css,'</style>')===1);
$check('Notes model revision is assignable',str_contains($model,"'revision'"));
$check('Owner-scoped floating methods',str_contains($controller,'where(\'owner_user_id\', $this->actor())'));
$check('Revision conflict returns 409',str_contains($controller,'409'));
$check('Mention writes authorize each target',str_contains($service,'if (!$this->target('));
$check('Mention rendering escapes text',str_contains($service,'nl2br(e('));
$check('Mention routes precede resource',strpos($routes,"'/notes/mention-options'") < strpos($routes,"Route::resource('notes'"));
$check('Private Notes regression fixture revision',str_contains($read('tests/Feature/WorkspacePersonalNoteHttpTest.php'),"'revision'"));
echo "\nA7.4.10.1 static: {$ok}/".($ok+$fail)." PASS, {$fail} FAIL.\n";
exit($fail ? 1 : 0);
