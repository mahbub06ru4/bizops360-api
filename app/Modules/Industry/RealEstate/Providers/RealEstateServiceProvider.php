<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Providers;

use App\Modules\Industry\RealEstate\Models\Amenity;
use App\Modules\Industry\RealEstate\Models\Building;
use App\Modules\Industry\RealEstate\Models\Installment;
use App\Modules\Industry\RealEstate\Models\InstallmentPlan;
use App\Modules\Industry\RealEstate\Models\LandRecord;
use App\Modules\Industry\RealEstate\Models\Offer;
use App\Modules\Industry\RealEstate\Models\ProjectDocument;
use App\Modules\Industry\RealEstate\Models\PropertyRequirement;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Industry\RealEstate\Models\SiteVisit;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Industry\RealEstate\Models\VerificationReview;
use App\Modules\Industry\RealEstate\Policies\AmenityPolicy;
use App\Modules\Industry\RealEstate\Policies\BuildingPolicy;
use App\Modules\Industry\RealEstate\Policies\InstallmentPlanPolicy;
use App\Modules\Industry\RealEstate\Policies\InstallmentPolicy;
use App\Modules\Industry\RealEstate\Policies\LandRecordPolicy;
use App\Modules\Industry\RealEstate\Policies\OfferPolicy;
use App\Modules\Industry\RealEstate\Policies\ProjectDocumentPolicy;
use App\Modules\Industry\RealEstate\Policies\ProjectPolicy;
use App\Modules\Industry\RealEstate\Policies\PropertyRequirementPolicy;
use App\Modules\Industry\RealEstate\Policies\RealEstateBookingPolicy;
use App\Modules\Industry\RealEstate\Policies\SiteVisitPolicy;
use App\Modules\Industry\RealEstate\Policies\UnitPolicy;
use App\Modules\Industry\RealEstate\Policies\VerificationReviewPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class RealEstateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(RealEstateProject::class, ProjectPolicy::class);
        Gate::policy(Building::class, BuildingPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(Amenity::class, AmenityPolicy::class);
        Gate::policy(ProjectDocument::class, ProjectDocumentPolicy::class);
        Gate::policy(LandRecord::class, LandRecordPolicy::class);

        Gate::policy(PropertyRequirement::class, PropertyRequirementPolicy::class);
        Gate::policy(SiteVisit::class, SiteVisitPolicy::class);
        Gate::policy(Offer::class, OfferPolicy::class);
        Gate::policy(RealEstateBooking::class, RealEstateBookingPolicy::class);
        Gate::policy(InstallmentPlan::class, InstallmentPlanPolicy::class);
        Gate::policy(Installment::class, InstallmentPolicy::class);
        Gate::policy(VerificationReview::class, VerificationReviewPolicy::class);
    }
}
