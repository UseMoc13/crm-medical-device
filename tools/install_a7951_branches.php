<?php
/**
 * Run from CRM project root:
 *   php tools/install_a7951_branches.php --check
 *   php tools/install_a7951_branches.php --apply
 * Never connects to PostgreSQL or changes schema/data.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$checkOnly = in_array('--check', $argv, true);
if (!$apply && !$checkOnly) {
    fwrite(STDERR, "Usage: php tools/install_a7951_branches.php --check|--apply\n");
    exit(2);
}

$required = [
    'app/Http/Controllers/BranchController.php',
    'routes/branches.php',
    'resources/views/branches/_form.blade.php',
    'resources/views/branches/_style.blade.php',
    'resources/views/branches/index.blade.php',
    'resources/views/branches/create.blade.php',
    'resources/views/branches/edit.blade.php',
    'resources/views/branches/show.blade.php',
];
foreach ($required as $name) {
    if (!is_file($root . '/' . $name)) {
        fwrite(STDERR, "[FAIL] Missing file: {$name}. Extract ZIP into the CRM project root first.\n");
        exit(1);
    }
}

$sidebarOld = <<<'BLADE'
        <a href="#" data-label="Branches">
            <span class="ico">⌂</span>
            <span class="nav-text">Branches</span>
        </a>
BLADE;
$sidebarNew = <<<'BLADE'
        @if(auth()->check() && app(\App\Services\CrmPermissionResolver::class)->isSuperAdmin(auth()->user()))
        <a href="{{ route('branches.index') }}"
           class="{{ request()->routeIs('branches.*') ? 'active' : '' }}"
           data-label="Branches">
            <span class="ico">⌂</span>
            <span class="nav-text">Branches</span>
        </a>
        @endif
BLADE;

$helpOld = <<<'BLADE'
                        <span class="form-hint">
                            Optional branch assignment for this user.
                        </span>
BLADE;
$helpNew = <<<'BLADE'
                        <span class="form-hint">
                            Optional branch assignment for this user.
                        </span>
                        <a href="{{ route('branches.index') }}"
                           class="form-hint" style="display:inline-block;margin-top:6px;color:#168d81;font-weight:650;">
                            Manage Branches →
                        </a>
BLADE;

$changes = [
    'routes/web.php' => [
        "require __DIR__ . '/teams.php';",
        "require __DIR__ . '/teams.php';\nrequire __DIR__ . '/branches.php';",
    ],
    'resources/views/components/sidebar.blade.php' => [$sidebarOld, $sidebarNew],
    'app/Services/CrmRouteAuthorizationPolicy.php' => [
        "preg_match('/^(roles|users)\\./D', \$routeName)",
        "preg_match('/^(roles|users|branches)\\./D', \$routeName)",
    ],
    'resources/views/users/create.blade.php' => [$helpOld, $helpNew],
    'resources/views/users/edit.blade.php' => [$helpOld, $helpNew],
];

$pending = [];
foreach ($changes as $path => [$old, $new]) {
    $file = $root . '/' . $path;
    if (!is_file($file)) {
        fwrite(STDERR, "[FAIL] Missing target file: {$path}\n");
        exit(1);
    }
    $original = file_get_contents($file);
    if ($original === false) throw new RuntimeException("Cannot read {$path}");
    // Windows checkouts often use CRLF while package anchors use LF.
    // Match against normalized line endings and restore the ORIGINAL style
    // when writing; this avoids rewriting entire large Blade files.
    $lineEnding = str_contains($original, "\r\n") ? "\r\n" : "\n";
    $contents = str_replace(["\r\n", "\r"], "\n", $original);
    $oldCount = substr_count($contents, $old);
    $newCount = substr_count($contents, $new);
    if ($newCount === 1 && ($oldCount === 0 || ($oldCount === 1 && str_contains($new, $old)))) {
        echo "[OK] Already integrated: {$path}\n";
        continue;
    }
    if ($oldCount !== 1 || $newCount !== 0) {
        fwrite(STDERR, "[FAIL] Anchor mismatch in {$path}: expected 1 old, 0 new; found {$oldCount} old, {$newCount} new. No files changed.\n");
        exit(1);
    }
    $normalizedResult = str_replace($old, $new, $contents);
    $pending[$path] = $lineEnding === "\r\n"
        ? str_replace("\n", "\r\n", $normalizedResult)
        : $normalizedResult;
    echo "[READY] {$path} [EOL: " . ($lineEnding === "\r\n" ? "CRLF" : "LF") . "]\n";
}

if ($checkOnly) {
    echo "[RESULT] BRANCHES_PATCH_COMPATIBLE; pending=" . count($pending) . "; no files modified\n";
    exit(0);
}
if (!$pending) {
    echo "[RESULT] BRANCHES_ALREADY_INTEGRATED\n";
    exit(0);
}

$stamp = date('Ymd-His') . '-' . substr(hash('sha256', implode('|', array_keys($pending))), 0, 7);
$backupRoot = $root . '/storage/app/private/a7951_branch_install_backups/' . $stamp;
if (!is_dir($backupRoot) && !mkdir($backupRoot, 0770, true) && !is_dir($backupRoot)) {
    throw new RuntimeException('Failed creating private backup directory. No files modified.');
}
foreach (array_keys($pending) as $path) {
    $target = $backupRoot . '/' . $path;
    if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0770, true) && !is_dir(dirname($target))) {
        throw new RuntimeException('Backup directory creation failed. No files modified.');
    }
    if (!copy($root . '/' . $path, $target)) {
        throw new RuntimeException("Backup failed for {$path}. No files modified.");
    }
}
foreach ($pending as $path => $newText) {
    $file = $root . '/' . $path;
    if (file_put_contents($file, $newText, LOCK_EX) !== strlen($newText)) {
        fwrite(STDERR, "[FAIL] Write error in {$path}. Originals at {$backupRoot}\n");
        exit(1);
    }
    echo "[PATCH] {$path}\n";
}
echo "[BACKUP] {$backupRoot}\n[RESULT] BRANCHES_INTEGRATED\n";
