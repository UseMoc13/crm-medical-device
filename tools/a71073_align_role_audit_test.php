<?php

declare(strict_types=1);

/**
 * A7.10.7.3: isolated test-contract correction, NOT an authorization change.
 * Adjusts only Tests/Feature/WorkspaceRoleAuditAccessHttpTest.php.
 * --check is read-only, --apply requires exact old source and makes backup.
 */

$root = dirname(__DIR__);
$relative = 'tests/Feature/WorkspaceRoleAuditAccessHttpTest.php';
$target = $root . '/' . $relative;
$backupBase = $root . '/storage/app/private/a71073_test_alignment_backups';
$old = <<<'SOURCE'
    public function test_editor_and_details_render_audit_permission_without_exposing_editor_to_staff(): void
    {
        $this->actingAs($this->admin,'web')->get(route('roles.edit',$this->target))
            ->assertOk()->assertSee('Audit Trail Permission')->assertSee('workspace_audit_access_present');
        $this->get(route('roles.show',$this->target))->assertOk()->assertSee('Audit Trail Access');
        $this->actingAs($this->staff,'web')->get(route('roles.edit',$this->target))
            ->assertOk()->assertDontSee('workspace_audit_access_present')
            ->assertSee('Only Super Admin can change this sensitive permission');
    }
SOURCE;
$new = <<<'SOURCE'
    public function test_role_editor_and_details_require_super_admin_and_hide_audit_controls_from_staff(): void
    {
        // Route RBAC: roles.* is Super-Admin-only, including read-only details.
        // Never weaken route authorization to satisfy an obsolete test assertion.
        $this->actingAs($this->admin,'web')->get(route('roles.edit',$this->target))
            ->assertOk()->assertSee('Audit Trail Permission')->assertSee('workspace_audit_access_present');
        $this->get(route('roles.show',$this->target))->assertOk()->assertSee('Audit Trail Access');
        $this->actingAs($this->staff,'web')->get(route('roles.edit',$this->target))
            ->assertForbidden()->assertDontSee('workspace_audit_access_present');
        $this->get(route('roles.show',$this->target))->assertForbidden();
    }
SOURCE;

function a71073_normalize(string $s): string
{
    return str_replace(["\r\n", "\r"], "\n", $s);
}

function a71073_status(string $contents, string $old, string $new): string
{
    $normalized = a71073_normalize($contents);
    $oldCount = substr_count($normalized, $old);
    $newCount = substr_count($normalized, $new);
    if ($oldCount === 1 && $newCount === 0) return 'OLD';
    if ($oldCount === 0 && $newCount === 1) return 'NEW';
    return 'UNKNOWN';
}

function a71073_lint(string $code): void
{
    $temp = tempnam(sys_get_temp_dir(), 'a71073_');
    if ($temp === false) throw new RuntimeException('Cannot create temp file for PHP lint.');
    try {
        if (file_put_contents($temp, $code) === false) throw new RuntimeException('Cannot write temp lint file.');
        $lines = [];
        $rc = 0;
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($temp) . ' 2>&1', $lines, $rc);
        if ($rc !== 0) throw new RuntimeException('PHP lint failed: ' . implode(' ', $lines));
    } finally {
        @unlink($temp);
    }
}

function a71073_write(string $path, string $code): void
{
    $temp = $path . '.a71073.tmp';
    if (file_put_contents($temp, $code, LOCK_EX) === false || !@rename($temp, $path)) {
        @unlink($temp);
        throw new RuntimeException('Failed to write ' . $path);
    }
}

