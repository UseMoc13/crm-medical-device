<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Additive changes: preserve existing Activity / To-Do rows.
        Schema::table('activities', function (Blueprint $table) {
            $table->timestamp('ends_at')->nullable();
        });
        Schema::table('workspace_tasks', function (Blueprint $table) {
            $table->timestamp('starts_at')->nullable();
        });

        Schema::create('workspace_task_assignments', function (Blueprint $table) {
            $table->uuid('assignment_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('task_id');
            $table->uuid('assigned_user_id');
            $table->boolean('completion_required')->default(true); // mandatory vs optional recipient
            $table->boolean('submission_required')->default(false); // file requirement is independent
            $table->string('status', 20)->default('assigned'); // assigned | completed
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('task_id')->references('task_id')->on('workspace_tasks')->cascadeOnDelete();
            $table->foreign('assigned_user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->unique(['task_id', 'assigned_user_id']);
            $table->index(['assigned_user_id', 'status']);
        });

        Schema::create('workspace_task_submission_files', function (Blueprint $table) {
            $table->uuid('file_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('assignment_id');
            $table->uuid('uploaded_by_user_id');
            $table->string('original_name', 255);
            $table->string('stored_path', 500);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
            $table->foreign('assignment_id')->references('assignment_id')->on('workspace_task_assignments')->cascadeOnDelete();
            $table->foreign('uploaded_by_user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->index(['assignment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        // Rollback does not delete stored private files; back up before rolling back.
        Schema::dropIfExists('workspace_task_submission_files');
        Schema::dropIfExists('workspace_task_assignments');
        Schema::table('workspace_tasks', fn (Blueprint $table) => $table->dropColumn('starts_at'));
        Schema::table('activities', fn (Blueprint $table) => $table->dropColumn('ends_at'));
    }
};
