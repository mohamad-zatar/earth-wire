<?php

use App\Http\Controllers\Article\ArticleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\UserPreference\UserPreferenceController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{id}', [ArticleController::class, 'show']);

Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::get('/articles/personalized', [ArticleController::class, 'personalizedFeed']);

    Route::post('/preferences', [UserPreferenceController::class, 'setPreferences']);
    Route::get('/preferences', [UserPreferenceController::class, 'getPreferences']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
