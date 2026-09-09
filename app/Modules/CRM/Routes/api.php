<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\CRM\Http\Controllers\Api\V1\CustomerController;
use App\Modules\CRM\Http\Controllers\Api\V1\LeadController;
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
        Route::get('leads/pipeline', [LeadController::class, 'pipeline'])->name('leads.pipeline');
        Route::put('leads/{lead}/stage', [LeadController::class, 'moveStage'])->name('leads.stage.update');
        Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
        Route::apiResource('leads', LeadController::class);

        Route::apiResource('customers', CustomerController::class);
    });
