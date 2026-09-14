<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\ProjectLocationFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Where a {@see RealEstateProject} sits — Bangladesh's division/district/area/
 * sector/road structure, plus free-text landmarks and optional coordinates for
 * map display. Powers the "near metro / near landmark" search Phase 4 will add.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $project_id
 * @property string|null $division
 * @property string|null $district
 * @property string|null $area
 * @property string|null $sector
 * @property string|null $road
 * @property string|null $landmark
 * @property string|null $latitude
 * @property string|null $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'division', 'district', 'area', 'sector', 'road', 'landmark', 'latitude', 'longitude'])]
class ProjectLocation extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ProjectLocationFactory> */
    use HasFactory;

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
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function newFactory(): ProjectLocationFactory
    {
        return ProjectLocationFactory::new();
    }
}
