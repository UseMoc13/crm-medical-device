<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Only primary business entities. Do not change owner/assignee columns or
     * invent creators for historical data. Existing announcements are excluded.
     */
    private const TABLES = [
        'brands',
        'product_categories',
        'products',
        'warehouses',
        'customers',
        'contacts',
        'leads',
        'activities',
        'opportunities',
        'quotations',
        'sales_orders',
        'contracts',
        'deliveries',
        'installations',
        'warranties',
        'service_tickets',
        'maintenance_schedules',
        'invoices',
        'serial_numbers',
        'stock_movements',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                // Avoid destroying existing installations with an optional module.
                continue;
            }

            $addCreated = ! Schema::hasColumn($tableName, 'created_by');
            $addUpdated = ! Schema::hasColumn($tableName, 'updated_by');

            if (! $addCreated && ! $addUpdated) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($addCreated, $addUpdated): void {
                if ($addCreated) {
                    $table->uuid('created_by')->nullable()->index();
                    $table->foreign('created_by')
                        ->references('user_id')->on('users')
                        ->nullOnDelete();
                }

                if ($addUpdated) {
                    $table->uuid('updated_by')->nullable()->index();
                    $table->foreign('updated_by')
                        ->references('user_id')->on('users')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // Down removes only the two conventional audit columns. If an older
        // schema already had columns under the same names, check before rollback.
        foreach (array_reverse(self::TABLES) as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                foreach (['created_by', 'updated_by'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }
            });
        }
    }
};
