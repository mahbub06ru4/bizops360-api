<?php

declare(strict_types=1);

use App\Modules\Tenant\Http\Controllers\Api\V1\CompanyController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware(['api', 'auth:sanctum', 'tenant', 'throttle:60,1'])
    ->group(function (): void {
        Route::get('company', [CompanyController::class, 'show'])->name('api.v1.company.show');
        Route::put('company', [CompanyController::class, 'update'])->name('api.v1.company.update');
    });
