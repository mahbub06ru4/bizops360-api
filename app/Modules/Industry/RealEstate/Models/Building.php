<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\BuildingFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A single tower/block within a {@see RealEstateProject}. A `plot` or `land_share`
 * project may have none; an `apartment`/`commercial` project usually has one
 * or more, each with its own {@see Unit}s.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $project_id
 * @property string $name
 * @property int $total_floors
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'name', 'total_floors'])]
class Building extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<BuildingFactory> */
    use HasFactory;

    /** @return BelongsTo<RealEstateProject, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    /** @return HasMany<Unit, $this> */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_floors' => 'integer',
        ];
    }

    protected static function newFactory(): BuildingFactory
    {
        return BuildingFactory::new();
    }
}
