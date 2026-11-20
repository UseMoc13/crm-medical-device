<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CrmPrivateFileAccessSourceTest extends TestCase
{
    public function test_direct_download_and_zip_use_scoped_file_validation(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/WorkspaceTaskSubmissionController.php');
        self::assertStringContainsString('CrmPrivateFilePath::submission($storedPath, $taskId, $assignmentId)', $source);
        self::assertStringContainsString('CrmPrivateFilePath::resolve(', $source);
        self::assertStringContainsString('if ($path === null) { $missing++; continue; }', $source);
        self::assertStringContainsString('if ($missing > 0)', $source);
        self::assertStringContainsString('$this->owner($todo)', $source);
        self::assertStringContainsString('$this->scopedFile($todo,$file)', $source);
        self::assertStringNotContainsString("str_starts_with(\$file->stored_path, 'crm-task-submissions/')", $source);
    }

    public function test_delete_validates_submission_path_before_deleting_row(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/WorkspaceTaskSubmissionController.php');
        $validation = strpos($source, 'CrmPrivateFilePath::submission(');
        $delete = strpos($source, '$document->delete();');
        self::assertNotFalse($validation);
        self::assertNotFalse($delete);
        self::assertLessThan($delete, $validation);
    }

    public function test_contract_download_cannot_read_arbitrary_private_file(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ContractController.php');
        self::assertStringContainsString('CrmPrivateFilePath::contract($storedPath)', $source);
        self::assertStringContainsString("CrmPrivateFilePath::resolve(\$disk->path(''), \$storedPath, 'contracts')", $source);
        self::assertStringContainsString("'Cache-Control' => 'private, no-store'", $source);
    }
}
