<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_reports');
        Schema::dropIfExists('user_blocks');
    }

    public function down(): void
    {
        // The rejected block/report feature is intentionally not recreated.
    }
};
