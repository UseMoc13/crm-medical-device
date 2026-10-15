
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->uuid('reversal_of_id')->nullable();
            $table->uuid('reversed_by_id')->nullable();
            $table->text('correction_reason')->nullable();

            $table->foreign('reversal_of_id')
                ->references('stock_movement_id')
                ->on('stock_movements')
                ->nullOnDelete();

            $table->foreign('reversed_by_id')
                ->references('stock_movement_id')
                ->on('stock_movements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['reversal_of_id']);
            $table->dropForeign(['reversed_by_id']);

            $table->dropColumn([
                'reversal_of_id',
                'reversed_by_id',
                'correction_reason',
            ]);
        });
    }
};
