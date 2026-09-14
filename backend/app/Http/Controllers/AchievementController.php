<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Achievement;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    /**
     * GET /api/achievements
     * Return the authenticated user's achievements.
     */
    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => $profile->achievementRecords()->orderByDesc('created_at')->get(),
        ]);
    }

    /**
     * POST /api/achievements
     * Create a new achievement, or update an existing one if "id" is provided.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => ['sometimes', 'nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'levels' => ['required', 'string', 'max:100'],
            'category' => ['sometimes','nullable', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'tanggal_terbit' => ['required', 'integer'],
            'kadaluwarsa' => ['required', 'integer'],
            'certificate' => ['required_without:id', 'sometimes', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:10240'],
        ]);

        // Ensure the user has a profile to attach achievements to.
        $profile = $request->user()->profile()->firstOrCreate([]);

        $payload = collect($validated)->except(['id', 'certificate'])->toArray();

        if ($request->hasFile('certificate')) {
            $payload['certificate_path'] = $request->getSchemeAndHttpHost() . Storage::url(
                $request->file('certificate')->store('certificates', 'public')
            );
            $payload['validated_upload'] = true;
        }

        if (! empty($validated['id'])) {
            $achievement = $profile->achievementRecords()->whereKey($validated['id'])->first();

            if (! $achievement) {
                return response()->json(['message' => 'Achievement not found.'], 404);
            }

            if ($request->hasFile('certificate') && $achievement->certificate_path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', parse_url($achievement->certificate_path, PHP_URL_PATH)));
            }

            $achievement->update($payload);
        } else {
            $achievement = $profile->achievementRecords()->create($payload);
        }

        return response()->json(
            ['data' => $achievement->fresh()],
            $achievement->wasRecentlyCreated ? 201 : 200
        );
    }

    /**
     * DELETE /api/achievements/{achievement}
     * (Optional but included for completeness.)
     */
    public function destroy(Request $request, int $id)
    {
        $profile = $request->user()->profile;

        $achievement = $profile?->achievementRecords()->whereKey($id)->first();

        if (! $achievement) {
            return response()->json(['message' => 'Achievement not found.'], 404);
        }

        if ($achievement->certificate_path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', parse_url($achievement->certificate_path, PHP_URL_PATH)));
        }

        $achievement->delete();

        return response()->json(['message' => 'Achievement deleted.']);
    }
}
