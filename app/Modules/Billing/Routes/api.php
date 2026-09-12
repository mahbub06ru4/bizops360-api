<?php

declare(strict_types=1);

use App\Modules\Billing\Http\Controllers\Api\V1\PlanController;
use App\Modules\Billing\Http\Controllers\Api\V1\PlatformAnalyticsController;
use App\Modules\Billing\Http\Controllers\Api\V1\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware(['api', 'auth:sanctum', 'throttle:60,1'])
    ->group(function (): void {
        Route::get('billing/plans', [PlanController::class, 'index'])->name('api.v1.billing.plans');

        Route::middleware('tenant')->group(function (): void {
            Route::get('billing/subscription', [SubscriptionController::class, 'show'])->name('api.v1.billing.subscription.show');
            Route::put('billing/subscription', [SubscriptionController::class, 'update'])->name('api.v1.billing.subscription.update');
            Route::post('billing/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('api.v1.billing.subscription.cancel');
        });

        Route::middleware('platform_admin')->group(function (): void {
            Route::get('platform/analytics', [PlatformAnalyticsController::class, 'index'])->name('api.v1.platform.analytics');
        });
    });
