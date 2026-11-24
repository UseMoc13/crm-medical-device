<?php
/** A7.9.5.1 — source-only check, NO database reads/writes. */
declare(strict_types=1);
$root = dirname(__DIR__);
$checks = [
    'routes/branches.php' => ['Route::resource(', "'branches'", 'EnsureCrmOrganizationAdmin'],
    'app/Http/Controllers/BranchController.php' => [
        'function index(', 'function create(', 'function store(', 'function show(', 'function edit(',
        'function update(', 'function destroy(', 'lockForUpdate()', "'crm_teams'", "'audit_logs'",
        'Rule::unique(', "->with('success'", "->with('error'",
    ],
    'resources/views/branches/index.blade.php' => ['route(\'branches.create\')', 'route(\'branches.show\'', 'branch-delete-form', '@csrf', '@method(\'DELETE\')', '->links()', "@include('branches._style')"],
    'resources/views/branches/_form.blade.php' => ['@csrf', "@method('PUT')", 'route(\'branches.store\')', 'name="branch_code"', 'name="branch_name"', 'name="address"', 'name="phone"', 'name="status"'],
    'resources/views/branches/create.blade.php' => ["@extends('layouts.app')", "@include('branches._form')"],
    'resources/views/branches/edit.blade.php' => ["@extends('layouts.app')", "@include('branches._form')"],
    'resources/views/branches/show.blade.php' => ["@extends('layouts.app')", 'route(\'users.show\'', 'route(\'teams.show\''],
    'resources/views/branches/_style.blade.php' => ['<style>', '</style>', '.branch-filter', '.branch-modal', '@media'],
    'routes/web.php' => ["require __DIR__ . '/branches.php';"],
    'resources/views/components/sidebar.blade.php' => ["route('branches.index')", "request()->routeIs('branches.*')"],
    'app/Services/CrmRouteAuthorizationPolicy.php' => ['roles|users|branches'],
    'resources/views/users/create.blade.php' => ["route('branches.index')"],
    'resources/views/users/edit.blade.php' => ["route('branches.index')"],
];
$all = 0;
foreach ($checks as $path => $needles) {
    $file = $root . '/' . $path;
    if (!is_file($file)) { fwrite(STDERR, "[FAIL] Missing {$path}\n"); exit(1); }
    $data = file_get_contents($file);
    foreach ($needles as $needle) {
        if (!str_contains($data, $needle)) { fwrite(STDERR, "[FAIL] {$path}: {$needle}\n"); exit(1); }
        $all++;
    }
    echo "[PASS] {$path}\n";
}
foreach (['routes/branches.php', 'app/Http/Controllers/BranchController.php', 'tools/install_a7951_branches.php'] as $f) {
    $result = null;
    $lines = [];
    $cmd = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($root . '/' . $f);
    exec($cmd, $lines, $result);
    if ($result !== 0) { fwrite(STDERR, "[FAIL] PHP syntax: {$f}\n" . implode("\n", $lines)); exit(1); }
    echo "[PASS] PHP syntax {$f}\n";
}
echo "[RESULT] A7951_BRANCHES_SOURCE_READY checks={$all} (PostgreSQL/HTTP not executed)\n";
