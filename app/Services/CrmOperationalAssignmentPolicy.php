<?php

namespace App\Services;

/**
 * A7.9.9 controlled correction policy. Do not rewrite historical performer
 * records or finalized medical-device events as though someone else acted.
 * This is NOT an RBAC grant and does not relax normal web assignment rules.
 */
final class CrmOperationalAssignmentPolicy
{
    private const TABLES = [
        'service_tickets' => [
            'key' => 'service_ticket_id',
            'active' => ['open', 'in_progress', 'on_hold'],
        ],
        'maintenance_schedules' => [
            'key' => 'maintenance_schedule_id',
            'active' => ['scheduled', 'in progress'],
        ],
        'installations' => [
            'key' => 'installation_id',
            'active' => ['scheduled', 'in progress'],
        ],
    ];

    /** @return list<string> */
    public static function mutableTables(): array
    {
        return array_keys(self::TABLES);
    }

    public static function key(string $table): ?string
    {
        return self::TABLES[$table]['key'] ?? null;
    }

    public static function mutable(string $table, ?string $status): bool
    {
        if (!isset(self::TABLES[$table]) || $status === null) {
            return false;
        }
        return in_array(mb_strtolower(trim($status)), self::TABLES[$table]['active'], true);
    }

    public static function classification(string $table, ?string $status): string
    {
        if ($table === 'maintenance_records') {
            return 'HISTORICAL_PERFORMER_DO_NOT_REWRITE';
        }
        if (!isset(self::TABLES[$table])) {
            return 'NOT_SUPPORTED_FOR_CLI_REASSIGNMENT';
        }
        return self::mutable($table, $status)
            ? 'OPERATIONAL_REVIEW'
            : 'HISTORICAL_FINALIZED_DO_NOT_REWRITE';
    }
}
