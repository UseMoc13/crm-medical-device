<?php

namespace App\Providers;

use App\Support\CrmCreatorAttribution;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        CrmCreatorAttribution::register();
        // A7.4.11.1: notifications share the same DB transaction as their source action.
        \App\Models\WorkspaceTaskAssignment::observe(\App\Observers\WorkspaceTaskAssignmentNotificationObserver::class);
        \App\Models\WorkspaceCollaborationMember::observe(\App\Observers\WorkspaceCollaborationMemberNotificationObserver::class);
        \App\Models\WorkspaceTaskReview::observe(\App\Observers\WorkspaceTaskReviewNotificationObserver::class);
    }
}
