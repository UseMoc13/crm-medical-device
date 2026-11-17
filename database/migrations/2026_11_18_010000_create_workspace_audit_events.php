<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('workspace_audit_events')) {
            Schema::create('workspace_audit_events', function (Blueprint $table) {
                $table->uuid('audit_event_id')->primary();
                // No user FK: preserve audit history when a user account is deleted.
                $table->uuid('actor_user_id')->nullable()->index();
                $table->string('event_type', 70)->index();
                $table->string('subject_type', 30)->index();
                $table->uuid('subject_id')->nullable()->index();
                $table->json('details')->nullable(); // only allowlisted, non-sensitive metadata
                $table->timestamp('created_at')->useCurrent()->index();
                $table->index(['subject_type', 'subject_id', 'created_at'], 'wsa_subject_time_idx');
            });
        }
        // Permission defaults to DENY for ordinary roles. Existing Super Admin
        // continues to pass the centralized CRM permission resolver.
        if (Schema::hasTable('permissions') && !DB::table('permissions')
            ->where('permission_name', 'workspace_audit.view')->exists()) {
            $row = ['permission_id' => (string) Str::uuid(),
                'permission_name' => 'workspace_audit.view'];
            $columns = [
                'module' => 'workspace_audit', 'action' => 'view',
                'description' => 'Read sanitized workspace audit trail',
                'created_at' => now(),
            ];
            foreach ($columns as $column => $value) {
                if (Schema::hasColumn('permissions', $column)) $row[$column] = $value;
            }
            DB::table('permissions')->insert($row);
        }
    }

    public function down(): void
    {
        // Deliberately preserve the existing permission because it may be granted
        // to roles; rollback of the log table is destructive and must be backed up.
        Schema::dropIfExists('workspace_audit_events');
    }
};
