<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_calendar_day_exclusions', function (Blueprint $table) {
            $table->uuid('exclusion_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('calendar_event_id');
            $table->date('excluded_date');
            $table->foreign('calendar_event_id', 'calendar_exclusion_event_fk')
                ->references('calendar_event_id')->on('workspace_calendar_events')->cascadeOnDelete();
            $table->unique(['calendar_event_id', 'excluded_date'], 'calendar_event_day_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_calendar_day_exclusions');
    }
};
