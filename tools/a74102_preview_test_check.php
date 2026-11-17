<?php
/** Standalone read-only structural check for A7.4.10.2 test correction. */
declare(strict_types=1);
$root = dirname(__DIR__);
$testFile = $root . '/tests/Feature/WorkspaceFloatingNoteHttpTest.php';
$serviceFile = $root . '/app/Services/WorkspaceNoteMentions.php';
$test = is_file($testFile) ? file_get_contents($testFile) : '';
$service = is_file($serviceFile) ? file_get_contents($serviceFile) : '';
$checks = [
    'Floating note test exists' => $test !== '',
    'Service escapes plain text' => str_contains($service, 'nl2br(e('),
    'Service escapes mention labels' => str_contains($service, "\$label = e("),
    'Test checks raw body preservation' => str_contains($test, "assertJsonPath('note.body', \$body)"),
    'Test scopes HTML assertions to preview_html' => str_contains($test, "\$response->json('note.preview_html')"),
    'Test checks script-tag escaping' => str_contains($test, '&lt;script&gt;'),
    'Test checks dangerous mention-label escaping' => str_contains($test, '&lt;svg/onload=alert(3)&gt;'),
    'Obsolete full-response assertion removed' => !str_contains($test, "assertDontSee('<script>',false)"),
];
$ok = 0;
foreach ($checks as $name => $passed) {
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    if ($passed) $ok++;
}
echo "\nA7.4.10.2 preview assertion check: {$ok}/" . count($checks) . ' PASS' . PHP_EOL;
exit($ok === count($checks) ? 0 : 1);
