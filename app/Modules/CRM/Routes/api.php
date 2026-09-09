<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\CRM\Http\Controllers\Api\V1\ContactController;
use App\Modules\CRM\Http\Controllers\Api\V1\CrmActivityController;
use App\Modules\CRM\Http\Controllers\Api\V1\CustomerController;
use App\Modules\CRM\Http\Controllers\Api\V1\FollowUpController;
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
        Route::get('leads/{lead}/contacts', [ContactController::class, 'leadIndex'])->name('leads.contacts.index');
        Route::post('leads/{lead}/contacts', [ContactController::class, 'leadStore'])->name('leads.contacts.store');
        Route::get('leads/{lead}/activities', [CrmActivityController::class, 'leadIndex'])->name('leads.activities.index');
        Route::post('leads/{lead}/notes', [CrmActivityController::class, 'leadNote'])->name('leads.notes.store');
        Route::get('leads/{lead}/follow-ups', [FollowUpController::class, 'leadIndex'])->name('leads.follow-ups.index');
        Route::post('leads/{lead}/follow-ups', [FollowUpController::class, 'leadStore'])->name('leads.follow-ups.store');
        Route::apiResource('leads', LeadController::class);

        Route::get('customers/{customer}/contacts', [ContactController::class, 'customerIndex'])->name('customers.contacts.index');
        Route::post('customers/{customer}/contacts', [ContactController::class, 'customerStore'])->name('customers.contacts.store');
        Route::get('customers/{customer}/activities', [CrmActivityController::class, 'customerIndex'])->name('customers.activities.index');
        Route::post('customers/{customer}/notes', [CrmActivityController::class, 'customerNote'])->name('customers.notes.store');
        Route::get('customers/{customer}/follow-ups', [FollowUpController::class, 'customerIndex'])->name('customers.follow-ups.index');
        Route::post('customers/{customer}/follow-ups', [FollowUpController::class, 'customerStore'])->name('customers.follow-ups.store');
        Route::apiResource('customers', CustomerController::class);

        Route::put('contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
        Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

        Route::get('follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
        Route::post('follow-ups/{followUp}/complete', [FollowUpController::class, 'complete'])->name('follow-ups.complete');
        Route::post('follow-ups/{followUp}/cancel', [FollowUpController::class, 'cancel'])->name('follow-ups.cancel');
        Route::put('follow-ups/{followUp}', [FollowUpController::class, 'update'])->name('follow-ups.update');
        Route::delete('follow-ups/{followUp}', [FollowUpController::class, 'destroy'])->name('follow-ups.destroy');
    });
