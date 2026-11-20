<?php
declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/** A7.8.4 manual evidence register. Pure logic; no Laravel, DB, HTTP or file mutations. */
final class CrmUatExecutionLedger
{
    public const STATUSES = ['NOT_RUN', 'PASS', 'FAIL', 'BLOCKED'];
    public const POLICY_HOLDS = ['announcement_public_assets', 'reports_privacy_pilot'];

    /** @return array<string,mixed> */
    public static function template(): array
    {
        $rows = [];
        foreach (CrmUatWorkflowCoverage::scenarios() as $id => $scenario) {
            $rows[$id] = [
                'status' => 'NOT_RUN', 'role' => '', 'expected_result' => $scenario['name'],
                'observed_result' => '', 'evidence_reference' => '', 'defect_reference' => '',
            ];
        }
        return [
            'stage' => 'A7.8.4',
            'environment' => 'UNVERIFIED',
            'dataset_reference' => '',
            'backup_reference' => '',
            'scenarios' => $rows,
            'policies' => [
                'announcement_public_assets' => ['status' => 'HOLD', 'approval_reference' => ''],
                'reports_privacy_pilot' => ['status' => 'HOLD', 'approval_reference' => ''],
            ],
            'review' => ['status' => 'PENDING', 'review_reference' => ''],
        ];
    }

    /** @param mixed $value @return array<string,mixed> */
    public static function evaluate(mixed $value): array
    {
        if (!is_array($value) || ($value['stage'] ?? null) !== 'A7.8.4') {
            throw new InvalidArgumentException('Invalid ledger stage or document.');
        }
        $env = $value['environment'] ?? null;
        if (!in_array($env, ['UNVERIFIED', 'LOCAL_DISPOSABLE', 'STAGING_DISPOSABLE'], true)) {
            throw new InvalidArgumentException('Unrecognized environment.');
        }
        $dataset = self::shortText($value['dataset_reference'] ?? null);
        $backup = self::shortText($value['backup_reference'] ?? null);
        if (!is_array($value['scenarios'] ?? null)) {
            throw new InvalidArgumentException('Missing UAT scenario register.');
        }
        $known = array_keys(CrmUatWorkflowCoverage::scenarios());
        $submitted = array_keys($value['scenarios']);
        sort($known); sort($submitted);
        if ($known !== $submitted) throw new InvalidArgumentException('UAT scenarios must match all 15 IDs.');

        $policyStates = [];
        $policies = $value['policies'] ?? null;
        if (!is_array($policies)) throw new InvalidArgumentException('Missing policy decisions.');
        $expectedPolicies = self::POLICY_HOLDS;
        $receivedPolicies = array_keys($policies);
        sort($expectedPolicies); sort($receivedPolicies);
        if ($expectedPolicies !== $receivedPolicies) throw new InvalidArgumentException('Unexpected policy set.');
        foreach (self::POLICY_HOLDS as $key) {
            $record = $policies[$key];
            if (!is_array($record) || !in_array($record['status'] ?? null, ['HOLD', 'APPROVAL_RECORDED'], true)) {
                throw new InvalidArgumentException('Invalid policy status.');
            }
            $reference = self::shortText($record['approval_reference'] ?? null);
            if ($record['status'] === 'APPROVAL_RECORDED' && $reference === '') {
                throw new InvalidArgumentException('Policy approval reference required.');
            }
            $policyStates[$key] = $record['status'];
        }

        $counts = array_fill_keys(self::STATUSES, 0);
        foreach (CrmUatWorkflowCoverage::scenarios() as $id => $def) {
            $row = $value['scenarios'][$id];
            if (!is_array($row) || !in_array($row['status'] ?? null, self::STATUSES, true)) {
                throw new InvalidArgumentException('Invalid UAT scenario result.');
            }
            $status = $row['status'];
            $role = self::shortText($row['role'] ?? null);
            $expected = self::shortText($row['expected_result'] ?? null);
            $observed = self::shortText($row['observed_result'] ?? null);
            $evidence = self::shortText($row['evidence_reference'] ?? null);
            $defect = self::shortText($row['defect_reference'] ?? null);
            if ($expected === '') throw new InvalidArgumentException('Scenario expectation cannot be empty.');
            if ($status === 'PASS' && ($role === '' || $observed === '' || $evidence === '')) {
                throw new InvalidArgumentException('PASS requires role, observed result, and evidence reference.');
            }
            if ($status === 'FAIL' && ($role === '' || $observed === '' || $defect === '')) {
                throw new InvalidArgumentException('FAIL requires role, observed result and defect reference.');
            }
            if ($status === 'BLOCKED' && $observed === '') {
                throw new InvalidArgumentException('BLOCKED requires a reason in observed_result.');
            }
            if ($status === 'PASS' && $def['gate'] === 'HOLD_POLICY') {
                $policy = $id === 'UAT-13' ? 'announcement_public_assets' : 'reports_privacy_pilot';
                if ($policyStates[$policy] !== 'APPROVAL_RECORDED') {
                    throw new InvalidArgumentException('Held UAT cannot PASS without recorded policy decision.');
                }
            }
            $counts[$status]++;
        }
        $review = $value['review'] ?? null;
        if (!is_array($review) || !in_array($review['status'] ?? null, ['PENDING', 'MANUAL_REVIEW_RECORDED'], true)) {
            throw new InvalidArgumentException('Invalid review state.');
        }
        $reviewRef = self::shortText($review['review_reference'] ?? null);
        if ($review['status'] === 'MANUAL_REVIEW_RECORDED' && $reviewRef === '') {
            throw new InvalidArgumentException('Manual review reference required.');
        }
        $executed = $counts['PASS'] + $counts['FAIL'];
        if ($executed > 0 && ($env === 'UNVERIFIED' || $dataset === '' || $backup === '')) {
            throw new InvalidArgumentException('Executed UAT requires confirmed disposable environment, dataset and backup references.');
        }
        $holds = count(array_filter($policyStates, static fn ($v) => $v === 'HOLD'));
        if ($counts['FAIL'] > 0) $decision = 'UAT_FAILED_NEEDS_RETEST';
        elseif ($counts['BLOCKED'] > 0) $decision = 'UAT_BLOCKED';
        elseif ($counts['NOT_RUN'] === 15) $decision = 'UAT_NOT_EXECUTED';
        elseif ($holds > 0) $decision = 'UAT_POLICY_HOLD';
        elseif ($counts['NOT_RUN'] > 0) $decision = 'UAT_IN_PROGRESS';
        else $decision = 'READY_FOR_EXTERNAL_SIGNOFF_NOT_RELEASE_APPROVED';
        return [
            'decision' => $decision,
            'total_scenarios' => 15,
            'not_run' => $counts['NOT_RUN'],
            'passed' => $counts['PASS'],
            'failed' => $counts['FAIL'],
            'blocked' => $counts['BLOCKED'],
            'policy_holds' => $holds,
            'manual_review_recorded' => $review['status'] === 'MANUAL_REVIEW_RECORDED',
            'production_release_approved' => false,
        ];
    }

    private static function shortText(mixed $text): string
    {
        if (!is_string($text) || strlen($text) > 300 || str_contains($text, "\0")) {
            throw new InvalidArgumentException('Malformed or oversized ledger field.');
        }
        return trim($text);
    }
}
