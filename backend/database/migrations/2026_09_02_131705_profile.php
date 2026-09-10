<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Profile fields are created by the later profiles table migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse; this migration is retained for existing databases.
    }
};
