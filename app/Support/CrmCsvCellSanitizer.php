<?php

namespace App\Support;

/**
 * A7.7.4.1: Prevent spreadsheet formula execution in untrusted TEXT columns.
 * Numeric database columns, even when PDO returns them as strings, must not
 * be modified. Callers MUST explicitly list the text column indexes.
 */
final class CrmCsvCellSanitizer
{
    /**
     * @param array<int|string, mixed> $fields
     * @param list<int> $textColumns Only indexes containing untrusted CRM text.
     * @return array<int|string, mixed>
     */
    public static function row(array $fields, array $textColumns): array
    {
        foreach ($textColumns as $index) {
            if (!array_key_exists($index, $fields)) {
                continue;
            }
            $value = $fields[$index];
            if (!is_string($value) || $value === '') {
                continue;
            }

            // Unicode separators, zero-width marks, and control characters
            // must not conceal the first formula character. Keep source data
            // intact; prepend apostrophe ONLY in the CSV output.
            $match = preg_match('/^[\p{Z}\p{C}\s]*[=+\-@]/u', $value);
            if ($match === 1 || $match === false) {
                $fields[$index] = "'" . $value;
            }
        }

        return $fields;
    }
}
