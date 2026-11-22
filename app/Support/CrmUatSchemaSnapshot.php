<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/** Pure PostgreSQL catalog metadata projection. NEVER reads CRM business rows. */
final class CrmUatSchemaSnapshot
{
    public const FORMAT = 'CRM_A791_SCHEMA_ONLY_V1';

    /**
     * @param list<object> $tables
     * @param list<object> $columns
     * @param list<object> $constraints
     * @param list<object> $enums
     * @param list<object> $triggers
     * @return array<string,mixed>
     */
    public static function build(
        array $tables,
        array $columns,
        array $constraints,
        array $enums,
        array $triggers
    ): array {
        if ($tables === [] || count($tables) > 250 || count($columns) > 12000
            || count($constraints) > 6000 || count($enums) > 2000 || count($triggers) > 2000) {
            throw new InvalidArgumentException('Schema catalog count outside supported safety bounds.');
        }

        $mapped = [];
        foreach ($tables as $row) {
            $name = self::identifier($row->table_name ?? null);
            if (isset($mapped[$name])) throw new InvalidArgumentException('Duplicate table metadata.');
            $mapped[$name] = ['name' => $name, 'columns' => [], 'constraints' => [], 'triggers' => []];
        }
        foreach ($columns as $row) {
            $table = self::identifier($row->table_name ?? null);
            $column = self::identifier($row->column_name ?? null);
            if (!isset($mapped[$table])) throw new InvalidArgumentException('Column references an unknown table.');
            if (isset($mapped[$table]['columns'][$column])) throw new InvalidArgumentException('Duplicate column metadata.');
            $type = self::printable($row->data_type ?? null, 255);
            $ordinal = (int) ($row->ordinal ?? 0);
            if ($ordinal < 1 || $ordinal > 10000) throw new InvalidArgumentException('Column order invalid.');
            $mapped[$table]['columns'][$column] = [
                'name' => $column,
                'ordinal' => $ordinal,
                'data_type' => $type,
                'not_null' => self::flag($row->not_null ?? null),
                'has_default' => self::flag($row->has_default ?? null),
                'identity' => self::flag($row->is_identity ?? null),
                'generated' => self::flag($row->is_generated ?? null),
            ];
        }
        foreach ($constraints as $row) {
            $table = self::identifier($row->table_name ?? null);
            if (!isset($mapped[$table])) throw new InvalidArgumentException('Constraint references unknown table.');
            $name = self::identifier($row->constraint_name ?? null);
            $type = (string) ($row->constraint_type ?? '');
            if (!in_array($type, ['p', 'f', 'u', 'c', 'x'], true)) {
                throw new InvalidArgumentException('Unsupported constraint kind.');
            }
            $mapped[$table]['constraints'][] = [
                'name' => $name, 'kind' => $type,
                'definition' => self::printable($row->definition ?? null, 8000),
            ];
        }
        foreach ($triggers as $row) {
            $table = self::identifier($row->table_name ?? null);
            if (!isset($mapped[$table])) throw new InvalidArgumentException('Trigger references unknown table.');
            $mapped[$table]['triggers'][] = [
                'name' => self::identifier($row->trigger_name ?? null),
                'definition' => self::printable($row->definition ?? null, 8000),
            ];
        }
        $enumMap = [];
        foreach ($enums as $row) {
            $name = self::identifier($row->enum_name ?? null);
            $enumMap[$name][] = self::printable($row->enum_label ?? null, 255);
        }
        ksort($mapped, SORT_STRING);
        foreach ($mapped as &$table) {
            uasort($table['columns'], static fn (array $a, array $b): int => $a['ordinal'] <=> $b['ordinal']);
            $table['columns'] = array_values($table['columns']);
            usort($table['constraints'], static fn (array $a,array $b): int => strcmp($a['name'], $b['name']));
            usort($table['triggers'], static fn (array $a,array $b): int => strcmp($a['name'], $b['name']));
        }
        unset($table);
        ksort($enumMap, SORT_STRING);
        $enumList = [];
        foreach ($enumMap as $name => $labels) $enumList[] = ['name' => $name, 'labels' => $labels];
        $tableList = array_values($mapped);
        $columnCount = $required = $fks = $checks = $unique = $triggerCount = 0;
        foreach ($tableList as $t) {
            $columnCount += count($t['columns']);
            $triggerCount += count($t['triggers']);
            foreach ($t['columns'] as $c) {
                if ($c['not_null'] && !$c['has_default'] && !$c['identity'] && !$c['generated']) ++$required;
            }
            foreach ($t['constraints'] as $c) {
                if ($c['kind'] === 'f') ++$fks;
                if ($c['kind'] === 'c') ++$checks;
                if ($c['kind'] === 'u') ++$unique;
            }
        }
        $structure = ['format' => self::FORMAT, 'schema' => 'public', 'tables' => $tableList, 'enums' => $enumList];
        $hash = hash('sha256', json_encode($structure, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        return $structure + [
            'summary' => [
                'tables' => count($tableList), 'columns' => $columnCount,
                'required_without_default' => $required,
                'foreign_keys' => $fks, 'checks' => $checks,
                'unique_constraints' => $unique, 'triggers' => $triggerCount,
                'enum_types' => count($enumList),
            ],
            'fingerprint_sha256' => $hash,
        ];
    }

    /** @param array<string,mixed> $snapshot @param list<string> $expected */
    public static function missingTables(array $snapshot, array $expected): array
    {
        $present = [];
        foreach ($snapshot['tables'] ?? [] as $table) {
            if (isset($table['name'])) $present[(string) $table['name']] = true;
        }
        return array_values(array_filter($expected, static fn (string $name): bool => !isset($present[$name])));
    }

    private static function identifier(mixed $value): string
    {
        if (!is_string($value) || !preg_match('/\A[A-Za-z_][A-Za-z0-9_$]{0,127}\z/D', $value)) {
            throw new InvalidArgumentException('Unexpected schema identifier.');
        }
        return $value;
    }

    private static function printable(mixed $value, int $limit): string
    {
        if (!is_string($value) || $value === '' || strlen($value) > $limit
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $value)) {
            throw new InvalidArgumentException('Unsafe or oversized catalog text.');
        }
        return $value;
    }

    private static function flag(mixed $value): bool
    {
        if ($value === true || $value === 1 || $value === '1' || $value === 't') return true;
        if ($value === false || $value === 0 || $value === '0' || $value === 'f') return false;
        throw new InvalidArgumentException('Unexpected catalog boolean.');
    }
}
