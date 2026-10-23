<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stock_movements', 'reversal_of_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->uuid('reversal_of_id')->nullable();
            });
        }

        if (!Schema::hasColumn('stock_movements', 'reversed_by_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->uuid('reversed_by_id')->nullable();
            });
        }

        if (!Schema::hasColumn('stock_movements', 'correction_reason')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->text('correction_reason')->nullable();
            });
        }

        $constraints = [
            'stock_movements_reversal_of_id_foreign' => 'reversal_of_id',
            'stock_movements_reversed_by_id_foreign' => 'reversed_by_id',
        ];

        foreach ($constraints as $constraint => $column) {
            $exists = DB::table('pg_constraint')
                ->where('conname', $constraint)
                ->whereRaw("conrelid = 'stock_movements'::regclass")
                ->exists();

            if (!$exists) {
                Schema::table('stock_movements', function (Blueprint $table) use ($column) {
                    $table->foreign($column)
                        ->references('stock_movement_id')
                        ->on('stock_movements')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // Sengaja tidak menghapus kolom atau foreign key
        // untuk melindungi data yang mungkin sudah digunakan di production.
    }
};
