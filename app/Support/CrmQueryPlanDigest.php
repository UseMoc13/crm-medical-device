<?php

namespace App\Support;

use JsonException;
use UnexpectedValueException;

/** Summarizes EXPLAIN JSON; never returns SQL, object names or filter values. */
final class CrmQueryPlanDigest
{
    /** @return array{cost:float,estimated_rows:int,nodes:list<string>,sequential_scans:int,index_scans:int} */
    public static function summarize(string $json): array
    {
        try {
            $data = json_decode($json, true, 128, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedValueException('Invalid EXPLAIN JSON.', previous: $e);
        }
        $root = $data[0]['Plan'] ?? $data['Plan'] ?? null;
        if (!is_array($root) || !is_string($root['Node Type'] ?? null)) {
            throw new UnexpectedValueException('Unexpected EXPLAIN plan structure.');
        }
        $nodes = [];
        $seq = 0;
        $idx = 0;
        self::walk($root, $nodes, $seq, $idx);
        return [
            'cost' => round((float) ($root['Total Cost'] ?? 0), 2),
            'estimated_rows' => max(0, (int) ($root['Plan Rows'] ?? 0)),
            'nodes' => array_values(array_unique($nodes)),
            'sequential_scans' => $seq,
            'index_scans' => $idx,
        ];
    }

    /** @param array<string,mixed> $node @param list<string> $types */
    private static function walk(array $node, array &$types, int &$seq, int &$idx): void
    {
        $type = (string) ($node['Node Type'] ?? 'Unknown');
        // The output is a fixed, short diagnostic vocabulary.
        $allowed = [
            'Seq Scan', 'Index Scan', 'Index Only Scan', 'Bitmap Index Scan',
            'Bitmap Heap Scan', 'Aggregate', 'HashAggregate', 'GroupAggregate',
            'Sort', 'Incremental Sort', 'Limit', 'Nested Loop', 'Hash Join',
            'Merge Join', 'Gather', 'Gather Merge', 'Append', 'Memoize',
            'Materialize', 'Result', 'Subquery Scan', 'CTE Scan', 'Unique',
            'LockRows', 'Parallel Seq Scan', 'Parallel Index Scan', 'Other',
        ];
        $safe = in_array($type, $allowed, true) ? $type : 'Other';
        $types[] = $safe;
        if ($safe === 'Seq Scan' || $safe === 'Parallel Seq Scan') $seq++;
        if (in_array($safe, ['Index Scan','Index Only Scan','Bitmap Index Scan','Parallel Index Scan'], true)) $idx++;
        foreach (($node['Plans'] ?? []) as $child) {
            if (is_array($child)) self::walk($child, $types, $seq, $idx);
        }
    }
}
