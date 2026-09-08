<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    $user = $request->user()->load('profile.skillRecords', 'profile.achievementRecords');

    if ($user->profile) {
        $user->profile->setAttribute('skills', $user->profile->skillRecords->pluck('name')->values());
        $user->profile->setAttribute('achievements', $user->profile->achievementRecords->pluck('name')->values());
    }

    return $user;
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
});