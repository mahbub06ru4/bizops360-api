<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\UnitFactory;
use App\Modules\Industry\RealEstate\Domain\UnitFacing;
use App\Modules\Industry\RealEstate\Domain\UnitStatus;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One sellable unit inside a {@see Building} — size, floor, layout, and sale
 * status. Its photos/floor-plans live in {@see UnitMedia}, and its price
 * history (base/current/per-sqft) in {@see UnitPrice}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $building_id
 * @property string $unit_number
 * @property int $floor
 * @property string $size_sqft
 * @property int|null $bedrooms
 * @property int|null $bathrooms
 * @property UnitFacing|null $facing
 * @property int $parking_spaces
 * @property UnitStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['building_id', 'unit_number', 'floor', 'size_sqft', 'bedrooms', 'bathrooms', 'facing', 'parking_spaces'])]
class Unit extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<UnitFactory> */
    use HasFactory;

    /** @return BelongsTo<Building, $this> */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /** @return HasMany<UnitMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(UnitMedia::class)->orderBy('sort_order');
    }

    /** @return HasMany<UnitPrice, $this> */
    public function prices(): HasMany
    {
        return $this->hasMany(UnitPrice::class);
    }

    /** @return HasMany<PropertyMatch, $this> */
    public function propertyMatches(): HasMany
    {
        return $this->hasMany(PropertyMatch::class);
    }

    /** @return HasMany<Offer, $this> */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_sqft' => 'decimal:2',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'facing' => UnitFacing::class,
            'parking_spaces' => 'integer',
            'status' => UnitStatus::class,
        ];
    }

    protected static function newFactory(): UnitFactory
    {
        return UnitFactory::new();
    }
}
