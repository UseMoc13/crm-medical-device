<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('workspace_notification_preferences')
            && !Schema::hasColumn('workspace_notification_preferences', 'crm_status_enabled')) {
            Schema::table('workspace_notification_preferences', static function (Blueprint $table): void {
                // Opt-in by default: no surprise cross-module notifications.
                $table->boolean('crm_status_enabled')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workspace_notification_preferences')
            && Schema::hasColumn('workspace_notification_preferences', 'crm_status_enabled')) {
            Schema::table('workspace_notification_preferences', static function (Blueprint $table): void {
                $table->dropColumn('crm_status_enabled');
            });
        }
    }
};
