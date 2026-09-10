<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

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
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/user/posts', [PostController::class, 'mine']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});
