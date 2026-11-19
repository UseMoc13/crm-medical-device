<?php

namespace Tests\Unit;

use App\Services\CrmDashboardAnnouncements;
use PHPUnit\Framework\TestCase;

final class CrmDashboardAnnouncementSourceTest extends TestCase
{
    public function test_dashboard_has_no_lazy_publishing_and_query_is_read_only(): void
    {
        $root = dirname(__DIR__, 2);
        $view = file_get_contents($root.'/resources/views/dashboard/index.blade.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/DashboardController.php');
        $service = file_get_contents($root.'/app/Services/CrmDashboardAnnouncements.php');
        self::assertStringNotContainsString('publishDueScheduled(', $view);
        self::assertStringNotContainsString('Announcement::publishDueScheduled(', $controller);
        self::assertStringNotContainsString('publishDueScheduled(', $service);
        self::assertStringContainsString('->published()', $service);
        self::assertStringContainsString('->limit(6)', $service);
        self::assertStringContainsString("'latestAnnouncements' =>", $controller);
        self::assertTrue(class_exists(CrmDashboardAnnouncements::class));
        self::assertStringNotContainsString('DB::table(', $service);
    }

    public function test_announcement_publishing_is_separate_and_report_class_case_is_correct(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertFileExists($root.'/app/Http/Controllers/ReportController.php');
        self::assertFileDoesNotExist($root.'/app/Http/Controllers/Reportcontroller.php');
        $report = file_get_contents($root.'/app/Http/Controllers/ReportController.php');
        self::assertMatchesRegularExpression('/class\s+ReportController\b/', $report);
        $schedule = file_get_contents($root.'/routes/console.php');
        $command = file_get_contents($root.'/app/Console/Commands/PublishDueCrmAnnouncements.php');
        self::assertStringContainsString("Schedule::command('crm:announcements:publish-due')", $schedule);
        self::assertStringContainsString('Announcement::publishDueScheduled()', $command);
        self::assertStringNotContainsString('->truncate(', $command);
    }
}
