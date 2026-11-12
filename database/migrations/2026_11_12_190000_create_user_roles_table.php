<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A1 Expand migration: preserves users.role_id and existing CRM integrations.
     * PostgreSQL 18 / Laravel 12, UUID PKs throughout the CRM database.
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('user_role_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->uuid('role_id');
            $table->boolean('is_primary')->default(false);
            $table->uuid('assigned_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id', 'user_roles_user_fk')
                ->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('role_id', 'user_roles_role_fk')
                ->references('role_id')->on('roles')->restrictOnDelete();
            $table->foreign('assigned_by', 'user_roles_assigned_by_fk')
                ->references('user_id')->on('users')->nullOnDelete();

            $table->unique(['user_id', 'role_id'], 'user_roles_user_role_unique');
            $table->index('role_id', 'user_roles_role_idx');
            $table->index('assigned_by', 'user_roles_assigned_by_idx');
        });

        // Allow only one PRIMARY assignment per user, not merely one unique role.
        // A user having at least one primary role is enforced by the future
        // UserRoleAssignmentService; backfilled legacy users satisfy it now.
        DB::statement('CREATE UNIQUE INDEX user_roles_one_primary_per_user ON user_roles (user_id) WHERE is_primary = true');

        // Snapshot existing single-role assignment into the new pivot table.
        // No role or user is invented, and no legacy column is changed.
        DB::statement(<<<'SQL'
            INSERT INTO user_roles (user_role_id, user_id, role_id, is_primary, assigned_by, created_at)
            SELECT gen_random_uuid(), u.user_id, u.role_id, true, NULL, CURRENT_TIMESTAMP
            FROM users AS u
            WHERE u.role_id IS NOT NULL
            ON CONFLICT (user_id, role_id) DO NOTHING
        SQL);
    }

    public function down(): void
    {
        // Removes ONLY the new pivot and its indexes/constraints.
        // Note: additional role assignments in user_roles are lost on rollback;
        // export/backup them before running migrate:rollback in later phases.
        Schema::dropIfExists('user_roles');
    }
};
