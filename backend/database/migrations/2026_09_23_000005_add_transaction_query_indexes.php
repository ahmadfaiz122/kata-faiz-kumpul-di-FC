<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['provider', 'status', 'created_at']);
            $table->index(['requester', 'status', 'created_at']);
            $table->index(['status', 'starts_at', 'ends_at']);
        });

    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['transactions_provider_status_created_at_index']);
            $table->dropIndex(['transactions_requester_status_created_at_index']);
            $table->dropIndex(['transactions_status_starts_at_ends_at_index']);
        });
    }
};
