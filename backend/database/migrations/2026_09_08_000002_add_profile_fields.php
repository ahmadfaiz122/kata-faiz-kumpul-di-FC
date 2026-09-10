<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->unique()->after('id')->constrained()->cascadeOnDelete();
            $table->string('username')->nullable();
            $table->string('alias')->nullable();
            $table->text('bio')->nullable();
            $table->string('nim')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('twitter')->nullable();
            $table->json('skills')->nullable();
            $table->json('achievements')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 'username', 'alias', 'bio', 'nim', 'linkedin',
                'github', 'twitter', 'skills', 'achievements',
            ]);
        });
    }
};