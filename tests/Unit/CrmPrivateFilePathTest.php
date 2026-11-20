<?php

namespace Tests\Unit;

use App\Support\CrmPrivateFilePath;
use PHPUnit\Framework\TestCase;

/** Pure file-system fixtures; no Laravel DB, no HTTP requests, no writes to CRM storage. */
final class CrmPrivateFilePathTest extends TestCase
{
    private const TASK = '11111111-1111-4111-8111-111111111111';
    private const ASSIGNMENT = '22222222-2222-4222-8222-222222222222';
    private const FILE = '33333333-3333-4333-8333-333333333333.pdf';

    public function test_legitimate_task_submission_path_is_accepted(): void
    {
        self::assertTrue(CrmPrivateFilePath::submission(
            'crm-task-submissions/'.self::TASK.'/'.self::ASSIGNMENT.'/'.self::FILE,
            self::TASK, self::ASSIGNMENT
        ));
    }

    public function test_foreign_task_and_assignment_are_denied(): void
    {
        $path = 'crm-task-submissions/'.self::TASK.'/'.self::ASSIGNMENT.'/'.self::FILE;
        self::assertFalse(CrmPrivateFilePath::submission($path, '44444444-4444-4444-8444-444444444444', self::ASSIGNMENT));
        self::assertFalse(CrmPrivateFilePath::submission($path, self::TASK, '55555555-5555-4555-8555-555555555555'));
    }

    public function test_task_path_traversal_and_unexpected_extensions_are_denied(): void
    {
        foreach ([
            'crm-task-submissions/../../contracts/another.pdf',
            'crm-task-submissions/'.self::TASK.'/'.self::ASSIGNMENT.'/../'.self::FILE,
            'crm-task-submissions/'.self::TASK.'/'.self::ASSIGNMENT.'/'.self::FILE.'/other',
            'crm-task-submissions/'.self::TASK.'/'.self::ASSIGNMENT.'/'.self::FILE.'.php',
            'crm-task-submissions/'.self::TASK.'/'.self::ASSIGNMENT.'/'.self::FILE.'\\evil',
        ] as $path) {
            self::assertFalse(CrmPrivateFilePath::submission($path, self::TASK, self::ASSIGNMENT));
        }
    }

    public function test_contract_download_must_remain_within_contracts_directory(): void
    {
        self::assertTrue(CrmPrivateFilePath::contract('contracts/AbC123_-xyz.pdf'));
        foreach (['contracts/../../invoices/secret.pdf', 'contracts/sub/file.pdf',
            'contracts/file.php', '/contracts/file.pdf', 'other/file.pdf',
            'contracts\\file.pdf'] as $bad) {
            self::assertFalse(CrmPrivateFilePath::contract($bad));
        }
    }

    public function test_realpath_rejects_other_directory_and_missing_file(): void
    {
        $base = sys_get_temp_dir().'/crm-private-path-test-'.bin2hex(random_bytes(4));
        mkdir($base.'/contracts', 0700, true);
        mkdir($base.'/other', 0700, true);
        file_put_contents($base.'/contracts/allowed.pdf', 'test content');
        file_put_contents($base.'/other/secret.pdf', 'test content');
        try {
            self::assertSame(realpath($base.'/contracts/allowed.pdf'),
                CrmPrivateFilePath::resolve($base, 'contracts/allowed.pdf', 'contracts'));
            self::assertNull(CrmPrivateFilePath::resolve($base, 'other/secret.pdf', 'contracts'));
            self::assertNull(CrmPrivateFilePath::resolve($base, 'contracts/missing.pdf', 'contracts'));
            self::assertNull(CrmPrivateFilePath::resolve($base, 'contracts/../other/secret.pdf', 'contracts'));
        } finally {
            unlink($base.'/contracts/allowed.pdf');
            unlink($base.'/other/secret.pdf');
            rmdir($base.'/contracts');
            rmdir($base.'/other');
            rmdir($base);
        }
    }

    public function test_link_that_escapes_folder_is_rejected_when_supported(): void
    {
        $base = sys_get_temp_dir().'/crm-private-link-test-'.bin2hex(random_bytes(4));
        mkdir($base.'/contracts', 0700, true);
        mkdir($base.'/other', 0700, true);
        file_put_contents($base.'/other/private.pdf', 'test content');
        $link = $base.'/contracts/link.pdf';
        $created = function_exists('symlink') && @symlink($base.'/other/private.pdf', $link);
        try {
            if (!$created) {
                $this->markTestSkipped('Symlinks unavailable without additional OS privileges.');
            }
            self::assertNull(CrmPrivateFilePath::resolve($base, 'contracts/link.pdf', 'contracts'));
        } finally {
            if ($created && is_link($link)) unlink($link);
            unlink($base.'/other/private.pdf');
            rmdir($base.'/contracts');
            rmdir($base.'/other');
            rmdir($base);
        }
    }
}
