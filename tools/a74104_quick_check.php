<?php
/** A7.4.10.4 read-only static checks; run php tools/a74104_quick_check.php */
declare(strict_types=1);

$root = dirname(__DIR__);
$floating = $root . '/resources/views/notes/_floating.blade.php';
$style = $root . '/resources/views/notes/_style.blade.php';
$create = $root . '/resources/views/notes/create.blade.php';
$edit = $root . '/resources/views/notes/edit.blade.php';
$show = $root . '/resources/views/notes/show.blade.php';
$errors = 0; $count = 0;
$check = static function (bool $condition, string $label) use (&$errors, &$count): void {
    ++$count;
    echo ($condition ? '[PASS] ' : '[FAIL] ') . $label . "\n";
    if (!$condition) ++$errors;
};
foreach (['Floating Blade'=>$floating, 'Notes stylesheet'=>$style, 'Create view'=>$create, 'Edit view'=>$edit, 'Show view'=>$show] as $label=>$file) {
    $check(is_file($file), $label);
}
if (!is_file($floating) || !is_file($style)) exit(1);
$f = file_get_contents($floating); $css = file_get_contents($style);
$check(str_contains($css, '/* A7.4.10.4 — full-width Create / Edit / Show'), 'Full-width Notes layout overrides present');
$check(str_contains($css, 'max-width:none!important') && str_contains($css, '.notes-simple-detail'), 'Create/Edit/Show unrestricted width');
$check(substr_count($f, 'id="crmNotesBodyResize"') === 1, 'One vertical writing-area drag handle');
$check(str_contains($f, 'aria-orientation="horizontal"') && str_contains($f, "e.key === 'ArrowUp'"), 'Accessible vertical drag and keyboard arrows');
$check(str_contains($f, "ui.bodyResize.setPointerCapture(e.pointerId)"), 'Pointer drag captures outside-handle movements');
$check(str_contains($f, "settings.bodyHeight = next") && str_contains($f, "persist();"), 'Resize size persisted for user session');
$check(str_contains($css, 'height:var(--notes-body-height,175px)') && str_contains($css,'.notes-floating-body-resize'), 'Editor content height controlled independently');
$check(substr_count($css, '<style>') === 1 && substr_count($css, '</style>') === 1, 'Single module Notes stylesheet retained');
$check(str_contains($f, 'const rich = window.CrmNotesRichEditor.editor'), 'Inline mention editor retained');
$check(str_contains($f, 'const saveNow = async () => {'), 'Autosave retained');
$check(!str_contains($f, 'notes-floating-preview-caption'), 'Old preview markup not reintroduced');
echo "\nA7.4.10.4 static: " . ($count-$errors) . "/{$count} PASS.\n";
echo "Browser resizing and actual HTTP regression remain to be checked separately.\n";
exit($errors ? 1 : 0);
