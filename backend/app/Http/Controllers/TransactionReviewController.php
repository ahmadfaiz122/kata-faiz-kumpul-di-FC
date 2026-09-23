<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'reputation' => ['required', 'numeric', 'min:1', 'max:100'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $recentReviewExists = TransactionReview::query()
            ->where('reviewer', $request->user()->id)
            ->where('reviewed', $transaction->provider)
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();
        if ($recentReviewExists) {
            throw ValidationException::withMessages([
                'review' => 'Rating untuk partner yang sama hanya dapat diberikan sekali dalam 7 hari.',
            ]);
        }

        $review = DB::transaction(function () use ($request, $transaction, $validated) {
            $review = TransactionReview::create([
                'transaction_id' => $transaction->id,
                'reviewer' => $request->user()->id,
                'reviewed' => $transaction->provider,
                ...$validated,
            ]);

            $providerProfile = $transaction->providerUser()->firstOrFail()->profile()->firstOrCreate([]);
            $providerProfile->update([
                'rating' => TransactionReview::where('reviewed', $transaction->provider)->avg('rating'),
                'reputation' => TransactionReview::where('reviewed', $transaction->provider)->avg('reputation'),
            ]);

            return $review;
        });

        return response()->json(['data' => $review], 201);
    }
}
