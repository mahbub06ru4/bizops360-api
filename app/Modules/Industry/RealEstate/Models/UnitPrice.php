<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\UnitPriceFactory;
use App\Modules\Industry\RealEstate\Domain\UnitPriceType;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One price point on a {@see Unit} — `base` (as first listed), `current`
 * (what it sells for today), or `per_sqft`. Kept as a history rather than a
 * single mutable column so a project's pricing story stays auditable.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $unit_id
 * @property string $price
 * @property UnitPriceType $price_type
 * @property Carbon|null $effective_from
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['unit_id', 'price', 'price_type', 'effective_from'])]
class UnitPrice extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<UnitPriceFactory> */
    use HasFactory;

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_type' => UnitPriceType::class,
            'effective_from' => 'date',
        ];
    }

    protected static function newFactory(): UnitPriceFactory
    {
        return UnitPriceFactory::new();
    }
}
