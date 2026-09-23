<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transaction_reviews')->where('reputation', 'sad')->update(['reputation' => 25]);
        DB::table('transaction_reviews')->where('reputation', 'flat')->update(['reputation' => 50]);
        DB::table('transaction_reviews')->where('reputation', 'smile')->update(['reputation' => 100]);

        Schema::table('transaction_reviews', function (Blueprint $table) {
            $table->unsignedTinyInteger('reputation_value')->nullable()->after('reputation');
        });

        DB::table('transaction_reviews')->update([
            'reputation_value' => DB::raw('CAST(reputation AS UNSIGNED)'),
        ]);

        Schema::table('transaction_reviews', function (Blueprint $table) {
            $table->dropColumn('reputation');
            $table->renameColumn('reputation_value', 'reputation');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->decimal('reputation_average', 5, 2)->default(0)->after('reputation');
        });
        DB::table('profiles')->update(['reputation_average' => DB::raw('reputation')]);
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('reputation');
            $table->renameColumn('reputation_average', 'reputation');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_reviews', function (Blueprint $table) {
            $table->string('reputation_text')->nullable()->after('rating');
        });
        DB::table('transaction_reviews')->update(['reputation_text' => DB::raw('CAST(reputation AS CHAR)')]);
        Schema::table('transaction_reviews', function (Blueprint $table) {
            $table->dropColumn('reputation');
            $table->renameColumn('reputation_text', 'reputation');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedInteger('reputation_value')->default(0)->after('rating');
        });
        DB::table('profiles')->update(['reputation_value' => DB::raw('ROUND(reputation)')]);
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('reputation');
            $table->renameColumn('reputation_value', 'reputation');
        });
    }
};