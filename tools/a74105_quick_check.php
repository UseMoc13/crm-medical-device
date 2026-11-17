<?php
/** Read-only checks for A7.4.10.5 Notes Create/Edit mention-picker overlay. */
declare(strict_types=1);
$root=dirname(__DIR__);
$formPath=$root.'/resources/views/notes/_form.blade.php';
$stylePath=$root.'/resources/views/notes/_style.blade.php';
$form=is_file($formPath) ? file_get_contents($formPath) : '';
$css=is_file($stylePath) ? file_get_contents($stylePath) : '';
$count=0;$failed=0;
$test=static function (bool $result,string $name) use (&$count,&$failed): void {
    ++$count;if (!$result) ++$failed;
    echo ($result?'[PASS] ':'[FAIL] ').$name."\n";
};
$test(is_file($formPath),'Notes Create/Edit common form exists');
$test(is_file($stylePath),'Notes shared stylesheet exists');
$test(str_contains($form,'document.body.appendChild(picker)'),'Picker is moved outside clipped card');
$test(str_contains($form,"picker.classList.add('notes-page-mention-portal')"),'Portal class is applied');
$test(str_contains($css,'.notes-mention-picker.notes-page-mention-portal'),'Portal is styled separately from floating Notes picker');
$test(str_contains($css,'position:fixed!important') && str_contains($css,'z-index:50020!important'),'Picker is a fixed high-level overlay');
$test(str_contains($form,'const caret=document.createRange()'),'Popup anchors at mention caret');
$test(str_contains($form,'const up=below<'),'Popup can open upward when viewport is short');
$test(str_contains($form,'document.addEventListener(\'scroll\',reposition,true)'),'Picker follows viewport scrolling');
$test(str_contains($form,"window.addEventListener('resize',reposition"),'Picker follows viewport resizing');
$test(str_contains($form,'results.append(b);') && str_contains($form,'placePicker();'),'Search results remain selectable and repositioned');
$test(str_contains($form,'rich.insertMention(item,range)'),'Existing UUID mention insertion preserved');
$test(str_contains($form,'textarea.value=rich.getValue()'),'Original note body serialization preserved');
$test(str_contains($css,'.notes-floating-body-resize') && str_contains($css,'max-width:none!important'),'A7.4.10.4 UI refinements retained');
$test(substr_count($css,'<style>')===1 && substr_count($css,'</style>')===1,'One Notes module stylesheet retained');
$test(!str_contains($form,'innerHTML') && !str_contains($form,'eval('),'Mention results generated via safe DOM APIs');
echo "\nA7.4.10.5 static: ".($count-$failed)."/{$count} PASS, {$failed} FAIL.\n";
echo "Static checks do not replace browser and HTTP tests.\n";
exit($failed?1:0);
