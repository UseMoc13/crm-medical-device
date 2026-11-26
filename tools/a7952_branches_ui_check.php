<?php
/** A7.9.5.2 read-only UI check; does not connect to DB. */
declare(strict_types=1);
$root = dirname(__DIR__);
$expected = [
    'resources/views/branches/index.blade.php' => [
        'branch-filter', 'branch-table', 'branch-search-icon', 'branch-filter-chip',
        'branch-delete-form', 'branch-modal', "@include('branches._style')",
        "route('branches.create')", "route('branches.show',", '@csrf', "@method('DELETE')", '->links()'
    ],
    'resources/views/branches/create.blade.php' => ["@extends('layouts.app')", "@include('branches._form')", "@include('branches._style')"],
    'resources/views/branches/edit.blade.php' => ["@extends('layouts.app')", "@include('branches._form')", "@include('branches._style')"],
    'resources/views/branches/_form.blade.php' => [
        'branch-editor-layout', 'branch-editor-preview', 'branch-section-heading', 'branch-control',
        "route('branches.store')", "route('branches.update'", '@csrf', "@method('PUT')",
        'name="branch_code"', 'name="branch_name"', 'name="phone"', 'name="address"', 'name="status"'
    ],
    'resources/views/branches/show.blade.php' => [
        'branch-detail-grid', 'branch-details', 'branch-related-card', "route('users.show',", "route('teams.show',",
        "@include('branches._style')", '->links()'
    ],
    'resources/views/branches/_style.blade.php' => [
        '<style>', '</style>', '.branch-filter', '.branch-table-wrap', '.branch-editor-layout',
        '.branch-detail-grid', '.branch-modal', '.branch-actions', '@media'
    ],
];
$count = 0;
foreach ($expected as $path => $needles) {
    $file = $root . '/' . $path;
    if (!is_file($file)) { fwrite(STDERR, "[FAIL] Missing {$path}\n"); exit(1); }
    $text = file_get_contents($file);
    foreach ($needles as $needle) {
        if (!str_contains($text, $needle)) { fwrite(STDERR, "[FAIL] {$path}: {$needle}\n"); exit(1); }
        $count++;
    }
    echo "[PASS] {$path}\n";
}
echo "[RESULT] A7952_BRANCHES_ROLES_UI_SOURCE_READY checks={$count} (HTTP not executed)\n";
