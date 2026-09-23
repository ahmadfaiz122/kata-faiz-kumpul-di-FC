<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Achievement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'tanggal_terbit' => ['required', 'date'],
            'kadaluwarsa' => ['required', 'date', 'after_or_equal:tanggal_terbit'],
            'certificate' => ['required_without:id', 'sometimes', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // Ensure the user has a profile to attach achievements to.
        $profile = $request->user()->profile()->firstOrCreate([]);

        if ($request->hasFile('certificate')) {
            $this->validateCertificate($request->file('certificate'));
        }

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

    private function validateCertificate(UploadedFile $file): void
    {
        $originalName = $file->getClientOriginalName();
        $safeName = basename($originalName);
        if ($safeName !== $originalName || preg_match('/[\x00-\x1F]/', $originalName) || strpbrk($originalName, "/\\") !== false || strlen($originalName) > 180) {
            throw ValidationException::withMessages([
                'certificate' => 'Nama file sertifikat tidak valid.',
            ]);
        }

        $mime = @((new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()));
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (! in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'certificate' => 'File harus berupa PDF, JPG, JPEG, atau PNG asli.',
            ]);
        }

        if ($mime === 'application/pdf') {
            $handle = fopen($file->getRealPath(), 'rb');
            $header = '';
            $tail = '';
            if (is_resource($handle)) {
                $header = fread($handle, 5);
                fseek($handle, -1024, SEEK_END);
                $tail = fread($handle, 1024);
                fclose($handle);
            }
            if ($header !== '%PDF-' || ! str_contains($tail, '%%EOF')) {
                throw ValidationException::withMessages([
                    'certificate' => 'File PDF tidak dapat dibaca atau rusak.',
                ]);
            }
            return;
        }

        $dimensions = @getimagesize($file->getRealPath());
        if (! $dimensions || $dimensions[0] < 100 || $dimensions[1] < 100) {
            throw ValidationException::withMessages([
                'certificate' => 'Dimensi gambar minimal 100 x 100 piksel.',
            ]);
        }
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
