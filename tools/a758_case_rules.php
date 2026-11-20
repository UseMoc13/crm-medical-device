<?php
/**
 * A7.5.8 pure evidence classification (no Laravel, DB, filesystem writes).
 * Verdicts are investigation outcomes, NEVER permission to update records.
 */
declare(strict_types=1);

final class A758CaseRules
{
    /** @param array<string,mixed> $facts @return array{decision:string,issues:list<string>,change_authorized:bool} */
    public static function installation(array $facts): array
    {
        $issues = [];
        $status = (string) ($facts['status'] ?? '');
        $formatOnly = $status !== 'Completed' && strtolower(trim($status)) === 'completed';
        if (!$formatOnly) $issues[] = 'NOT_CASE_ONLY_COMPLETED';
        if (($facts['delivery_delivered'] ?? false) !== true) $issues[] = 'DELIVERY_NOT_CONFIRMED';
        if ((int) ($facts['checklist_total'] ?? 0) < 1 || (int) ($facts['checklist_pending'] ?? -1) !== 0) {
            $issues[] = 'CHECKLIST_NOT_VERIFIED';
        }
        if ((int) ($facts['item_count'] ?? 0) < 1 || ($facts['unique_serials'] ?? false) !== true) {
            $issues[] = 'SERIAL_ALLOCATION_NOT_VERIFIED';
        }
        if ((int) ($facts['missing_serials'] ?? -1) !== 0) $issues[] = 'SERIAL_REFERENCE_MISSING';
        if ((int) ($facts['assigned_serials'] ?? 0) > 0) $issues[] = 'SERIAL_ASSIGNED_NOT_INSTALLED';
        if ((int) ($facts['other_serials'] ?? 0) > 0) $issues[] = 'SERIAL_NONINSTALLED_OR_UNKNOWN';
        if ((int) ($facts['installed_serials'] ?? 0) !== (int) ($facts['item_count'] ?? 0)) {
            $issues[] = 'NOT_ALL_SERIALS_INSTALLED';
        }
        if ($issues === []) {
            return [
                'decision' => 'FORMAT_ONLY_CANDIDATE_PENDING_ORIGINAL_HANDOVER_AND_APPROVAL',
                'issues' => [], 'change_authorized' => false,
            ];
        }
        return ['decision' => 'HOLD_INSTALLATION_LIFECYCLE_REVIEW', 'issues' => $issues, 'change_authorized' => false];
    }

    /** @param array<string,mixed> $facts @return array{decision:string,issues:list<string>,change_authorized:bool} */
    public static function invoice(array $facts): array
    {
        $status = (string) ($facts['status'] ?? '');
        if ($status === 'unpaid' || strtolower(trim($status)) === 'unpaid') {
            return [
                'decision' => 'HOLD_ORIGINAL_ISSUANCE_AND_PAYMENT_PROOF',
                'issues' => ['UNPAID_HAS_NO_CANONICAL_WORKFLOW_MAPPING'],
                'change_authorized' => false,
            ];
        }
        if (in_array($status, ['Draft', 'Sent', 'Paid', 'Cancelled'], true)) {
            return ['decision' => 'CANONICAL_STATUS_NO_CORRECTION_PROPOSED', 'issues' => [], 'change_authorized' => false];
        }
        $possible = array_values(array_filter(['Draft','Sent','Paid','Cancelled'],
            static fn(string $target): bool => strtolower($target) === strtolower(trim($status))));
        if (count($possible) === 1) {
            return [
                'decision' => 'FORMAT_ONLY_CANDIDATE_PENDING_DOCUMENTS_AND_APPROVAL',
                'issues' => ['FORMAT_DIFFERENCE_UNVERIFIED'],
                'change_authorized' => false,
            ];
        }
        return [
            'decision' => 'HOLD_UNRECOGNIZED_INVOICE_BUSINESS_STATE',
            'issues' => ['ORIGINAL_BUSINESS_MEANING_REQUIRED'],
            'change_authorized' => false,
        ];
    }
}
