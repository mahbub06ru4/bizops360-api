<?php

declare(strict_types=1);

use App\Modules\Organization\Http\Controllers\Api\V1\BranchController;
use App\Modules\Organization\Http\Controllers\Api\V1\DepartmentController;
use App\Modules\Organization\Http\Controllers\Api\V1\DesignationController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware(['api', 'auth:sanctum', 'tenant', 'throttle:60,1'])
    ->group(function (): void {
        Route::apiResource('branches', BranchController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('designations', DesignationController::class);
    });
