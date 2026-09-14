<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Actions\SetProjectPricing;
use App\Modules\Industry\RealEstate\Database\Factories\ProjectPricingFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The one cost breakdown for a {@see RealEstateProject} — land + construction +
 * consultancy rolling up into `estimated_total`, computed at write time by
 * {@see SetProjectPricing}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $project_id
 * @property string $land_cost
 * @property string $construction_cost
 * @property string $consultancy_cost
 * @property string $estimated_total
 * @property string $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'land_cost', 'construction_cost', 'consultancy_cost', 'estimated_total', 'currency'])]
class ProjectPricing extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ProjectPricingFactory> */
    use HasFactory;

    // The migration creates `project_pricing` (singular — one row per
    // project); Eloquent's default pluralization would otherwise look for
    // `project_pricings`.
    protected $table = 'project_pricing';

    /** @return BelongsTo<RealEstateProject, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'land_cost' => 'decimal:2',
            'construction_cost' => 'decimal:2',
            'consultancy_cost' => 'decimal:2',
            'estimated_total' => 'decimal:2',
        ];
    }

    protected static function newFactory(): ProjectPricingFactory
    {
        return ProjectPricingFactory::new();
    }
}
