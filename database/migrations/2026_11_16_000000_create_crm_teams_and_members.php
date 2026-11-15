<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Organizational metadata only: membership never grants CRM record access.
        Schema::create('crm_teams', function (Blueprint $table): void {
            $table->uuid('team_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('team_code', 32)->unique();
            $table->string('team_name', 150);
            $table->string('team_type', 16); // permanent | project
            $table->string('status', 16)->default('active'); // active | archived
            $table->uuid('branch_id')->nullable(); // Required for permanent teams.
            $table->uuid('leader_user_id')->nullable();
            $table->uuid('created_by_user_id')->nullable();
            $table->text('description')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('branch_id')->references('branch_id')->on('branches')->nullOnDelete();
            $table->foreign('leader_user_id')->references('user_id')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('user_id')->on('users')->nullOnDelete();
            $table->index(['team_type', 'status']);
            $table->index(['branch_id', 'status']);
        });

        Schema::create('crm_team_members', function (Blueprint $table): void {
            $table->uuid('team_member_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('team_id');
            $table->uuid('user_id');
            $table->string('member_role', 16)->default('member'); // member | coordinator
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable(); // Preserve membership history.
            $table->timestamps();
            $table->foreign('team_id')->references('team_id')->on('crm_teams')->cascadeOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->unique(['team_id', 'user_id'], 'crm_team_member_user_unique');
            $table->index(['user_id', 'left_at']);
            $table->index(['team_id', 'left_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_team_members');
        Schema::dropIfExists('crm_teams');
    }
};
