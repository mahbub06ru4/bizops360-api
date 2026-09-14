<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\AmenityFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A project-level amenity (swimming pool, gym, generator backup, …) shown on
 * the buyer-facing project detail page.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $project_id
 * @property string $name
 * @property string|null $icon
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'name', 'icon'])]
class Amenity extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<AmenityFactory> */
    use HasFactory;

    /** @return BelongsTo<RealEstateProject, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    protected static function newFactory(): AmenityFactory
    {
        return AmenityFactory::new();
    }
}
