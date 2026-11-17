<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Dedicated workspace inbox; does not mutate legacy CRM notifications.
        Schema::create('workspace_notifications', function (Blueprint $table) {
            $table->uuid('notification_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('recipient_user_id');
            $table->uuid('actor_user_id')->nullable();
            $table->string('kind', 60);
            $table->string('title', 160);
            $table->string('message', 255);
            $table->string('target_type', 20);
            $table->uuid('target_id');
            $table->string('dedupe_key', 160);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('recipient_user_id', 'wsn_recipient_fk')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('actor_user_id', 'wsn_actor_fk')->references('user_id')->on('users')->nullOnDelete();
            $table->unique(['recipient_user_id', 'dedupe_key'], 'wsn_user_dedupe_unique');
            $table->index(['recipient_user_id', 'read_at', 'created_at'], 'wsn_inbox_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_notifications');
    }
};
