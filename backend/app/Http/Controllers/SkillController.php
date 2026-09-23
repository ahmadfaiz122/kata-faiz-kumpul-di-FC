<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            'data' => $profile->skillRecords()->where('status', 'published')->orderBy('name')->get(),
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'material' => ['required_without:id', 'sometimes', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        // Ensure the user has a profile to attach skills to.
        $profile = $request->user()->profile()->firstOrCreate([]);

        if ($request->hasFile('material')) {
            $this->validateMaterial($request->file('material'));
        }

        $payload = collect($validated)->except(['id', 'material'])->toArray();
        $payload['status'] = 'published';

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

    private function validateMaterial(UploadedFile $file): void
    {
        $originalName = $file->getClientOriginalName();
        if (
            basename($originalName) !== $originalName
            || preg_match('/[\x00-\x1F]/', $originalName)
            || strpbrk($originalName, "/\\") !== false
            || strlen($originalName) > 180
        ) {
            throw ValidationException::withMessages(['material' => 'Nama file materi tidak valid.']);
        }

        $path = $file->getRealPath();
        $mime = @((new \finfo(FILEINFO_MIME_TYPE))->file($path));
        if ($mime !== 'application/pdf') {
            throw ValidationException::withMessages(['material' => 'Materi harus berupa file PDF asli.']);
        }

        $contents = @file_get_contents($path);
        if ($contents === false || ! str_starts_with($contents, '%PDF-') || ! str_contains(substr($contents, -2048), '%%EOF')) {
            throw ValidationException::withMessages(['material' => 'File PDF rusak atau tidak dapat dibuka.']);
        }

        if (preg_match('/\/(?:JavaScript|JS|OpenAction|AA|Launch|RichMedia|EmbeddedFile)\b/i', $contents)) {
            throw ValidationException::withMessages(['material' => 'PDF dengan action aktif atau konten tertanam tidak diizinkan.']);
        }
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