<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionReviewController;
use App\Http\Controllers\LeaderboardController;
use App\Models\Category;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user()->only(['id', 'name', 'email', 'avatar', 'google_id']);
        $profile = $request->user()->profile()
            ->select(['id', 'user_id', 'username', 'alias', 'bio', 'nim', 'linkedin', 'github', 'instagram', 'rating', 'reputation', 'leaderboard', 'credits'])
            ->first();

        if ($request->boolean('details')) {
            $profile?->load([
                'skillRecords:id,profile_id,category_id,name,category_skills,description,material_path,status',
                'achievementRecords:id,profile_id,name,levels,category,description,validated_upload,certificate_path,tanggal_terbit,kadaluwarsa',
            ]);
        }

        if ($profile) {
            $nim = preg_match('/^([0-9]+)@mhs\.unesa\.ac\.id$/i', trim($user['email'] ?? ''), $matches)
                ? $matches[1]
                : null;
            $profile->setAttribute('nim', $nim);
            if ($request->boolean('details')) {
                $profile->setAttribute('skills', $profile->skillRecords->pluck('name')->values());
                $profile->setAttribute('achievements', $profile->achievementRecords->pluck('name')->values());
            }
        }

        $user['profile'] = $profile;
        return response()->json($user);
    })->middleware('auth:sanctum');

    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}/comments', [PostController::class, 'comments']);
    Route::get('/proposals', [ProposalController::class, 'index']);
    Route::get('/proposals/categories', [ProposalController::class, 'categories']);
    Route::get('/categories', fn () => response()->json(['data' => Category::query()->orderBy('name')->get(['id', 'name', 'slug'])]));
    Route::get('/proposals/{proposal}', [ProposalController::class, 'show']);
    Route::get('/proposals/{proposal}/file', [ProposalController::class, 'file']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/profile', [ProfileController::class, 'update']);
        Route::put('/profile', [ProfileController::class, 'update']);

        // Route::post('/posts', [PostController::class, 'store']);

        Route::get('/skills', [SkillController::class, 'index']);
        Route::post('/skills', [SkillController::class, 'store'])->middleware('throttle:10,1');
        Route::delete('/skills/{id}', [SkillController::class, 'destroy']);

        Route::get('/achievements', [AchievementController::class, 'index']);
        Route::post('/achievements', [AchievementController::class, 'store'])->middleware('throttle:10,1');
        Route::delete('/achievements/{id}', [AchievementController::class, 'destroy']);
        Route::post('/proposals', [ProposalController::class, 'store']);
        Route::get('/user/proposals', [ProposalController::class, 'mine']);
        Route::post('/user/proposals/{id}', [ProposalController::class, 'update']);
        Route::delete('/user/proposals/{id}', [ProposalController::class, 'destroy']);
        Route::get('/user/proposals', [ProposalController::class, 'mine']);
        Route::post('/user/proposals/{id}', [ProposalController::class, 'update']);
        Route::delete('/user/proposals/{id}', [ProposalController::class, 'destroy']);
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/credits/ledger', [TransactionController::class, 'ledger']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
        Route::get('/transactions/{transaction}/materials/{skill}', [TransactionController::class, 'material']);
        Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve']);
        Route::post('/transactions/{transaction}/review', [TransactionReviewController::class, 'store'])->middleware('throttle:10,1');
        Route::post('/posts', [PostController::class, 'store']);
        Route::post('/posts/{post}/like', [PostController::class, 'toggleLike']);
        Route::post('/posts/{post}/comments', [PostController::class, 'storeComment']);
        Route::get('/user/posts', [PostController::class, 'mine']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    });
});
