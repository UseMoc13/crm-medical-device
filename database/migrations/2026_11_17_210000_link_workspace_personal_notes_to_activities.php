<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workspace_personal_notes', function (Blueprint $table): void {
            $table->uuid('activity_id')->nullable();
            $table->foreign('activity_id', 'workspace_notes_activity_fk')
                ->references('activity_id')->on('activities')->nullOnDelete();
            $table->index(['owner_user_id', 'activity_id'], 'workspace_notes_owner_activity_idx');
            $table->index(['owner_user_id', 'calendar_event_id'], 'workspace_notes_owner_calendar_idx');
        });
    }

    public function down(): void
    {
        Schema::table('workspace_personal_notes', function (Blueprint $table): void {
            $table->dropForeign('workspace_notes_activity_fk');
            $table->dropIndex('workspace_notes_owner_activity_idx');
            $table->dropIndex('workspace_notes_owner_calendar_idx');
            $table->dropColumn('activity_id');
        });
    }
};
