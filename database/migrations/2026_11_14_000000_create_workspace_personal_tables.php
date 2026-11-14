<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Additive only: existing CRM activities and notifications are untouched.
        Schema::create('workspace_tasks', function (Blueprint $table) {
            $table->uuid('task_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('owner_user_id');
            $table->uuid('assigned_user_id')->nullable();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('visibility', 16)->default('personal'); // personal | work
            $table->string('status', 20)->default('todo'); // todo | in_progress | completed | cancelled
            $table->string('priority', 12)->default('medium'); // low | medium | high | urgent
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->uuid('lead_id')->nullable();
            $table->uuid('opportunity_id')->nullable();
            $table->uuid('activity_id')->nullable();
            $table->timestamps();

            $table->foreign('owner_user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->foreign('assigned_user_id')->references('user_id')->on('users')->nullOnDelete();
            $table->foreign('customer_id')->references('customer_id')->on('customers')->nullOnDelete();
            $table->foreign('lead_id')->references('lead_id')->on('leads')->nullOnDelete();
            $table->foreign('opportunity_id')->references('opportunity_id')->on('opportunities')->nullOnDelete();
            $table->foreign('activity_id')->references('activity_id')->on('activities')->nullOnDelete();
            $table->index(['owner_user_id', 'visibility', 'status']);
            $table->index(['assigned_user_id', 'status', 'due_at']);
            $table->index('due_at');
        });

        Schema::create('workspace_calendar_events', function (Blueprint $table) {
            $table->uuid('calendar_event_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('owner_user_id');
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('visibility', 16)->default('personal'); // personal only in first version
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->string('location', 255)->nullable();
            $table->timestamps();

            $table->foreign('owner_user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->index(['owner_user_id', 'starts_at']);
        });

        Schema::create('workspace_personal_notes', function (Blueprint $table) {
            $table->uuid('note_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('owner_user_id');
            $table->string('title', 150);
            $table->text('body')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('remind_at')->nullable();
            $table->uuid('task_id')->nullable();
            $table->uuid('calendar_event_id')->nullable();
            $table->timestamps();

            $table->foreign('owner_user_id')->references('user_id')->on('users')->restrictOnDelete();
            $table->foreign('task_id')->references('task_id')->on('workspace_tasks')->nullOnDelete();
            $table->foreign('calendar_event_id')->references('calendar_event_id')->on('workspace_calendar_events')->nullOnDelete();
            $table->index(['owner_user_id', 'is_pinned', 'updated_at']);
            $table->index(['owner_user_id', 'remind_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_personal_notes');
        Schema::dropIfExists('workspace_calendar_events');
        Schema::dropIfExists('workspace_tasks');
    }
};
