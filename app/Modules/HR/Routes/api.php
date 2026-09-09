<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\HR\Http\Controllers\Api\V1\HolidayController;
use App\Modules\HR\Http\Controllers\Api\V1\LeaveBalanceController;
use App\Modules\HR\Http\Controllers\Api\V1\LeaveRequestController;
use App\Modules\HR\Http\Controllers\Api\V1\LeaveTypeController;
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
        Route::apiResource('holidays', HolidayController::class);
        Route::apiResource('leave-types', LeaveTypeController::class)
            ->parameters(['leave-types' => 'leaveType']);

        Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
            ->name('leave-requests.approve');
        Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
            ->name('leave-requests.reject');
        Route::post('leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])
            ->name('leave-requests.cancel');
        Route::apiResource('leave-requests', LeaveRequestController::class)
            ->only(['index', 'store', 'show'])
            ->parameters(['leave-requests' => 'leaveRequest']);

        Route::get('leave-balances', [LeaveBalanceController::class, 'index'])->name('leave-balances.index');
        Route::put('leave-balances', [LeaveBalanceController::class, 'upsert'])->name('leave-balances.upsert');
    });
