<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_with_more_than_three_completed_transactions_are_listed(): void
    {
        $firstUser = User::factory()->create(['name' => 'Leaderboard User One']);
        $secondUser = User::factory()->create(['name' => 'Leaderboard User Two']);

        foreach (range(1, 4) as $index) {
            Transaction::create([
                'provider' => $firstUser->id,
                'requester' => $secondUser->id,
                'mode' => 'credit',
                'hour' => 1,
                'credits' => 1,
                'status' => 'completed',
                'completed_at' => now()->subDays($index),
            ]);

            Transaction::create([
                'provider' => $secondUser->id,
                'requester' => $firstUser->id,
                'mode' => 'credit',
                'hour' => 1,
                'credits' => 1,
                'status' => 'completed',
                'completed_at' => now()->subDays($index),
            ]);
        }

        $response = $this->getJson('/api/leaderboard');

        $response
            ->assertOk()
            ->assertJsonPath('meta.minimum_transactions', 3)
            ->assertJsonCount(2, 'data');

        $response->assertJsonFragment([
            'id' => $firstUser->id,
            'name' => 'Leaderboard User One',
            'transactions' => 8,
        ]);
        $response->assertJsonFragment([
            'id' => $secondUser->id,
            'name' => 'Leaderboard User Two',
            'transactions' => 8,
        ]);
    }
}
