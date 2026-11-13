<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('installation_checklists')) {
            return;
        }

        foreach (['created_by', 'updated_by', 'completed_by'] as $column) {
            if (Schema::hasColumn('installation_checklists', $column)) {
                continue;
            }

            Schema::table('installation_checklists', function (Blueprint $table) use ($column): void {
                $table->uuid($column)->nullable();
                $table->foreign($column)
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // This migration only adds columns. Keep historical audit trails intact
        // during a rollback until a dedicated data-retention decision is made.
    }
};
