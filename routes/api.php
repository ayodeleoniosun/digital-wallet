<?php

use App\Domains\Authentication\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::prefix('register')->group(function () {
        Route::post('/', [RegisterController::class, 'create']);
    });
});
