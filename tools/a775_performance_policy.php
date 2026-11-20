<?php

declare(strict_types=1);

/** Observational flags only; none implies that a new index is appropriate. */
final class CrmPerformancePreflightPolicy
{
    /** @return list<string> */
    public static function reviewFlags(int $liveRows, int $deadRows, int $seqScans, int $idxScans, int $indexCount): array
    {
        if (min($liveRows, $deadRows, $seqScans, $idxScans, $indexCount) < 0) {
            throw new InvalidArgumentException('Statistics must not be negative.');
        }
        $flags = [];
        if ($liveRows >= 1000 && $seqScans >= 100 && $seqScans > 3 * max(1, $idxScans)) {
            $flags[] = 'REVIEW_HIGH_SEQUENTIAL_SCAN_COUNT';
        }
        if ($liveRows >= 1000 && $deadRows >= 200 && $deadRows / max(1, $liveRows + $deadRows) >= 0.2) {
            $flags[] = 'REVIEW_DEAD_TUPLE_RATIO';
        }
        if ($liveRows >= 1000 && $indexCount === 0) {
            $flags[] = 'REVIEW_LARGE_TABLE_WITHOUT_INDEX';
        }
        return $flags;
    }
}
