<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_system')->default(false);
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            // NULL = scope has not been configured; fail closed for scoped reads.
            $table->string('data_scope', 20)->nullable();
        });

        DB::statement("ALTER TABLE role_permissions ADD CONSTRAINT role_permissions_data_scope_check CHECK (data_scope IS NULL OR data_scope IN ('all', 'branch', 'team', 'assigned', 'own'))");
        DB::statement('CREATE UNIQUE INDEX role_permissions_unique_pair ON role_permissions (role_id, permission_id)');
    }

    public function down(): void
    {
        // Deliberately refuse rollback after privileged-role provisioning.
        // Never remove the security marker from active Super Admin assignments.
        $hasSuperAdmin = DB::table('roles')
            ->join('user_roles', 'roles.role_id', '=', 'user_roles.role_id')
            ->where('roles.is_system', true)
            ->whereRaw('LOWER(roles.role_name) = ?', ['super admin'])
            ->exists();

        if ($hasSuperAdmin) {
            throw new RuntimeException('A4 rollback blocked: an active Super Admin role assignment exists. Do not remove the security schema.');
        }

        DB::statement('DROP INDEX IF EXISTS role_permissions_unique_pair');
        DB::statement('ALTER TABLE role_permissions DROP CONSTRAINT IF EXISTS role_permissions_data_scope_check');
        Schema::table('role_permissions', fn (Blueprint $table) => $table->dropColumn('data_scope'));
        Schema::table('roles', fn (Blueprint $table) => $table->dropColumn('is_system'));
    }
};
