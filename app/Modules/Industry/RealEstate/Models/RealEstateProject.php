<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Models\User;
use App\Modules\Audit\Concerns\LogsBusinessActivity;
use App\Modules\Industry\RealEstate\Database\Factories\RealEstateProjectFactory;
use App\Modules\Industry\RealEstate\Domain\ProjectStatus;
use App\Modules\Industry\RealEstate\Domain\ProjectType;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * A development a seller (developer/agency tenant) is bringing to market — an
 * apartment building, a land-share plot, a commercial block, or a bare plot.
 * Everything else in the module — {@see ProjectLocation}, {@see Building}
 * (and its {@see Unit}s), {@see Amenity}, {@see ProjectPricing},
 * {@see ProjectPaymentPlan}, and for land-share projects {@see LandRecord} /
 * {@see LandShare} — hangs off this row.
 *
 * Starts life `draft`; {@see \App\Modules\Industry\RealEstate\Actions\SubmitProjectForVerification}
 * moves it to `pending_verification` for the platform's admin review queue
 * (Phase 2). Never trust `status` client-side for trust badges — only a
 * `verified` project may claim RAJUK/REHAB approval publicly.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $created_by
 * @property string $name
 * @property string $slug
 * @property ProjectType $project_type
 * @property string|null $description
 * @property ProjectStatus $status
 * @property string|null $total_land_area
 * @property string $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'project_type', 'description', 'total_land_area', 'currency'])]
class RealEstateProject extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<RealEstateProjectFactory> */
    use HasFactory;

    use LogsBusinessActivity;

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<ProjectLocation, $this> */
    public function locations(): HasMany
    {
        return $this->hasMany(ProjectLocation::class, 'project_id');
    }

    /** @return HasMany<ProjectDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class, 'project_id');
    }

    /** @return HasMany<LandRecord, $this> */
    public function landRecords(): HasMany
    {
        return $this->hasMany(LandRecord::class, 'project_id');
    }

    /** @return HasMany<LandShare, $this> */
    public function landShares(): HasMany
    {
        return $this->hasMany(LandShare::class, 'project_id');
    }

    /** @return HasMany<Building, $this> */
    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'project_id');
    }

    /** @return HasMany<Amenity, $this> */
    public function amenities(): HasMany
    {
        return $this->hasMany(Amenity::class, 'project_id');
    }

    /** @return HasOne<ProjectPricing, $this> */
    public function pricing(): HasOne
    {
        return $this->hasOne(ProjectPricing::class, 'project_id');
    }

    /** @return HasMany<ProjectPaymentPlan, $this> */
    public function paymentPlans(): HasMany
    {
        return $this->hasMany(ProjectPaymentPlan::class, 'project_id');
    }

    /** @return HasMany<SiteVisit, $this> */
    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class, 'project_id');
    }

    /** @return HasMany<VerificationReview, $this> */
    public function verificationReviews(): HasMany
    {
        return $this->hasMany(VerificationReview::class, 'project_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'project_type' => ProjectType::class,
            'status' => ProjectStatus::class,
            'total_land_area' => 'decimal:2',
        ];
    }

    protected static function newFactory(): RealEstateProjectFactory
    {
        return RealEstateProjectFactory::new();
    }
}
