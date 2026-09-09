<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\Industry\Travel\Http\Controllers\Api\V1\BookingController;
use App\Modules\Industry\Travel\Http\Controllers\Api\V1\TravellerController;
use App\Modules\Industry\Travel\Http\Controllers\Api\V1\TravelReportController;
use App\Modules\Industry\Travel\Http\Controllers\Api\V1\VisaApplicationController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware([
        ForceJsonResponse::class,
        'auth:sanctum',
        'tenant',
        'industry:travel',
        'throttle:60,1',
        SubstituteBindings::class,
    ])
    ->group(function (): void {
        Route::get('travel/overview', [TravelReportController::class, 'overview'])->name('travel.overview');

        Route::apiResource('travellers', TravellerController::class);

        Route::post('visa-applications/{visaApplication}/requirements', [VisaApplicationController::class, 'addRequirement'])->name('visa-applications.requirements.store');
        Route::put('visa-requirements/{visaRequirement}', [VisaApplicationController::class, 'toggleRequirement'])->name('visa-requirements.toggle');
        Route::post('visa-applications/{visaApplication}/submit', [VisaApplicationController::class, 'submit'])->name('visa-applications.submit');
        Route::post('visa-applications/{visaApplication}/processing', [VisaApplicationController::class, 'processing'])->name('visa-applications.processing');
        Route::post('visa-applications/{visaApplication}/decision', [VisaApplicationController::class, 'decision'])->name('visa-applications.decision');
        Route::post('visa-applications/{visaApplication}/cancel', [VisaApplicationController::class, 'cancel'])->name('visa-applications.cancel');
        Route::apiResource('visa-applications', VisaApplicationController::class);

        Route::post('bookings/{booking}/issue', [BookingController::class, 'issue'])->name('bookings.issue');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('bookings/{booking}/refund', [BookingController::class, 'refund'])->name('bookings.refund');
        Route::post('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
        Route::apiResource('bookings', BookingController::class);
    });
