<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * A7.5.2.2c — review instructions and evidence, NOT a data remediation engine.
 * The A7.5.2.1 investigator remains the only source for the four finding IDs.
 * No writes, DDL, seeders, file mutations, or guessed status mappings.
 */
final class CrmHistoricalRemediationReview
{
    private const POLICIES = [
        'delivery.dispatched_item_missing_warehouse' => [
            'priority' => 'high',
            'decision' => 'manual_provenance_verification',
            'guidance' => 'Compare the original Delivery Item, serial product, warehouse dispatch evidence and stock-out movement. Never create a second stock-out or infer warehouse solely from a movement.',
        ],
        'delivery.stockout_quantity_mismatch' => [
            'priority' => 'high',
            'decision' => 'manual_stock_reconciliation',
            'guidance' => 'Reconcile each warehouse/product allocation against existing stock-out movements. Check whether a NULL warehouse explains the apparent mismatch; do not change inventory quantities.',
        ],
        'installation.unknown_status' => [
            'priority' => 'medium',
            'decision' => 'format_normalization_requires_review',
            'guidance' => 'A case-only difference (completed vs Completed) is a formatting candidate, not permission to rewrite. Inspect linked Delivery, checklist and device state before approving a separate correction.',
        ],
        'billing.unknown_status' => [
            'priority' => 'medium',
            'decision' => 'business_meaning_required',
            'guidance' => 'Unpaid is not a currently supported Invoice state. Verify the original billing event and payment history. Never guess Draft, Sent, or Paid.',
        ],
    ];

    public function __construct(private readonly CrmIntegrationInvestigation $investigation) {}

    /** @return array<string,array<string,mixed>> */
    public static function policies(): array
    {
        return self::POLICIES;
    }

    /**
     * @return array<string,mixed> The summary never contains record UUIDs or raw status values.
     */
    public function run(bool $details = false, int $limit = 10): array
    {
        if ($details && !app()->environment('local')) {
            throw new InvalidArgumentException('Detailed review is restricted to APP_ENV=local.');
        }
        $investigation = $this->investigation->run($details, $limit);
        $cases = [];
        foreach ($investigation['checks'] as $check) {
            $id = (string) $check['id'];
            $policy = self::POLICIES[$id] ?? null;
            if (!$policy) {
                $cases[] = ['id' => $id, 'status' => 'error', 'error_type' => 'UnknownReviewPolicy'];
                continue;
            }
            $case = [
                'id' => $id,
                'status' => $check['status'],
                'count' => $check['count'] ?? null,
                'priority' => $policy['priority'],
                'decision' => $policy['decision'],
                'guidance' => $policy['guidance'],
                'auto_apply_allowed' => false,
            ];
            if (isset($check['missing_schema'])) $case['missing_schema'] = $check['missing_schema'];
            if (isset($check['error_type'])) $case['error_type'] = $check['error_type'];
            if ($details && $check['status'] === 'flagged') {
                $case['evidence'] = $this->prepareEvidence($id, $check['details'] ?? []);
                $case['details_limited'] = $check['details_limited'] ?? false;
            }
            $cases[] = $case;
        }
        $flagged = count(array_filter($cases, static fn (array $c) => $c['status'] === 'flagged'));
        $errors = count(array_filter($cases, static fn (array $c) => $c['status'] === 'error'));
        $skipped = count(array_filter($cases, static fn (array $c) => $c['status'] === 'skipped'));
        return [
            'stage' => 'A7.5.2.2c',
            'read_only' => true,
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'checked' => count($cases),
                'flagged_rules' => $flagged,
                'skipped' => $skipped,
                'errors' => $errors,
                'shared_delivery_records' => $investigation['summary']['overlapping_delivery_count'] ?? null,
                'automatic_corrections' => 0,
                'human_review_required' => $flagged > 0,
            ],
            'cases' => $cases,
            'next_step' => 'Confirm original source documents and responsible approver before creating any separate, audited corrective migration. No changes are made by this command.',
        ];
    }

    /** @param array<int,array<string,mixed>> $rows
     *  @return array<int,array<string,mixed>>
     */
    private function prepareEvidence(string $id, array $rows): array
    {
        if ($id === 'installation.unknown_status') {
            return array_map(fn (array $row) => $this->installationEvidence($row), $rows);
        }
        if ($id === 'billing.unknown_status') {
            return array_map(static function (array $row) {
                $formatCandidate = $row['classification'] === 'case_or_outer_whitespace_only';
                return $row + [
                    'readiness' => $formatCandidate ? 'format_only_candidate_needs_approval' : 'business_review_required',
                    'auto_apply_allowed' => false,
                ];
            }, $rows);
        }
        if ($id === 'delivery.dispatched_item_missing_warehouse') {
            return array_map(static function (array $row) {
                $observed = $row['observed_stockout_warehouses'] ?? [];
                return $row + [
                    'unique_observed_stockout_warehouse' => count($observed) === 1 && ($observed[0]['warehouse_id'] ?? null) !== null,
                    'readiness' => 'manual_provenance_review',
                    'auto_apply_allowed' => false,
                ];
            }, $rows);
        }
        if ($id === 'delivery.stockout_quantity_mismatch') {
            return array_map(static fn (array $row) => $row + [
                'readiness' => 'manual_stock_reconciliation',
                'auto_apply_allowed' => false,
            ], $rows);
        }
        return [];
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private function installationEvidence(array $row): array
    {
        $id = (string) ($row['record_id'] ?? '');
        $result = $row + [
            'readiness' => 'manual_review_required',
            'auto_apply_allowed' => false,
            'preconditions' => [
                'related_delivery_delivered' => null,
                'checklist_complete' => null,
                'all_delivery_serials_installed' => null,
            ],
        ];
        // Optional evidence is genuinely unavailable if a module is absent.
        $tables = ['installations', 'deliveries', 'installation_checklists', 'delivery_items', 'serial_numbers'];
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $result['readiness'] = 'insufficient_schema_for_evidence';
                return $result;
            }
        }
        $installation = DB::table('installations')->where('installation_id', $id)
            ->select('installation_id', 'delivery_id')->first();
        if (!$installation) {
            $result['readiness'] = 'record_no_longer_exists';
            return $result;
        }
        $deliveryStatus = DB::table('deliveries')->where('delivery_id', $installation->delivery_id)->value('status');
        $checklistTotal = (int) DB::table('installation_checklists')->where('installation_id', $id)->count();
        $pendingChecklist = (int) DB::table('installation_checklists')
            ->where('installation_id', $id)->where('is_completed', false)->count();
        $serialRows = DB::table('delivery_items as di')
            ->leftJoin('serial_numbers as sn', 'sn.serial_number_id', '=', 'di.serial_number_id')
            ->where('di.delivery_id', $installation->delivery_id)
            ->select('sn.status')->get();
        $allInstalled = $serialRows->isNotEmpty()
            && $serialRows->every(static fn ($item) => $item->status === 'installed');
        $preconditions = [
            'related_delivery_delivered' => strtolower(trim((string) $deliveryStatus)) === 'delivered',
            'checklist_complete' => $checklistTotal > 0 && $pendingChecklist === 0,
            'all_delivery_serials_installed' => $allInstalled,
        ];
        $result['preconditions'] = $preconditions;
        $formatOnly = ($row['classification'] ?? '') === 'case_or_outer_whitespace_only';
        $result['readiness'] = $formatOnly && !in_array(false, $preconditions, true)
            ? 'format_only_candidate_needs_approval'
            : 'manual_review_required';
        return $result;
    }
}
