<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\Operations\Http\Controllers\Api\V1\ProjectController;
use App\Modules\Operations\Http\Controllers\Api\V1\TaskController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware([
        ForceJsonResponse::class,
        'auth:sanctum',
        'tenant',
        'throttle:60,1',
        SubstituteBindings::class,
    ])
    ->group(function (): void {
        Route::apiResource('projects', ProjectController::class);

        Route::put('tasks/{task}/assignee', [TaskController::class, 'assign'])->name('tasks.assignee.update');
        Route::put('tasks/{task}/status', [TaskController::class, 'changeStatus'])->name('tasks.status.update');
        Route::apiResource('tasks', TaskController::class);
    });
