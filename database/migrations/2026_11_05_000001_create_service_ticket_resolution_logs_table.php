<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_ticket_resolution_logs', function (Blueprint $table) {
            $table->uuid('resolution_log_id')->primary();
            $table->uuid('service_ticket_id');
            $table->string('status_from', 30);
            $table->string('status_to', 30);
            $table->text('resolution');
            $table->timestamp('created_at');
            $table->foreign('service_ticket_id')->references('service_ticket_id')->on('service_tickets')->cascadeOnDelete();
            $table->index(['service_ticket_id', 'created_at'], 'ticket_resolution_logs_by_ticket');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('service_ticket_resolution_logs');
    }
};
