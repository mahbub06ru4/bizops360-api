<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\Organization\Http\Controllers\Api\V1\BranchController;
use App\Modules\Organization\Http\Controllers\Api\V1\DepartmentController;
use App\Modules\Organization\Http\Controllers\Api\V1\DesignationController;
use App\Modules\Organization\Http\Controllers\Api\V1\EmployeeController;
use App\Modules\Organization\Http\Controllers\Api\V1\RoleController;
use App\Modules\Organization\Http\Controllers\Api\V1\TeamController;
use App\Modules\Organization\Http\Controllers\Api\V1\UserController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

// Tenant context is resolved before route-model binding runs, so a bound model
// that belongs to another tenant is filtered out by the global scope and yields
// a 404 rather than leaking its existence.
Route::prefix('api/v1')
    ->middleware([
        ForceJsonResponse::class,
        'auth:sanctum',
        'tenant',
        'throttle:60,1',
        SubstituteBindings::class,
    ])
    ->group(function (): void {
        Route::apiResource('branches', BranchController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('designations', DesignationController::class);

        Route::post('employees/{employee}/terminate', [EmployeeController::class, 'terminate'])
            ->name('employees.terminate');
        Route::apiResource('employees', EmployeeController::class);

        Route::put('teams/{team}/members', [TeamController::class, 'setMembers'])->name('teams.members.update');
        Route::apiResource('teams', TeamController::class);

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');

        Route::put('users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.roles.update');
        Route::apiResource('users', UserController::class);
    });