try {
    $arg = $argv[1] ?? '--check';
    if ($arg === '--self-test') {
        if (a71073_status($old, $old, $new) !== 'OLD' || a71073_status($new, $old, $new) !== 'NEW') {
            throw new RuntimeException('Source marker contract failed.');
        }
        $fixtureOld = "<?php\nclass A71073Fixture {\n" . $old . "\n}\n";
        $fixtureNew = str_replace($old, $new, $fixtureOld);
        a71073_lint($fixtureOld);
        a71073_lint($fixtureNew);
        if (str_contains($fixtureNew, '->assertOk()->assertDontSee')
            || substr_count($fixtureNew, '->assertForbidden()') !== 2) {
            throw new RuntimeException('Replacement lacks denied role-route assertions.');
        }
        echo "[PASS] exact-one-block replacement + PHP syntax + 2 forbidden role routes\n";
        echo "[RESULT] A71073_SELF_TEST_PASS; no project files modified\n";
        exit(0);
    }
    if (!is_file($target)) throw new RuntimeException('Missing required test: ' . $relative);
    $before = file_get_contents($target);
    if ($before === false) throw new RuntimeException('Cannot read test file.');

    if (str_starts_with($arg, '--rollback=')) {
        $id = substr($arg, strlen('--rollback='));
        if (!preg_match('/^\d{8}-\d{6}-[a-f0-9]{8}$/D', $id)) throw new RuntimeException('Invalid backup id.');
        $dir = $backupBase . '/' . $id;
        $manifest = json_decode((string) @file_get_contents($dir . '/manifest.json'), true);
        $backup = $dir . '/WorkspaceRoleAuditAccessHttpTest.php.bak';
        if (!is_array($manifest) || ($manifest['file'] ?? null) !== $relative || !is_file($backup)) {
            throw new RuntimeException('Backup not found or incompatible.');
        }
        if (hash('sha256', $before) !== ($manifest['patched_sha256'] ?? null)) {
            throw new RuntimeException('Test differs from patched backup state. Refusing rollback overwrite.');
        }
        $original = file_get_contents($backup);
        if ($original === false || hash('sha256', $original) !== ($manifest['original_sha256'] ?? null)) {
            throw new RuntimeException('Backup checksum mismatch. Refusing rollback.');
        }
        a71073_lint($original);
        a71073_write($target, $original);
        echo "[RESULT] A71073_TEST_ONLY_ROLLED_BACK; restored original test\n";
        exit(0);
    }
    if ($arg !== '--check' && $arg !== '--apply') throw new RuntimeException('Usage: --check | --apply | --self-test | --rollback=BACKUP_ID');

    $state = a71073_status($before, $old, $new);
    if ($state === 'UNKNOWN') throw new RuntimeException('Unrecognized/modified test method. No changes made. Inspect the test manually.');
    if ($state === 'NEW') {
        a71073_lint($before);
        echo "[PASS] Staff roles.edit/roles.show are asserted 403; Super Admin 200\n";
        echo "[RESULT] A71073_TEST_ALREADY_ALIGNED; no files modified\n";
        exit(0);
    }
    $lf = a71073_normalize($before);
    $converted = str_replace($old, $new, $lf);
    if ($before !== $lf) {
        // Retain Windows CRLF when the entire input uses CRLF.
        if (str_contains($before, "\r\n") && !preg_match('/(?<!\r)\n/', $before)) {
            $converted = str_replace("\n", "\r\n", $converted);
        }
    }
    a71073_lint($converted);
    echo "[PASS] One obsolete test expectation identified; replacement PHP lint OK\n";
    if ($arg === '--check') {
        echo "[RESULT] A71073_TEST_PATCH_READY; one test file; no files modified\n";
        exit(0);
    }
    $id = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
    $dir = $backupBase . '/' . $id;
    if (!@mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Could not create test backup directory.');
    $backup = $dir . '/WorkspaceRoleAuditAccessHttpTest.php.bak';
    if (file_put_contents($backup, $before, LOCK_EX) === false) throw new RuntimeException('Backup failed. No files modified.');
    $manifest = [
        'file' => $relative,
        'original_sha256' => hash('sha256', $before),
        'patched_sha256' => hash('sha256', $converted),
        'note' => 'Test only; never adjust application route authorization',
    ];
    if (file_put_contents($dir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) {
        throw new RuntimeException('Backup manifest failed. No files modified.');
    }
    a71073_write($target, $converted);
    echo "[BACKUP_ID] {$id}\n";
    echo "[BACKUP] storage/app/private/a71073_test_alignment_backups/{$id}\n";
    echo "[RESULT] A71073_ROLE_AUDIT_TEST_ALIGNED; files=1; application source and database untouched\n";
} catch (Throwable $e) {
    fwrite(STDERR, '[FAIL] ' . $e->getMessage() . "\n");
    fwrite(STDERR, "[RESULT] A71073_TEST_ALIGNMENT_FAILED\n");
    exit(1);
}
