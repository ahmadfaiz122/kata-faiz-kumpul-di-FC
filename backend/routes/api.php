<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\AchievementController;

Route::get('/user', function (Request $request) {
    $user = $request->user()->load('profile.skillRecords', 'profile.achievementRecords');

    if ($user->profile) {
        $user->profile->setAttribute('skills', $user->profile->skillRecords->pluck('name')->values());
        $user->profile->setAttribute('achievements', $user->profile->achievementRecords->pluck('name')->values());
    }

    return $user;
})->middleware('auth:sanctum');

Route::get('/posts', [PostController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Route::post('/posts', [PostController::class, 'store']);

    Route::get('/skills', [SkillController::class, 'index']);
    Route::post('/skills', [SkillController::class, 'store']);
    Route::delete('/skills/{id}', [SkillController::class, 'destroy']);

    Route::get('/achievements', [AchievementController::class, 'index']);
    Route::post('/achievements', [AchievementController::class, 'store']);
    Route::delete('/achievements/{id}', [AchievementController::class, 'destroy']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/user/posts', [PostController::class, 'mine']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});
