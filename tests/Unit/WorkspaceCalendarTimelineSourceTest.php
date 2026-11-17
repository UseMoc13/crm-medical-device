<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Source-level regression guard: the unexpected literal \\n disabled all Calendar JS handlers. */
final class WorkspaceCalendarTimelineSourceTest extends TestCase
{
    private function source(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 2) . '/resources/views/calendar/index.blade.php');
    }

    public function test_literal_newline_escape_does_not_break_daily_dialog_script(): void
    {
        $source = $this->source();
        $this->assertStringNotContainsString(';\\n        noteAdd.href', $source);
        $this->assertMatchesRegularExpression('/add\.href\s*=\s*`[^`]+`;\R\s*noteAdd\.href\s*=/', $source);
    }

    public function test_day_and_event_selection_open_daily_timeline(): void
    {
        $source = $this->source();
        foreach (['id="calendarDayBackdrop"', 'id="calendarDayTimeline"',
            "document.querySelectorAll('[data-calendar-open]')",
            "document.querySelectorAll('[data-calendar-date]')", 'backdrop.hidden = false;',
            'for (const item of list)', 'timeline.append(entry);'] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }
    }
}
