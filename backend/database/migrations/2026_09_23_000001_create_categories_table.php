<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        DB::table('categories')->insert([
            ['name' => 'Education', 'slug' => 'education', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Technology', 'slug' => 'technology', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Business', 'slug' => 'business', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Language', 'slug' => 'language', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Art', 'slug' => 'art', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Writing', 'slug' => 'writing', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('skills', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('profile_id')->constrained('categories')->nullOnDelete();
        });

        DB::table('skills')->orderBy('id')->each(function ($skill) {
            $categoryId = DB::table('categories')->where('name', $skill->category_skills)->value('id');
            if ($categoryId) DB::table('skills')->where('id', $skill->id)->update(['category_id' => $categoryId]);
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
        Schema::dropIfExists('categories');
    }
};