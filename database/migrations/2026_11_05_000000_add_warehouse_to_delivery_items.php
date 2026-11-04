<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('delivery_items')) {
            throw new RuntimeException('Run migration create_delivery_items_if_missing first.');
        }
        if (!Schema::hasColumn('delivery_items', 'warehouse_id')) {
            Schema::table('delivery_items', function (Blueprint $table) {
                $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses', 'warehouse_id');
            });
        }
    }
    public function down(): void
    {
        // Non-destructive: do not remove warehouse assignments from existing shipments.
    }
};
