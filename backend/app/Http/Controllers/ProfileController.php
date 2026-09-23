<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\TransactionReview;
use App\Models\User;
use App\Models\Post;
use App\Models\SkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($this->profileFor($request));
    }

    public function publicShow(User $user)
    {
        $user->load('profile.skillRecords', 'profile.achievementRecords');
        $profile = $user->profile;

        if ($profile) {
            $summary = $this->reviewSummary((int) $user->id);
            $profile->setAttribute('skills', $profile->skillRecords->pluck('name')->values());
            $profile->setAttribute('achievements', $profile->achievementRecords->pluck('name')->values());
            $profile->setAttribute('rating_average', $summary['rating_average']);
            $profile->setAttribute('reputation_score', (int) ($profile->reputation ?? 50));
            $profile->setAttribute('dominant_reputation_emoji', $summary['dominant_reputation_emoji']);
            $profile->setAttribute('review_count', $summary['review_count']);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'profile' => $profile,
            'posts' => Post::query()
                ->where('user_id', $user->id)
                ->with('user:id,name,avatar')
                ->withCount(['likes', 'comments'])
                ->latest('id')
                ->get(),
            'swapp_posts' => SkillRequest::query()
                ->where('requester', $user->id)
                ->where('status', 'pending')
                ->where(function ($query) {
                    $query->whereNull('available_at')->orWhere('available_at', '>', now());
                })
                ->with('skill:id,name,category_skills')
                ->latest('id')
                ->get(['id', 'requester', 'skill_id', 'skill_name', 'skill_category', 'skill_description', 'available_at', 'status', 'hour']),
        ]);
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

        $profilePayload = array_intersect_key(
            $validated,
            array_flip(['username', 'alias', 'bio', 'linkedin', 'github', 'instagram', 'skills', 'achievements'])
        );
        $profilePayload['nim'] = $this->nimFromEmail($user->email);
        $profile = $user->profile()->updateOrCreate([], $profilePayload);

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
        $user = $request->user()->only(['id', 'name', 'email', 'avatar', 'google_id']);
        $profile ??= $request->user()->profile()
            ->select(['id', 'user_id', 'username', 'alias', 'bio', 'nim', 'linkedin', 'github', 'instagram', 'rating', 'reputation', 'leaderboard', 'credits'])
            ->with([
                'skillRecords:id,profile_id,category_id,name,category_skills,description,material_path,status',
                'achievementRecords:id,profile_id,name,levels,category,description,validated_upload,certificate_path,tanggal_terbit,kadaluwarsa',
            ])
            ->first();

        if ($profile) {
            $profile->setAttribute('nim', $this->nimFromEmail($user['email']));
            $profile->setAttribute('skills', $profile->skillRecords->pluck('name')->values());
            $profile->setAttribute('achievements', $profile->achievementRecords->pluck('name')->values());
            $summary = $this->reviewSummary((int) $user->id);
            $profile->setAttribute('reputation_score', (int) ($profile->reputation ?? 50));
            $profile->setAttribute('rating_average', $summary['rating_average']);
            $profile->setAttribute('dominant_reputation_emoji', $summary['dominant_reputation_emoji']);
            $profile->setAttribute('review_count', $summary['review_count']);
        }

        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar' => $user['avatar'],
            'google_id' => $user['google_id'],
            'profile' => $profile,
        ];
    }

    public static function reviewSummary(int $userId): array
    {
        $reviews = TransactionReview::query()->where('reviewed', $userId)->get(['rating', 'reputation_emoji', 'reputation']);
        $counts = $reviews->groupBy(fn ($review) => $review->reputation_emoji ?: $review->reputation)->map->count();
        $dominant = $counts->sortDesc()->keys()->first();

        return [
            'rating_average' => $reviews->avg('rating') !== null ? round((float) $reviews->avg('rating'), 2) : null,
            'dominant_reputation_emoji' => $dominant,
            'review_count' => $reviews->count(),
        ];
    }
}
