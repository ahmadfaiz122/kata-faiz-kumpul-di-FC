<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($this->profileFor($request));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'username' => ['sometimes', 'nullable', 'string', 'max:50'],
            'alias' => ['sometimes', 'nullable', 'string', 'max:100'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'nim' => ['sometimes', 'nullable', 'string', 'max:50'],
            'linkedin' => ['sometimes', 'nullable', 'url', 'max:255'],
            'github' => ['sometimes', 'nullable', 'url', 'max:255'],
            'instagram' => ['sometimes', 'nullable', 'url', 'max:255'],
            'skills' => ['sometimes', 'array'],
            'skills.*' => ['string', 'max:100'],
            'achievements' => ['sometimes', 'array'],
            'achievements.*' => ['string', 'max:255'],
        ]);

        $user = $request->user();
        if ($request->hasFile('avatar')) {
            if ($user->avatar && str_contains($user->avatar, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', parse_url($user->avatar, PHP_URL_PATH)));
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $request->getSchemeAndHttpHost() . Storage::url($validated['avatar']);
        }

        $user->fill(array_intersect_key($validated, array_flip(['name', 'email', 'avatar'])));
        $user->save();

        $profile = $user->profile()->updateOrCreate([], array_intersect_key(
            $validated,
            array_flip(['username', 'alias', 'bio', 'nim', 'linkedin', 'github', 'instagram', 'skills', 'achievements'])
        ));

        if (array_key_exists('skills', $validated)) {
            $profile->skillRecords()->delete();
            $profile->skillRecords()->createMany(array_map(
                fn (string $skill) => ['name' => $skill],
                $validated['skills']
            ));
        }

        if (array_key_exists('achievements', $validated)) {
            $profile->achievementRecords()->delete();
            $profile->achievementRecords()->createMany(array_map(
                fn (string $achievement) => ['name' => $achievement],
                $validated['achievements']
            ));
        }

        return response()->json($this->profileFor($request, $profile));
    }

    protected function profileFor(Request $request, ?Profile $profile = null): array
    {
        $user = $request->user()->loadMissing('profile.skillRecords', 'profile.achievementRecords');
        $profile ??= $user->profile;

        if ($profile) {
            $profile->setAttribute('skills', $profile->skillRecords->pluck('name')->values());
            $profile->setAttribute('achievements', $profile->achievementRecords->pluck('name')->values());
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'google_id' => $user->google_id,
            'profile' => $profile,
        ];
    }
}
