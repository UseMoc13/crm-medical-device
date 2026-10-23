<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('announcements')) {
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
            });
        }

        if (!Schema::hasTable('announcement_attachments')) {
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
            });
        }

        if (!Schema::hasTable('announcement_media')) {
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
            });
        }

        $this->addIndexIfMissing(
            'announcements',
            'announcements_status_index',
            'CREATE INDEX announcements_status_index ON announcements (status)'
        );

        $this->addIndexIfMissing(
            'announcements',
            'announcements_published_at_index',
            'CREATE INDEX announcements_published_at_index ON announcements (published_at)'
        );

        $this->addIndexIfMissing(
            'announcements',
            'announcements_is_pinned_pin_order_index',
            'CREATE INDEX announcements_is_pinned_pin_order_index ON announcements (is_pinned, pin_order)'
        );

        $this->addIndexIfMissing(
            'announcement_attachments',
            'announcement_attachments_media_type_index',
            'CREATE INDEX announcement_attachments_media_type_index ON announcement_attachments (media_type)'
        );

        $this->addIndexIfMissing(
            'announcement_media',
            'announcement_media_media_type_index',
            'CREATE INDEX announcement_media_media_type_index ON announcement_media (media_type)'
        );

        $this->addForeignKeyIfMissing(
            'announcement_attachments',
            'announcement_attachments_announcement_id_foreign',
            'ALTER TABLE announcement_attachments
             ADD CONSTRAINT announcement_attachments_announcement_id_foreign
             FOREIGN KEY (announcement_id)
             REFERENCES announcements (announcement_id)
             ON DELETE CASCADE'
        );

        $this->addForeignKeyIfMissing(
            'announcement_media',
            'announcement_media_announcement_id_foreign',
            'ALTER TABLE announcement_media
             ADD CONSTRAINT announcement_media_announcement_id_foreign
             FOREIGN KEY (announcement_id)
             REFERENCES announcements (announcement_id)
             ON DELETE CASCADE'
        );

        $this->addForeignKeyIfMissing(
            'announcements',
            'announcements_created_by_foreign',
            'ALTER TABLE announcements
             ADD CONSTRAINT announcements_created_by_foreign
             FOREIGN KEY (created_by)
             REFERENCES users (user_id)
             ON DELETE SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'announcements',
            'announcements_updated_by_foreign',
            'ALTER TABLE announcements
             ADD CONSTRAINT announcements_updated_by_foreign
             FOREIGN KEY (updated_by)
             REFERENCES users (user_id)
             ON DELETE SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'announcement_attachments',
            'announcement_attachments_uploaded_by_foreign',
            'ALTER TABLE announcement_attachments
             ADD CONSTRAINT announcement_attachments_uploaded_by_foreign
             FOREIGN KEY (uploaded_by)
             REFERENCES users (user_id)
             ON DELETE SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'announcement_media',
            'announcement_media_uploaded_by_foreign',
            'ALTER TABLE announcement_media
             ADD CONSTRAINT announcement_media_uploaded_by_foreign
             FOREIGN KEY (uploaded_by)
             REFERENCES users (user_id)
             ON DELETE SET NULL'
        );
    }

    private function addIndexIfMissing(
        string $table,
        string $index,
        string $sql
    ): void {
        $exists = DB::selectOne(
            'SELECT EXISTS (
                SELECT 1
                FROM pg_indexes
                WHERE schemaname = current_schema()
                  AND tablename = ?
                  AND indexname = ?
            ) AS exists',
            [$table, $index]
        );

        if (!$exists || !$exists->exists) {
            DB::statement($sql);
        }
    }

    private function addForeignKeyIfMissing(
        string $table,
        string $constraint,
        string $sql
    ): void {
        $exists = DB::selectOne(
            'SELECT EXISTS (
                SELECT 1
                FROM pg_constraint
                WHERE conname = ?
                  AND conrelid = ?::regclass
                  AND contype = \'f\'
            ) AS exists',
            [$constraint, $table]
        );

        if (!$exists || !$exists->exists) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        // Intentionally preserve existing announcement tables and data.
    }
};
