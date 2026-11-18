<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkspaceNotification;
use App\Services\CrmStatusNotificationAccess;
use App\Services\WorkspaceNotificationCenter;
use Tests\TestCase;

/** Does not write business tables, notifications, or user preferences. */
final class CrmStatusNotificationPrivacyTest extends TestCase
{
    public function test_unknown_target_and_inactive_recipient_fail_closed(): void
    {
        $user = new User(['status' => 'inactive']);
        $user->setAttribute('user_id', '9f2f1d91-a5cc-4e7f-a025-f5e59f89b28d');
        $this->assertFalse(app(CrmStatusNotificationAccess::class)->canOpen($user, 'unknown', 'e5a8a185-6352-4287-83cc-8661d2b7be40'));
        $this->assertFalse(app(CrmStatusNotificationAccess::class)->canOpen($user, 'delivery', 'e5a8a185-6352-4287-83cc-8661d2b7be40'));
    }

    public function test_notification_open_never_grants_access_to_another_user(): void
    {
        $recipient = new User(['status' => 'active']);
        $recipient->setAttribute('user_id', '9f2f1d91-a5cc-4e7f-a025-f5e59f89b28d');
        $notice = new WorkspaceNotification([
            'recipient_user_id' => '09e16857-dee0-4b7d-9754-9dd62d0f45fa',
            'target_type' => 'delivery',
            'target_id' => 'e5a8a185-6352-4287-83cc-8661d2b7be40',
        ]);
        $this->assertFalse(app(WorkspaceNotificationCenter::class)->canOpen($recipient, $notice));
    }

    public function test_pending_migration_does_not_send_any_status_notification(): void
    {
        // Strong source-level policy: the publisher requires the new column
        // before it reads stakeholders or creates any workspace inbox row.
        $source = file_get_contents(app_path('Services/CrmStatusNotificationPublisher.php'));
        $this->assertStringContainsString("Schema::hasColumn('workspace_notification_preferences', 'crm_status_enabled')", $source);
        $this->assertStringContainsString('WorkspaceNotificationPreferences::class', $source);
    }
}
