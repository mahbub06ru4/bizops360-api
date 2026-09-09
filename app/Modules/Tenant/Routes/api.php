<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\Tenant\Http\Controllers\Api\V1\CompanyController;
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
        Route::get('company', [CompanyController::class, 'show'])->name('api.v1.company.show');
        Route::put('company', [CompanyController::class, 'update'])->name('api.v1.company.update');
    });
