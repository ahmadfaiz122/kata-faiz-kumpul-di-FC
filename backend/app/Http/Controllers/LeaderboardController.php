<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LeaderboardController extends Controller
{
    private const MIN_TRANSACTIONS = 3;
    private const BAYESIAN_MIN_REVIEWS = 5;
    private const VELOCITY_DAYS = 30;
    private const DECAY_DAYS = 90;
    private const RECIPROCAL_WINDOW_DAYS = 7;

    public function index(Request $request)
    {
        $transactions = Transaction::query()
            ->with(['review', 'barterRequest:id,skill_id', 'barterRequest.skill:id,name', 'requesterSkill:id,name'])
            ->where('status', 'completed')
            ->where(function ($query) {
                $query->whereNotNull('completed_at')->orWhere('ends_at', '<=', now());
            })
            ->get();

        $reviews = TransactionReview::query()
            ->whereIn('transaction_id', $transactions->pluck('id'))
            ->get();
        $globalRating = (float) ($reviews->avg('rating') ?: 0);
        $users = User::query()->with('profile')->whereIn('id', $transactions->flatMap(fn ($transaction) => [$transaction->provider, $transaction->requester])->unique())->get()->keyBy('id');
        $participantCounts = $transactions->flatMap(fn ($transaction) => [$transaction->provider, $transaction->requester])->countBy();
        $maxTransactions = max(self::MIN_TRANSACTIONS, (int) ($participantCounts->max() ?: self::MIN_TRANSACTIONS));

        $rows = $users->map(function (User $user) use ($transactions, $reviews, $globalRating, $maxTransactions) {
            $asProvider = $transactions->where('provider', $user->id);
            $asRequester = $transactions->where('requester', $user->id);
            $completed = $asProvider->merge($asRequester);
            $userReviews = $reviews->where('reviewed', $user->id);
            $eligibleReviews = $this->eligibleReviews($userReviews, $reviews);
            $transactionCount = $completed->count();

            if ($transactionCount < self::MIN_TRANSACTIONS || $this->isSuspiciousUser($user->id, $reviews)) {
                return null;
            }

            $averageRating = (float) ($eligibleReviews->avg('rating') ?: 0);
            $reviewCount = $eligibleReviews->count();
            $weightedRating = (($reviewCount / ($reviewCount + self::BAYESIAN_MIN_REVIEWS)) * $averageRating)
                + ((self::BAYESIAN_MIN_REVIEWS / ($reviewCount + self::BAYESIAN_MIN_REVIEWS)) * $globalRating);
            $ratingNormalized = $weightedRating / 5;
            $transactionComponent = log(1 + $transactionCount) / log(1 + max(self::MIN_TRANSACTIONS, $maxTransactions));
            $recentCount = $completed->filter(fn ($transaction) => ($transaction->completed_at ?? $transaction->ends_at)?->gte(now()->subDays(self::VELOCITY_DAYS)))->count();
            $creditVelocity = min(1, $recentCount / self::VELOCITY_DAYS);
            $uniqueSkills = $completed->map(fn ($transaction) => $transaction->provider === $user->id ? $transaction->barterRequest?->skill?->name : $transaction->requesterSkill?->name)->filter()->unique()->count();
            $uniquePartners = $completed->map(fn ($transaction) => $transaction->provider === $user->id ? $transaction->requester : $transaction->provider)->unique()->count();
            $diversityBonus = min(1, ($uniqueSkills / 5) * 0.7 + ($uniquePartners / 5) * 0.3);
            $lastActivity = $completed->map(fn ($transaction) => $transaction->completed_at ?? $transaction->ends_at ?? $transaction->updated_at)->filter()->max();
            $decay = $lastActivity ? exp(-max(0, now()->diffInDays($lastActivity)) / self::DECAY_DAYS) : 0;
            $score = (0.45 * $ratingNormalized) + (0.20 * $transactionComponent) + (0.20 * $creditVelocity) + (0.15 * $diversityBonus);
            $score *= $decay;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'score' => round($score * 100, 2),
                'rating' => round($weightedRating, 2),
                'reputation' => round((float) ($eligibleReviews->avg('reputation') ?: 0), 2),
                'transactions' => $transactionCount,
                'recent_transactions' => $recentCount,
                'unique_skills' => $uniqueSkills,
                'unique_partners' => $uniquePartners,
                'decay' => round($decay, 4),
            ];
        })->filter()->sortByDesc('score')->values()->take(50)->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'minimum_transactions' => self::MIN_TRANSACTIONS,
                'weights' => ['rating' => 0.45, 'transactions' => 0.20, 'velocity' => 0.20, 'diversity' => 0.15],
                'global_rating' => round($globalRating, 2),
            ],
        ]);
    }

    private function eligibleReviews(Collection $userReviews, Collection $allReviews): Collection
    {
        return $userReviews->filter(function ($review) use ($allReviews) {
            return ! $allReviews->contains(function ($other) use ($review) {
                return $other->id !== $review->id
                    && $other->reviewer === $review->reviewed
                    && $other->reviewed === $review->reviewer
                    && $other->created_at->diffInDays($review->created_at) < self::RECIPROCAL_WINDOW_DAYS;
            });
        });
    }

    private function isSuspiciousUser(int $userId, Collection $reviews): bool
    {
        $userReviews = $reviews->where('reviewed', $userId);
        return $userReviews->groupBy('reviewer')->contains(function (Collection $partnerReviews) use ($userId, $reviews) {
            if ($partnerReviews->count() < 3 || (float) $partnerReviews->avg('rating') < 4.5) return false;
            $partnerId = (int) $partnerReviews->first()->reviewer;
            $reverse = $reviews->where('reviewer', $userId)->where('reviewed', $partnerId);
            return $reverse->count() >= 3 && (float) $reverse->avg('rating') >= 4.5;
        });
    }
}