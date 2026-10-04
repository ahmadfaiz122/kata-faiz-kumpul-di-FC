<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LeaderboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $firstUser = User::updateOrCreate(
            ['email' => 'leaderboard.demo.one@example.test'],
            ['name' => 'Leaderboard Demo One', 'password' => Hash::make('password')],
        );
        $secondUser = User::updateOrCreate(
            ['email' => 'leaderboard.demo.two@example.test'],
            ['name' => 'Leaderboard Demo Two', 'password' => Hash::make('password')],
        );

        Transaction::query()
            ->whereIn('provider', [$firstUser->id, $secondUser->id])
            ->whereIn('requester', [$firstUser->id, $secondUser->id])
            ->where('mode', 'skill')
            ->delete();

        foreach (range(1, 4) as $index) {
            Transaction::create([
                'provider' => $firstUser->id,
                'requester' => $secondUser->id,
                'mode' => 'skill',
                'hour' => 1,
                'credits' => 0,
                'status' => 'completed',
                'completed_at' => now()->subDays($index),
            ]);
            Transaction::create([
                'provider' => $secondUser->id,
                'requester' => $firstUser->id,
                'mode' => 'skill',
                'hour' => 1,
                'credits' => 0,
                'status' => 'completed',
                'completed_at' => now()->subDays($index),
            ]);
        }

        $this->command?->info('Leaderboard demo siap: dua user dengan masing-masing 8 transaksi.');
    }
}
