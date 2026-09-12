<?php

declare(strict_types=1);

use App\Modules\Audit\Http\Controllers\Api\V1\ActivityController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware(['api', 'auth:sanctum', 'tenant', 'throttle:60,1'])
    ->group(function (): void {
        Route::get('activity', [ActivityController::class, 'index'])->name('api.v1.activity.index');
    });
