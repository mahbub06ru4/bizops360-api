<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\Operations\Http\Controllers\Api\V1\ProjectController;
use App\Modules\Operations\Http\Controllers\Api\V1\TaskActivityController;
use App\Modules\Operations\Http\Controllers\Api\V1\TaskCommentController;
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

        Route::get('tasks/{task}/comments', [TaskCommentController::class, 'index'])->name('tasks.comments.index');
        Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
        Route::get('tasks/{task}/activities', [TaskActivityController::class, 'index'])->name('tasks.activities.index');
        Route::put('task-comments/{taskComment}', [TaskCommentController::class, 'update'])->name('task-comments.update');
        Route::delete('task-comments/{taskComment}', [TaskCommentController::class, 'destroy'])->name('task-comments.destroy');

        Route::put('tasks/{task}/assignee', [TaskController::class, 'assign'])->name('tasks.assignee.update');
        Route::put('tasks/{task}/status', [TaskController::class, 'changeStatus'])->name('tasks.status.update');
        Route::apiResource('tasks', TaskController::class);
    });
