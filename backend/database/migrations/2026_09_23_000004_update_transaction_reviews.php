<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_reviews', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropUnique('transaction_reviews_transaction_id_unique');
            $table->string('reputation_emoji', 4)->nullable()->after('rating');
            $table->unique(['transaction_id', 'reviewer']);
            $table->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete();
        });

        DB::table('transaction_reviews')->whereNull('reputation_emoji')->get()->each(function ($review) {
            $emoji = [1 => '😞', 2 => '🙁', 3 => '😐', 4 => '🙂', 5 => '😄'][(int) $review->rating] ?? null;
            DB::table('transaction_reviews')->where('id', $review->id)->update([
                'reputation_emoji' => $emoji,
                'reputation' => $emoji,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('transaction_reviews', function (Blueprint $table) {
            $table->dropUnique('transaction_reviews_transaction_id_reviewer_unique');
            $table->dropColumn('reputation_emoji');
            $table->unique('transaction_id');
        });
    }
};
