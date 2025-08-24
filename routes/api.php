<?php

use App\Http\Controllers\Api\LocaleController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::get('locales', [LocaleController::class, 'index']);
    Route::post('locales', [LocaleController::class, 'store']);
    Route::put('locales/{id}', [LocaleController::class, 'update']);
    Route::delete('locales/{id}', [LocaleController::class, 'destroy']);

    Route::get('tags', [TagController::class, 'index']);
    Route::post('tags', [TagController::class, 'store']);
    Route::put('tags/{id}', [TagController::class, 'update']);
    Route::delete('tags/{id}', [TagController::class, 'destroy']);

    Route::get('translations', [TranslationController::class, 'index']);
    Route::post('translations', [TranslationController::class, 'store']);
    Route::put('translations/{id}', [TranslationController::class, 'update']);
    Route::delete('translations/{id}', [TranslationController::class, 'destroy']);
});
