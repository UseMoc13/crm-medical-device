
<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The users table is managed by the existing CRM PostgreSQL schema.
    }

    public function down(): void
    {
        // Preserve the existing CRM users table and its data.
    }
};
