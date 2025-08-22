<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocaleController;


Route::get('locales', [LocaleController::class, 'index']);
Route::post('locales', [LocaleController::class, 'storeOrUpdate']);
Route::put('locales/{locale}', [LocaleController::class, 'storeOrUpdate']);
Route::delete('locales/{locale}', [LocaleController::class, 'destroy']);
