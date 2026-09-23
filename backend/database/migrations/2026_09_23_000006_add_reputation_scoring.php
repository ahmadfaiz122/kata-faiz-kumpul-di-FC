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
            $table->string('reputation_category', 32)->nullable()->after('reputation_emoji');
            $table->smallInteger('reputation_delta')->default(0)->after('reputation_category');
            $table->index(['reviewed', 'reputation_category']);
        });

        $mapping = [
            '😞' => ['category' => 'very_bad', 'delta' => -2],
            '🙁' => ['category' => 'needs_improvement', 'delta' => -1],
            '😐' => ['category' => 'neutral', 'delta' => 0],
            '🙂' => ['category' => 'good', 'delta' => 1],
            '😄' => ['category' => 'excellent', 'delta' => 2],
        ];

        DB::table('transaction_reviews')->get()->each(function ($review) use ($mapping) {
            $values = $mapping[$review->reputation_emoji ?: $review->reputation] ?? $mapping['😐'];
            DB::table('transaction_reviews')->where('id', $review->id)->update([
                'reputation_category' => $values['category'],
                'reputation_delta' => $values['delta'],
            ]);
        });

        DB::table('profiles')->update(['reputation' => 50]);
        DB::table('transaction_reviews')
            ->select('reviewed', DB::raw('SUM(reputation_delta) as total_delta'))
            ->groupBy('reviewed')
            ->get()
            ->each(function ($summary) {
                $score = max(1, min(100, 50 + (int) $summary->total_delta));
                DB::table('profiles')->where('user_id', $summary->reviewed)->update(['reputation' => $score]);
            });
    }

    public function down(): void
    {
        Schema::table('transaction_reviews', function (Blueprint $table) {
            $table->dropIndex(['transaction_reviews_reviewed_reputation_category_index']);
            $table->dropColumn(['reputation_category', 'reputation_delta']);
        });
    }
};
