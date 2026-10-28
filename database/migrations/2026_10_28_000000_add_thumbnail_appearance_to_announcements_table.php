<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'thumbnail_fit' => [10, 'cover'],
            'thumbnail_position' => [10, 'center'],
            'thumbnail_background_style' => [15, 'solid'],
            'thumbnail_background' => [7, '#17284f'],
            'thumbnail_gradient_start' => [7, '#17284f'],
            'thumbnail_gradient_end' => [7, '#2ba7a0'],
        ];

        foreach ($columns as $column => [$length, $default]) {
            if (!Schema::hasColumn('announcements', $column)) {
                Schema::table('announcements', function (Blueprint $table) use ($column, $length, $default) {
                    $table->string($column, $length)->default($default);
                });
            }
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            'thumbnail_fit',
            'thumbnail_position',
            'thumbnail_background_style',
            'thumbnail_background',
            'thumbnail_gradient_start',
            'thumbnail_gradient_end',
        ], fn ($column) => Schema::hasColumn('announcements', $column)));

        if ($columns) {
            Schema::table('announcements', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
