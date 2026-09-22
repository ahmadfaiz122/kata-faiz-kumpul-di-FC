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
        if ((int) $transaction->requester !== (int) $request->user()->id) {
            return response()->json(['message' => 'Hanya requester yang dapat memberi review.'], 403);
        }
        if ($transaction->status !== 'completed') {
            return response()->json(['message' => 'Review tersedia setelah sesi satu jam selesai.'], 422);
        }
        if ($transaction->review()->exists()) {
            return response()->json(['message' => 'Review untuk transaksi ini sudah dikirim.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'reputation' => ['required', Rule::in(['sad', 'flat', 'smile'])],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = DB::transaction(function () use ($request, $transaction, $validated) {
            $review = TransactionReview::create([
                'transaction_id' => $transaction->id,
                'reviewer' => $request->user()->id,
                'reviewed' => $transaction->provider,
                ...$validated,
            ]);

            $providerProfile = $transaction->providerUser()->firstOrFail()->profile()->firstOrCreate([]);
            $providerProfile->increment('reputation', 1);
            $providerProfile->update([
                'rating' => TransactionReview::where('reviewed', $transaction->provider)->avg('rating'),
            ]);

            return $review;
        });

        return response()->json(['data' => $review], 201);
    }
}
