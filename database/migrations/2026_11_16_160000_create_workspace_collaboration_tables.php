<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Two nullable foreign keys deliberately avoid a polymorphic orphan problem.
        // A record must belong to exactly one Activity or Work TO-DO.
        Schema::create('workspace_collaboration_members', function (Blueprint $table) {
            $table->uuid('member_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('activity_id')->nullable();
            $table->uuid('task_id')->nullable();
            $table->uuid('user_id');
            $table->string('access_role', 20); // viewer | contributor
            $table->timestamps();
            $table->foreign('activity_id')->references('activity_id')->on('activities')->cascadeOnDelete();
            $table->foreign('task_id')->references('task_id')->on('workspace_tasks')->cascadeOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->unique(['activity_id', 'user_id']);
            $table->unique(['task_id', 'user_id']);
            $table->index(['user_id', 'access_role']);
        });
        DB::statement("ALTER TABLE workspace_collaboration_members ADD CONSTRAINT wcm_one_resource CHECK ((activity_id IS NOT NULL) <> (task_id IS NOT NULL))");
        DB::statement("ALTER TABLE workspace_collaboration_members ADD CONSTRAINT wcm_valid_role CHECK (access_role IN ('viewer','contributor'))");

        Schema::create('workspace_collaboration_entries', function (Blueprint $table) {
            $table->uuid('entry_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('activity_id')->nullable();
            $table->uuid('task_id')->nullable();
            $table->uuid('author_user_id');
            $table->string('entry_type', 20); // comment | progress
            $table->text('body');
            $table->timestamps();
            $table->foreign('activity_id')->references('activity_id')->on('activities')->cascadeOnDelete();
            $table->foreign('task_id')->references('task_id')->on('workspace_tasks')->cascadeOnDelete();
            $table->foreign('author_user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->index(['activity_id', 'created_at']);
            $table->index(['task_id', 'created_at']);
        });
        DB::statement("ALTER TABLE workspace_collaboration_entries ADD CONSTRAINT wce_one_resource CHECK ((activity_id IS NOT NULL) <> (task_id IS NOT NULL))");
        DB::statement("ALTER TABLE workspace_collaboration_entries ADD CONSTRAINT wce_valid_type CHECK (entry_type IN ('comment','progress'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_collaboration_entries');
        Schema::dropIfExists('workspace_collaboration_members');
    }
};
