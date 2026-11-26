<?php

declare(strict_types=1);

namespace App\Support;

/**
 * A7.10.7: pure metadata policy for the Workspace Audit permission.
 * This is not an authorization grant and contains no database operations.
 */
final class CrmAuditPermissionSeedPolicy
{
    public const PERMISSION = 'workspace_audit.view';
    public const MODULE = 'workspace_audit';
    public const ACTION = 'view';
    public const DESCRIPTION = 'Read sanitized workspace audit trail';
    public const MIGRATION = '2026_11_18_010000_create_workspace_audit_events';

    /** @param object|null $row Record queried from permissions, or null if missing. */
    public static function classify(?object $row): string
    {
        if ($row === null) {
            return 'MISSING';
        }
        if ((string) ($row->permission_name ?? '') !== self::PERMISSION ||
            (string) ($row->module ?? '') !== self::MODULE ||
            (string) ($row->action ?? '') !== self::ACTION ||
            trim((string) ($row->permission_id ?? '')) === '') {
            return 'CONFLICT';
        }
        return 'READY';
    }

    /** @return array<string,string> Fields of the ONE permission; never role grants. */
    public static function insertFields(string $uuid, string $timestamp): array
    {
        return [
            'permission_id' => $uuid,
            'permission_name' => self::PERMISSION,
            'module' => self::MODULE,
            'action' => self::ACTION,
            'description' => self::DESCRIPTION,
            'created_at' => $timestamp,
        ];
    }
}
