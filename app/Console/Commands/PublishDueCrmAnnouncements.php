<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;

/** Business mutation deliberately restricted to explicit scheduled/CLI execution. */
final class PublishDueCrmAnnouncements extends Command
{
    protected $signature = 'crm:announcements:publish-due';
    protected $description = 'Publish announcements whose scheduled_at is due';

    public function handle(): int
    {
        $count = Announcement::publishDueScheduled();
        $this->components->info("Due announcements published: {$count}");
        return self::SUCCESS;
    }
}
