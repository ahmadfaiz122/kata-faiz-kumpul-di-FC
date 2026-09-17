<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (! Schema::hasColumn('requests', 'full_name')) {
                $table->string('full_name')->nullable()->after('requester');
            }
            if (! Schema::hasColumn('requests', 'email')) {
                $table->string('email')->nullable()->after('full_name');
            }
            if (! Schema::hasColumn('requests', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (! Schema::hasColumn('requests', 'city')) {
                $table->string('city')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('requests', 'skill_description')) {
                $table->text('skill_description')->nullable()->after('city');
            }
            if (! Schema::hasColumn('requests', 'proposal_path')) {
                $table->string('proposal_path')->nullable()->after('skill_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $columns = ['full_name', 'email', 'phone', 'city', 'skill_description', 'proposal_path'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};