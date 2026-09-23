<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionReviewController extends Controller
{
    public function store(Request $request, Transaction $transaction)
    {
        if (! in_array((int) $request->user()->id, [(int) $transaction->requester, (int) $transaction->provider], true)) {
            return response()->json(['message' => 'Kamu bukan peserta transaksi ini.'], 403);
        }
        if ($transaction->status !== 'completed') {
            return response()->json(['message' => 'Review tersedia setelah sesi satu jam selesai.'], 422);
        }
        if ($transaction->reviews()->where('reviewer', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Review untuk transaksi ini sudah dikirim.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'reputation_category' => ['required', Rule::in(['very_bad', 'needs_improvement', 'neutral', 'good', 'excellent'])],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $reputationMap = [
            'very_bad' => ['emoji' => '😞', 'delta' => -2],
            'needs_improvement' => ['emoji' => '🙁', 'delta' => -1],
            'neutral' => ['emoji' => '😐', 'delta' => 0],
            'good' => ['emoji' => '🙂', 'delta' => 1],
            'excellent' => ['emoji' => '😄', 'delta' => 2],
        ];
        $reputation = $reputationMap[$validated['reputation_category']];

        $reviewed = (int) $transaction->requester === (int) $request->user()->id
            ? $transaction->provider
            : $transaction->requester;

        $review = DB::transaction(function () use ($request, $transaction, $validated, $reviewed, $reputation) {
            $review = TransactionReview::create([
                'transaction_id' => $transaction->id,
                'reviewer' => $request->user()->id,
                'reviewed' => $reviewed,
                'rating' => $validated['rating'],
                'reputation_emoji' => $reputation['emoji'],
                'reputation' => $validated['reputation_category'],
                'reputation_category' => $validated['reputation_category'],
                'reputation_delta' => $reputation['delta'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $reviewedProfile = $reviewed === (int) $transaction->provider
                ? $transaction->providerUser()->firstOrFail()->profile()->firstOrCreate([])
                : $transaction->requesterUser()->firstOrFail()->profile()->firstOrCreate([]);
            $reviewedProfile->update([
                'reputation' => max(1, min(100, (int) $reviewedProfile->reputation + $reputation['delta'])),
            ]);
            $reviewedProfile->update([
                'rating' => TransactionReview::where('reviewed', $reviewed)->avg('rating'),
            ]);

            return $review;
        });

        return response()->json(['data' => $review], 201);
    }
}
