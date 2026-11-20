<?php

namespace App\Support;

/**
 * A7.7.4.2: allowlisted, folder-scoped paths for private CRM downloads.
 * This validates a stored path; it does NOT grant access to a business record.
 */
final class CrmPrivateFilePath
{
    private const UUID = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

    public static function submission(string $storedPath, string $taskId, string $assignmentId): bool
    {
        if (!self::isUuid($taskId) || !self::isUuid($assignmentId)) {
            return false;
        }
        $prefix = 'crm-task-submissions/' . $taskId . '/' . $assignmentId . '/';
        if (!str_starts_with($storedPath, $prefix)) {
            return false;
        }
        $filename = substr($storedPath, strlen($prefix));
        return preg_match('/\A' . self::UUID . '\.(?:pdf|doc|docx|xls|xlsx|png|jpg|jpeg|txt)\z/iD', $filename) === 1;
    }

    public static function contract(string $storedPath): bool
    {
        // Contracts are saved by UploadedFile::store('contracts', 'local').
        // No nested folders or path separators are valid for that workflow.
        return preg_match('/\Acontracts\/[a-z0-9][a-z0-9_-]{0,160}\.pdf\z/iD', $storedPath) === 1;
    }

    /**
     * Resolve under a specific private folder, rejecting traversal and links
     * that escape the folder. Null means deny/not found.
     */
    public static function resolve(string $diskRoot, string $storedPath, string $relativeScope): ?string
    {
        if ($diskRoot === '' || $storedPath === '' || $relativeScope === '' ||
            str_contains($storedPath, "\0") || str_contains($storedPath, '\\') ||
            str_contains($relativeScope, "\0") || str_contains($relativeScope, '\\') ||
            str_contains($storedPath, '..') || str_contains($relativeScope, '..') ||
            str_starts_with($storedPath, '/') || str_starts_with($relativeScope, '/') ||
            !str_starts_with($storedPath, rtrim($relativeScope, '/') . '/')) {
            return null;
        }

        $root = realpath($diskRoot);
        if ($root === false || !is_dir($root)) {
            return null;
        }
        $relative = str_replace('/', DIRECTORY_SEPARATOR, $storedPath);
        $folder = str_replace('/', DIRECTORY_SEPARATOR, trim($relativeScope, '/'));
        $resolved = realpath($root . DIRECTORY_SEPARATOR . $relative);
        if ($resolved === false || !is_file($resolved)) {
            return null;
        }
        $expectedPrefix = rtrim($root, '/\\') . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        // Windows paths are case-insensitive; POSIX paths are not.
        $inScope = DIRECTORY_SEPARATOR === '\\'
            ? str_starts_with(strtolower($resolved), strtolower($expectedPrefix))
            : str_starts_with($resolved, $expectedPrefix);
        return $inScope ? $resolved : null;
    }

    private static function isUuid(string $value): bool
    {
        return preg_match('/\A' . self::UUID . '\z/iD', $value) === 1;
    }
}
