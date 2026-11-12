<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_ticket_work_logs', function (Blueprint $table) {
            $table->uuid('work_log_id')->primary();
            $table->uuid('service_ticket_id');
            $table->string('entry_type', 30);
            $table->string('status_snapshot', 30);
            $table->text('notes');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('service_ticket_id')->references('service_ticket_id')
                ->on('service_tickets')->cascadeOnDelete();
            $table->index(['service_ticket_id', 'created_at'], 'st_work_logs_ticket_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_ticket_work_logs');
    }
};
