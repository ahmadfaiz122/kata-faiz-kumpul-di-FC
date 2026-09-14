<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            if (! Schema::hasColumn('achievements', 'tanggal_terbit')) {
                $table->integer('tanggal_terbit')->nullable();
            }
            if (! Schema::hasColumn('achievements', 'kadaluwarsa')) {
                $table->integer('kadaluwarsa')->nullable();
            }
            if (! Schema::hasColumn('achievements', 'description')) {
                $table->text('description')->nullable();
            }
            if (! Schema::hasColumn('achievements', 'certificate_path')) {
                $table->string('certificate_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            foreach (['tanggal_terbit', 'kadaluwarsa', 'description'] as $column) {
                if (Schema::hasColumn('achievements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};