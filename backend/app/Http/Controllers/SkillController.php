<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return response()->json([
            'data' => $profile->skillRecords()->orderBy('name')->get(),
        ]);
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
        ]);

        // Ensure the user has a profile to attach skills to.
        $profile = $request->user()->profile()->firstOrCreate([]);

        $payload = collect($validated)->except(['id'])->toArray();

        if (! empty($validated['id'])) {
            $skill = $profile->skillRecords()->whereKey($validated['id'])->first();

            if (! $skill) {
                return response()->json(['message' => 'Skill not found.'], 404);
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

        $skill->delete();

        return response()->json(['message' => 'Skill deleted.']);
    }
}