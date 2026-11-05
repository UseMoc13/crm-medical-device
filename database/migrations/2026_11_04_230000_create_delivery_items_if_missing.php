<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('delivery_items')) return;
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->uuid('delivery_item_id')->primary();
            $table->foreignUuid('delivery_id')->constrained('deliveries', 'delivery_id')->cascadeOnDelete();
            $table->foreignUuid('serial_number_id')->constrained('serial_numbers', 'serial_number_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->unique(['delivery_id', 'serial_number_id']);
            $table->index('serial_number_id');
        });
    }

    public function down(): void
    {
        // An existing table must never be dropped by a migration that did not create it.
        // Rollback is deliberately non-destructive.
    }
};
