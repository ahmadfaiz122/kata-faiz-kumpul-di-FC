<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_ledger', function (Blueprint $table) {
            $table->unique(['transaction_id', 'type'], 'credit_ledger_transaction_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('credit_ledger', function (Blueprint $table) {
            $table->dropUnique('credit_ledger_transaction_type_unique');
        });
    }
};
