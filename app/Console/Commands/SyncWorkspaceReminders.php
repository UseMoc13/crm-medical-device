<?php

namespace App\Console\Commands;

use App\Services\WorkspaceReminderSync;
use Illuminate\Console\Command;

final class SyncWorkspaceReminders extends Command
{
    protected $signature = 'workspace:reminders:sync {--dry-run : Count eligible recipients without writing notifications}';
    protected $description = 'Generate idempotent CRM workspace reminders for the next 24 hours';

    public function handle(WorkspaceReminderSync $sync): int
    {
        $results = $sync->run((bool) $this->option('dry-run'));
        foreach ($results as $kind => $count) $this->line("{$kind}: {$count}");
        $this->info($this->option('dry-run') ? 'Dry run completed; no notifications written.' : 'Workspace reminders synchronized.');
        return self::SUCCESS;
    }
}
