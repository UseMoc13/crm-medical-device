<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('announcement_id')
                ->primary()
                ->default(DB::raw('gen_random_uuid()'));

            $table->string('title', 150);
            $table->string('slug', 180)->nullable()->unique();
            $table->string('category', 100)->nullable();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('thumbnail_path', 500)->nullable();

            $table->string('status', 20)->default('draft');

            $table->boolean('is_pinned')->default(false);
            $table->unsignedInteger('pin_order')->nullable();

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();

            $table->foreignUuid('updated_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
            $table->index(['is_pinned', 'pin_order']);
        });

        Schema::create('announcement_attachments', function (Blueprint $table) {
            $table->uuid('attachment_id')
                ->primary()
                ->default(DB::raw('gen_random_uuid()'));

            $table->uuid('announcement_id');

            $table->string('original_name', 255);
            $table->string('file_path', 500);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('media_type', 30)->default('document');
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignUuid('uploaded_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();

            $table->timestamps();

            $table->foreign('announcement_id')
                ->references('announcement_id')
                ->on('announcements')
                ->cascadeOnDelete();

            $table->index('media_type');
        });

        Schema::create('announcement_media', function (Blueprint $table) {
            $table->uuid('media_id')
                ->primary()
                ->default(DB::raw('gen_random_uuid()'));

            $table->uuid('announcement_id');

            $table->string('original_name', 255);
            $table->string('file_path', 500);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('media_type', 30)->default('image');
            $table->string('alt_text', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignUuid('uploaded_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();

            $table->timestamps();

            $table->foreign('announcement_id')
                ->references('announcement_id')
                ->on('announcements')
                ->cascadeOnDelete();

            $table->index('media_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_media');
        Schema::dropIfExists('announcement_attachments');
        Schema::dropIfExists('announcements');
    }
};