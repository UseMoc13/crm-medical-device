<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\WorkspaceTaskAssignment;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

/** No DB, migrations, sessions, HTTP requests or test fixture resets. */
final class WorkspaceTaskAssignmentReviewFillableTest extends TestCase
{
    public function test_new_submission_keeps_pending_review_attributes(): void
    {
        $assignment = new WorkspaceTaskAssignment();
        $assignment->fill([
            'status' => 'completed',
            'review_status' => 'pending_review',
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ]);
        self::assertSame('completed', $assignment->status);
        self::assertSame('pending_review', $assignment->review_status);
        self::assertArrayHasKey('review_status', $assignment->getAttributes());
    }

    public function test_creator_review_metadata_is_preserved(): void
    {
        $assignment = new WorkspaceTaskAssignment();
        $assignment->fill([
            'review_status' => 'approved',
            'reviewed_by_user_id' => '11111111-1111-4111-8111-111111111111',
            'reviewed_at' => '2026-11-17 09:00:00',
            'review_note' => 'Device report accepted',
        ]);
        self::assertSame('approved', $assignment->review_status);
        self::assertSame('11111111-1111-4111-8111-111111111111', $assignment->reviewed_by_user_id);
        self::assertSame('Device report accepted', $assignment->review_note);
        self::assertInstanceOf(DateTimeInterface::class, $assignment->reviewed_at);
    }

    public function test_legacy_status_and_revision_attributes_are_assignable(): void
    {
        $assignment = new WorkspaceTaskAssignment();
        $assignment->fill(['status' => 'completed', 'review_status' => 'legacy_accepted']);
        self::assertSame('legacy_accepted', $assignment->review_status);
        $assignment->fill(['status' => 'assigned', 'review_status' => 'revision_requested', 'review_note' => 'Please update']);
        self::assertSame('revision_requested', $assignment->review_status);
        self::assertSame('Please update', $assignment->review_note);
    }
}
