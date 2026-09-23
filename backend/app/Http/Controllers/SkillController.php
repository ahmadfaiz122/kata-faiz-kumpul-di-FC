<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\TransactionReview;

class SkillController extends Controller
{
    /**
     * GET /api/skills
     * Return the authenticated user's skills.
     */
    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            return response()->json(['data' => []]);
        }

        $skills = $profile->skillRecords()->orderBy('name')->get();
        $skills->each(function ($skill) {
            $reviews = TransactionReview::query()
                ->join('transactions', 'transaction_reviews.transaction_id', '=', 'transactions.id')
                ->join('requests', 'transactions.barter_request', '=', 'requests.id')
                ->where('requests.skill_id', $skill->id)
                ->get(['transaction_reviews.rating', 'transaction_reviews.reputation_emoji', 'transaction_reviews.reputation']);
            $counts = $reviews->groupBy(fn ($review) => $review->reputation_emoji ?: $review->reputation)->map->count();
            $skill->setAttribute('rating_average', $reviews->avg('rating') !== null ? round((float) $reviews->avg('rating'), 2) : null);
            $skill->setAttribute('dominant_reputation_emoji', $counts->sortDesc()->keys()->first());
            $skill->setAttribute('review_count', $reviews->count());
        });

        return response()->json(['data' => $skills]);
    }

    /**
     * POST /api/skills
     * Create a new skill, or update an existing one if "id" is provided.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => ['sometimes', 'nullable', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'category_skills' => ['required', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'material' => ['required_without:id', 'sometimes', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        // Ensure the user has a profile to attach skills to.
        $profile = $request->user()->profile()->firstOrCreate([]);

        $payload = collect($validated)->except(['id', 'material'])->toArray();

        if ($request->hasFile('material')) {
            $payload['material_path'] = $request->getSchemeAndHttpHost() . Storage::url(
                $request->file('material')->store('skill-materials', 'public')
            );
        }

        if (! empty($validated['id'])) {
            $skill = $profile->skillRecords()->whereKey($validated['id'])->first();

            if (! $skill) {
                return response()->json(['message' => 'Skill not found.'], 404);
            }

            if ($request->hasFile('material') && $skill->material_path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', parse_url($skill->material_path, PHP_URL_PATH)));
            }

            $skill->update($payload);
        } else {
            $skill = $profile->skillRecords()->create($payload);
        }

        return response()->json(
            ['data' => $skill->fresh()],
            $skill->wasRecentlyCreated ? 201 : 200
        );
    }

    /**
     * DELETE /api/skills/{skill}
     * (Optional but included for completeness — remove a skill owned by the user.)
     */
    public function destroy(Request $request, int $id)
    {
        $profile = $request->user()->profile;

        $skill = $profile?->skillRecords()->whereKey($id)->first();

        if (! $skill) {
            return response()->json(['message' => 'Skill not found.'], 404);
        }

        if ($skill->material_path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', parse_url($skill->material_path, PHP_URL_PATH)));
        }

        $skill->delete();

        return response()->json(['message' => 'Skill deleted.']);
    }
}
