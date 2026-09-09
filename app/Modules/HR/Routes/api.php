<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\HR\Http\Controllers\Api\V1\AttendanceController;
use App\Modules\HR\Http\Controllers\Api\V1\AttendanceSettingController;
use App\Modules\HR\Http\Controllers\Api\V1\EmployeeDocumentController;
use App\Modules\HR\Http\Controllers\Api\V1\EmployeeDocumentDownloadController;
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

        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::get('attendance/summary', [AttendanceController::class, 'summary'])->name('attendance.summary');
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/{attendance}', [AttendanceController::class, 'show'])->name('attendance.show');

        Route::get('attendance-settings', [AttendanceSettingController::class, 'show'])->name('attendance-settings.show');
        Route::put('attendance-settings', [AttendanceSettingController::class, 'update'])->name('attendance-settings.update');

        Route::get('employee-documents/{employeeDocument}/file', EmployeeDocumentDownloadController::class)
            ->middleware('signed')
            ->name('api.v1.employee-documents.file');
        Route::apiResource('employee-documents', EmployeeDocumentController::class)
            ->only(['index', 'store', 'show', 'destroy'])
            ->parameters(['employee-documents' => 'employeeDocument']);
    });
