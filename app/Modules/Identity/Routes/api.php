<?php

declare(strict_types=1);

use App\Modules\Identity\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware('api')
    ->group(function (): void {
        Route::post('auth/register', [AuthController::class, 'register'])
            ->middleware('throttle:6,1')
            ->name('api.v1.auth.register');

        Route::post('auth/login', [AuthController::class, 'login'])
            ->middleware('throttle:6,1')
            ->name('api.v1.auth.login');

        Route::middleware(['auth:sanctum', 'tenant'])->group(function (): void {
            Route::get('auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        });
    });
