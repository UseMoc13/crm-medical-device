<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CrmAuditPermissionSeedPolicy as Policy;
use PHPUnit\Framework\TestCase;

/** Pure policy tests; no Laravel DB, migrations, or role changes. */
final class CrmAuditPermissionSeedPolicyTest extends TestCase
{
    public function test_missing_row_is_recognized_without_granting_any_roles(): void
    {
        self::assertSame('MISSING', Policy::classify(null));
        self::assertSame('workspace_audit.view', Policy::PERMISSION);
    }

    public function test_correct_row_is_ready(): void
    {
        $row = (object) [
            'permission_id' => 'id-1', 'permission_name' => Policy::PERMISSION,
            'module' => 'workspace_audit', 'action' => 'view',
        ];
        self::assertSame('READY', Policy::classify($row));
    }

    public function test_conflicting_metadata_is_never_silently_rewritten(): void
    {
        $row = (object) [
            'permission_id' => 'id-1', 'permission_name' => Policy::PERMISSION,
            'module' => 'reporting', 'action' => 'view',
        ];
        self::assertSame('CONFLICT', Policy::classify($row));
        $row->module = 'workspace_audit';
        $row->action = 'delete';
        self::assertSame('CONFLICT', Policy::classify($row));
    }

    public function test_insert_fields_contain_one_catalogue_permission_and_no_grants(): void
    {
        $fields = Policy::insertFields('test-id', '2026-11-26 12:00:00');
        self::assertSame('workspace_audit.view', $fields['permission_name']);
        self::assertSame('workspace_audit', $fields['module']);
        self::assertSame('view', $fields['action']);
        self::assertSame('test-id', $fields['permission_id']);
        self::assertArrayNotHasKey('role_id', $fields);
        self::assertArrayNotHasKey('user_id', $fields);
        self::assertArrayNotHasKey('data_scope', $fields);
    }
}
