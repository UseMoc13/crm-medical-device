<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Metadata only: a reporting hierarchy NEVER grants inherited RBAC permissions.
        Schema::table('roles', function (Blueprint $table): void {
            $table->uuid('parent_role_id')->nullable();
            $table->foreign('parent_role_id', 'roles_parent_role_fk')
                ->references('role_id')->on('roles')->nullOnDelete();
        });

        // One direct manager per employee in phase 1; distinct from role membership.
        Schema::create('user_reporting_lines', function (Blueprint $table): void {
            $table->uuid('reporting_line_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id')->unique();
            $table->uuid('manager_user_id');
            $table->uuid('updated_by_user_id')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('manager_user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by_user_id')->references('user_id')->on('users')->nullOnDelete();
            $table->index('manager_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reporting_lines');
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropForeign('roles_parent_role_fk');
            $table->dropColumn('parent_role_id');
        });
    }
};
