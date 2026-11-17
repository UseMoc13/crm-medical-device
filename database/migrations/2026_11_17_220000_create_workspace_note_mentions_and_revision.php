<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workspace_personal_notes', function (Blueprint $table): void {
            $table->unsignedInteger('revision')->default(0);
        });
        Schema::create('workspace_note_mentions', function (Blueprint $table): void {
            $table->uuid('mention_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('note_id');
            $table->string('target_type', 12);
            $table->uuid('target_id');
            $table->timestamps();
            $table->foreign('note_id')->references('note_id')->on('workspace_personal_notes')->cascadeOnDelete();
            $table->unique(['note_id', 'target_type', 'target_id'], 'workspace_note_mention_target_unique');
            $table->index(['target_type', 'target_id'], 'workspace_note_mention_target_idx');
        });
        // Nullable polymorphic targets intentionally have no FK: access is always checked at read/write.
        DB::statement("ALTER TABLE workspace_note_mentions ADD CONSTRAINT workspace_note_mentions_type_check CHECK (target_type IN ('activity','todo','calendar'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_note_mentions');
        Schema::table('workspace_personal_notes', fn (Blueprint $table) => $table->dropColumn('revision'));
    }
};
