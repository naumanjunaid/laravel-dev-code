<?php

use App\Http\Controllers\Api\LocaleController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;

Route::get('locales', [LocaleController::class, 'index']);
Route::post('locales', [LocaleController::class, 'storeOrUpdate']);
Route::put('locales/{locale}', [LocaleController::class, 'storeOrUpdate']);
Route::delete('locales/{locale}', [LocaleController::class, 'destroy']);

Route::get('tags', [TagController::class, 'index']);
Route::post('tags', [TagController::class, 'storeOrUpdate']);
Route::put('tags/{tag}', [TagController::class, 'storeOrUpdate']);
Route::delete('tags/{tag}', [TagController::class, 'destroy']);

Route::get('translations', [TranslationController::class, 'index']);
Route::post('translations', [TranslationController::class, 'storeOrUpdate']);
Route::put('translations/{translation}', [TranslationController::class, 'storeOrUpdate']);
Route::delete('translations/{translation}', [TranslationController::class, 'destroy']);
