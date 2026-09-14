<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\AmenityController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\BuildingController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\InstallmentController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\InstallmentPlanController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\OfferController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\ProjectController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\ProjectDocumentController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\ProjectVerificationController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\PropertyRequirementController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\RealEstateBookingController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\SiteVisitController;
use App\Modules\Industry\RealEstate\Http\Controllers\Api\V1\UnitController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/real-estate')
    ->middleware([
        ForceJsonResponse::class,
        'auth:sanctum',
        'tenant',
        'industry:real_estate',
        'throttle:60,1',
        SubstituteBindings::class,
    ])
    ->group(function (): void {
        Route::post('projects/{project}/submit', [ProjectController::class, 'submit'])->name('real-estate.projects.submit');
        Route::post('projects/{project}/location', [ProjectController::class, 'addLocation'])->name('real-estate.projects.location.store');
        Route::post('projects/{project}/pricing', [ProjectController::class, 'setPricing'])->name('real-estate.projects.pricing.store');
        Route::post('projects/{project}/payment-plans', [ProjectController::class, 'addPaymentPlan'])->name('real-estate.projects.payment-plans.store');
        Route::post('projects/{project}/land-shares', [ProjectController::class, 'addLandShare'])->name('real-estate.projects.land-shares.store');
        Route::post('projects/{project}/land-records', [ProjectController::class, 'addLandRecord'])->name('real-estate.projects.land-records.store');

        Route::get('projects/{project}/documents', [ProjectDocumentController::class, 'index'])->name('real-estate.projects.documents.index');
        Route::post('projects/{project}/documents', [ProjectDocumentController::class, 'store'])->name('real-estate.projects.documents.store');

        Route::post('projects/{project}/buildings', [BuildingController::class, 'store'])->name('real-estate.projects.buildings.store');
        Route::get('buildings/{building}', [BuildingController::class, 'show'])->name('real-estate.buildings.show');
        Route::put('buildings/{building}', [BuildingController::class, 'update'])->name('real-estate.buildings.update');
        Route::delete('buildings/{building}', [BuildingController::class, 'destroy'])->name('real-estate.buildings.destroy');

        Route::post('buildings/{building}/units', [UnitController::class, 'store'])->name('real-estate.buildings.units.store');
        Route::get('units/{unit}', [UnitController::class, 'show'])->name('real-estate.units.show');
        Route::put('units/{unit}', [UnitController::class, 'update'])->name('real-estate.units.update');
        Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('real-estate.units.destroy');
        Route::post('units/{unit}/media', [UnitController::class, 'addMedia'])->name('real-estate.units.media.store');
        Route::post('units/{unit}/prices', [UnitController::class, 'addPrice'])->name('real-estate.units.prices.store');

        Route::post('projects/{project}/amenities', [AmenityController::class, 'store'])->name('real-estate.projects.amenities.store');
        Route::delete('amenities/{amenity}', [AmenityController::class, 'destroy'])->name('real-estate.amenities.destroy');

        Route::apiResource('projects', ProjectController::class)->names('real-estate.projects');

        // Phase 1 commercial loop: Lead -> Requirement -> Property Match ->
        // Site Visit -> Negotiation -> Reservation -> Booking -> Installment.
        Route::get('leads/{lead}/requirements', [PropertyRequirementController::class, 'index'])->name('real-estate.leads.requirements.index');
        Route::post('leads/{lead}/requirements', [PropertyRequirementController::class, 'store'])->name('real-estate.leads.requirements.store');
        Route::get('requirements/{requirement}', [PropertyRequirementController::class, 'show'])->name('real-estate.requirements.show');
        Route::post('requirements/{requirement}/match', [PropertyRequirementController::class, 'match'])->name('real-estate.requirements.match');

        Route::get('site-visits', [SiteVisitController::class, 'all'])->name('real-estate.site-visits.all');
        Route::get('leads/{lead}/site-visits', [SiteVisitController::class, 'index'])->name('real-estate.leads.site-visits.index');
        Route::post('leads/{lead}/site-visits', [SiteVisitController::class, 'store'])->name('real-estate.leads.site-visits.store');
        Route::get('site-visits/{visit}', [SiteVisitController::class, 'show'])->name('real-estate.site-visits.show');
        Route::post('site-visits/{visit}/complete', [SiteVisitController::class, 'complete'])->name('real-estate.site-visits.complete');
        Route::post('site-visits/{visit}/cancel', [SiteVisitController::class, 'cancel'])->name('real-estate.site-visits.cancel');

        Route::get('offers', [OfferController::class, 'all'])->name('real-estate.offers.all');
        Route::get('leads/{lead}/offers', [OfferController::class, 'index'])->name('real-estate.leads.offers.index');
        Route::post('leads/{lead}/offers', [OfferController::class, 'store'])->name('real-estate.leads.offers.store');
        Route::get('offers/{offer}', [OfferController::class, 'show'])->name('real-estate.offers.show');
        Route::post('offers/{offer}/counter', [OfferController::class, 'counter'])->name('real-estate.offers.counter');
        Route::post('offers/{offer}/accept', [OfferController::class, 'accept'])->name('real-estate.offers.accept');
        Route::post('offers/{offer}/reject', [OfferController::class, 'reject'])->name('real-estate.offers.reject');

        Route::post('offers/{offer}/reserve', [RealEstateBookingController::class, 'store'])->name('real-estate.offers.reserve');
        Route::get('real-estate-bookings', [RealEstateBookingController::class, 'index'])->name('real-estate.bookings.index');
        Route::get('real-estate-bookings/{booking}', [RealEstateBookingController::class, 'show'])->name('real-estate.bookings.show');
        Route::post('real-estate-bookings/{booking}/confirm', [RealEstateBookingController::class, 'confirm'])->name('real-estate.bookings.confirm');
        Route::post('real-estate-bookings/{booking}/cancel', [RealEstateBookingController::class, 'cancel'])->name('real-estate.bookings.cancel');

        Route::post('real-estate-bookings/{booking}/installment-plan', [InstallmentPlanController::class, 'store'])->name('real-estate.bookings.installment-plan.store');
        Route::get('installment-plans/{installmentPlan}', [InstallmentPlanController::class, 'show'])->name('real-estate.installment-plans.show');

        Route::get('installments/{installment}', [InstallmentController::class, 'show'])->name('real-estate.installments.show');
        Route::post('installments/{installment}/generate-invoice', [InstallmentController::class, 'generateInvoice'])->name('real-estate.installments.generate-invoice');
        Route::post('installments/{installment}/mark-paid', [InstallmentController::class, 'markPaid'])->name('real-estate.installments.mark-paid');
    });

// Platform-admin verification queue: a platform admin is not tied to any one
// tenant, so — like Billing's `platform/analytics` — this sits outside the
// `tenant`/`industry:real_estate` gate entirely and relies solely on the
// `platform_admin` middleware.
Route::prefix('api/v1/real-estate')
    ->middleware([
        ForceJsonResponse::class,
        'auth:sanctum',
        'platform_admin',
        'throttle:60,1',
        SubstituteBindings::class,
    ])
    ->group(function (): void {
        Route::post('projects/{project}/verify', [ProjectVerificationController::class, 'verify'])->name('real-estate.projects.verify');
        Route::post('projects/{project}/reject', [ProjectVerificationController::class, 'reject'])->name('real-estate.projects.reject');
    });
