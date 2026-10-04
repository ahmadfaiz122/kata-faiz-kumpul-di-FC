<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\TransactionReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_submit_a_review_with_a_reputation_category(): void
    {
        $reviewer = User::factory()->create();
        $reviewed = User::factory()->create();
        $transaction = Transaction::create([
            'provider' => $reviewed->id,
            'requester' => $reviewer->id,
            'mode' => 'credit',
            'hour' => 1,
            'credits' => 1,
            'status' => 'completed',
            'completed_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($reviewer, 'sanctum')->postJson(
            "/api/transactions/{$transaction->id}/review",
            [
                'rating' => 5,
                'reputation_category' => 'excellent',
                'comment' => 'Sesi berjalan lancar.',
            ],
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.reputation_category', 'excellent')
            ->assertJsonPath('data.reputation_delta', 2);

        $this->assertDatabaseHas('transaction_reviews', [
            'transaction_id' => $transaction->id,
            'reviewer' => $reviewer->id,
            'reviewed' => $reviewed->id,
            'reputation_category' => 'excellent',
            'reputation_delta' => 2,
        ]);
    }
}
