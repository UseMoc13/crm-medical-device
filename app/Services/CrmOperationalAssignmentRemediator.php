<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Strict, one-record reassignment for already-ineligible operational owners.
 * All changes and audit records commit/roll back together. Cannot be called
 * via HTTP; no controller or public route is exposed by A799.
 */
final class CrmOperationalAssignmentRemediator
{
    /** @return array<string, mixed> */
    public function preview(string $table, string $recordId, string $fromId, string $toId): array
    {
        return $this->inspect($table, $recordId, $fromId, $toId, false);
    }

    /** @return array<string, mixed> */
    public function apply(string $table, string $recordId, string $fromId, string $toId, string $reason): array
    {
        $reason = trim($reason);
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 500) {
            throw ValidationException::withMessages(['reason' => 'Reason must be 10 to 500 characters.']);
        }
        return DB::transaction(function () use ($table, $recordId, $fromId, $toId, $reason): array {
            $plan = $this->inspect($table, $recordId, $fromId, $toId, true);
            $key = CrmOperationalAssignmentPolicy::key($table);
            if ($key === null) {
                throw new \LogicException('Unsafe table rejected.');
            }

            $changes = ['user_id' => $toId];
            if (in_array($table, ['maintenance_schedules', 'installations'], true)) {
                $changes['updated_at'] = now();
            }
            // The CLI is not a logged-in user. Preserve created_by/updated_by
            // and store a system-initiated audit entry with user_id=NULL.
            $count = DB::table($table)
                ->where($key, $recordId)
                ->where('user_id', $fromId)
                ->where('status', $plan['status'])
                ->update($changes);
            if ($count !== 1) {
                throw ValidationException::withMessages(['record' => 'Record changed during correction. Nothing committed.']);
            }

            DB::table('audit_logs')->insert([
                'audit_log_id' => (string) Str::uuid(),
                'user_id' => null,
                'action' => 'UPDATE',
                'table_name' => $table,
                'record_id' => $recordId,
                'old_value' => json_encode(['user_id' => $fromId, 'status' => $plan['status']], JSON_THROW_ON_ERROR),
                'new_value' => json_encode([
                    'user_id' => $toId,
                    'status' => $plan['status'],
                    'correction' => 'A799_CLI_REASSIGN',
                    'reason' => $reason,
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
            $plan['applied'] = true;
            return $plan;
        }, 3);
    }

    /** @return array<string, mixed> */
    private function inspect(string $table, string $recordId, string $fromId, string $toId, bool $lock): array
    {
        $key = CrmOperationalAssignmentPolicy::key($table);
        if ($key === null) {
            throw ValidationException::withMessages(['table' => 'Only operational tickets, schedules and installations may be corrected.']);
        }
        if ($fromId === $toId) {
            throw ValidationException::withMessages(['to' => 'Replacement must differ from current assignee.']);
        }

        $query = DB::table($table)->where($key, $recordId);
        $record = ($lock ? $query->lockForUpdate() : $query)->first();
        if ($record === null) {
            throw ValidationException::withMessages(['record' => 'Record not found.']);
        }
        if ((string) ($record->user_id ?? '') !== $fromId) {
            throw ValidationException::withMessages(['from' => 'Current assignee differs from --from. Refresh the audit before retrying.']);
        }
        $status = (string) ($record->status ?? '');
        if (!CrmOperationalAssignmentPolicy::mutable($table, $status)) {
            throw ValidationException::withMessages(['status' => 'This record is finalized or otherwise not open. Historical facts must not be rewritten.']);
        }

        $eligibility = app(CrmUserAssignmentEligibility::class);
        $oldQuery = DB::table('users')->where('user_id', $fromId);
        $old = ($lock ? $oldQuery->lockForUpdate() : $oldQuery)
            ->first(['user_id', 'name', 'branch_id']);
        $newQuery = $eligibility->eligibleQuery('technician')->whereKey($toId);
        $new = ($lock ? $newQuery->lockForUpdate() : $newQuery)
            ->first(['users.user_id', 'users.name', 'users.branch_id']);
        if ($old === null || $new === null) {
            throw ValidationException::withMessages(['to' => 'Original user missing or replacement is not an active, eligible Technician.']);
        }
        if ($eligibility->eligibleQuery('technician')->whereKey($fromId)->exists()) {
            throw ValidationException::withMessages(['from' => 'Original assignee is currently eligible. Use the normal CRM Edit workflow for routine reassignment.']);
        }
        // Never silently cross branches from a CLI that has no authenticated
        // data-scope context. An operator must handle that via application UI.
        if ((string) ($old->branch_id ?? '') !== (string) ($new->branch_id ?? '')) {
            throw ValidationException::withMessages(['to' => 'Users belong to different branches. Use the CRM Edit workflow with an authorized account.']);
        }

        return [
            'table' => $table,
            'record_id' => $recordId,
            'status' => $status,
            'previous_id' => $fromId,
            'previous_name' => $old->name,
            'replacement_id' => $toId,
            'replacement_name' => $new->name,
            'branch_id' => $old->branch_id,
            'applied' => false,
        ];
    }
}
