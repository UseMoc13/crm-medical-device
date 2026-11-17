<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workspace_notification_preferences', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->boolean('assignment_enabled')->default(true);
            $table->boolean('collaboration_enabled')->default(true);
            $table->boolean('submission_enabled')->default(true);
            $table->boolean('review_enabled')->default(true);
            $table->boolean('reminder_note_enabled')->default(true);
            $table->boolean('reminder_calendar_enabled')->default(true);
            $table->boolean('reminder_activity_enabled')->default(true);
            $table->boolean('reminder_todo_enabled')->default(true);
            $table->unsignedSmallInteger('reminder_lead_minutes')->default(1440);
            $table->timestamps();
            $table->foreign('user_id', 'wsnp_user_fk')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_notification_preferences');
    }
};
