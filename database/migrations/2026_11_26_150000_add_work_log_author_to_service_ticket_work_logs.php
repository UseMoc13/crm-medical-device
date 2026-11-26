<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_ticket_work_logs', function (Blueprint $table): void {
            // NULL means legacy entry: do not falsely attribute older notes.
            $table->uuid('created_by_user_id')->nullable();
            $table->index('created_by_user_id', 'st_work_logs_author_idx');
            $table->foreign('created_by_user_id', 'st_work_logs_author_fk')
                ->references('user_id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_ticket_work_logs', function (Blueprint $table): void {
            $table->dropForeign('st_work_logs_author_fk');
            $table->dropIndex('st_work_logs_author_idx');
            $table->dropColumn('created_by_user_id');
        });
    }
};
