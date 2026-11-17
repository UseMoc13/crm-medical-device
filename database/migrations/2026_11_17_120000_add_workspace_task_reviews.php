<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Non-destructive expansion: previous completed submissions must NOT suddenly
        // prevent a task from closing. They are explicitly marked legacy_accepted.
        Schema::table('workspace_task_assignments', function (Blueprint $table) {
            $table->string('review_status', 24)->default('not_submitted');
            $table->uuid('reviewed_by_user_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->foreign('reviewed_by_user_id', 'wta_reviewed_by_fk')
                ->references('user_id')->on('users')->nullOnDelete();
            $table->index(['task_id', 'review_status'], 'wta_task_review_idx');
        });

        // Old completed assignments have no human-review audit event, so do not
        // label them approved. Only new submissions enter pending_review.
        DB::table('workspace_task_assignments')->where('status', 'completed')
            ->update(['review_status' => 'legacy_accepted']);

        Schema::create('workspace_task_reviews', function (Blueprint $table) {
            $table->uuid('review_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('task_id');
            $table->uuid('assignment_id');
            $table->uuid('actor_user_id');
            $table->string('decision', 24); // approved | revision_requested
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('task_id')->references('task_id')->on('workspace_tasks')->cascadeOnDelete();
            $table->foreign('assignment_id')->references('assignment_id')
                ->on('workspace_task_assignments')->cascadeOnDelete();
            $table->foreign('actor_user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->index(['task_id', 'created_at'], 'wtr_task_history_idx');
            $table->index(['assignment_id', 'created_at'], 'wtr_assignment_history_idx');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE workspace_task_assignments ADD CONSTRAINT wta_review_status_check CHECK (review_status IN ('not_submitted','pending_review','approved','revision_requested','legacy_accepted'))");
            DB::statement("ALTER TABLE workspace_task_reviews ADD CONSTRAINT wtr_decision_check CHECK (decision IN ('approved','revision_requested'))");
        }
    }

    public function down(): void
    {
        // Downgrades discard only review metadata, never original tasks/files.
        Schema::dropIfExists('workspace_task_reviews');
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE workspace_task_assignments DROP CONSTRAINT IF EXISTS wta_review_status_check');
        }
        Schema::table('workspace_task_assignments', function (Blueprint $table) {
            $table->dropForeign('wta_reviewed_by_fk');
            $table->dropIndex('wta_task_review_idx');
            $table->dropColumn(['review_status','reviewed_by_user_id','reviewed_at','review_note']);
        });
    }
};
