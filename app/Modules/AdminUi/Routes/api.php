<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\AdminUi\Http\Controllers\Api\V1\AdminSchemaController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware([
        ForceJsonResponse::class,
        'auth:sanctum',
        'tenant',
        'throttle:60,1',
    ])
    ->group(function (): void {
        Route::get('admin/schema', [AdminSchemaController::class, 'index'])->name('admin.schema');
    });
