<?php

use App\Domains\Authentication\Http\Controllers\AuthController;
use App\Domains\Wallet\VirtualAccount\Http\Controllers\VirtualAccountController;
use App\Domains\Wallet\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('webhooks')->group(function () {
    Route::post('/deposit', [WebhookController::class, 'deposit']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('wallets')->group(function () {
        Route::prefix('virtual-accounts')->group(function () {
            Route::post('/', [VirtualAccountController::class, 'generateAccount']);
            Route::get('/', [VirtualAccountController::class, 'getAccount']);
        });
    });
});
