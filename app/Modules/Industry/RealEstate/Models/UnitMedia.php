<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\UnitMediaFactory;
use App\Modules\Industry\RealEstate\Domain\UnitMediaType;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One photo, floor plan, or video attached to a {@see Unit}, in display order.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $unit_id
 * @property string $file_path
 * @property UnitMediaType $media_type
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['media_type', 'sort_order'])]
class UnitMedia extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<UnitMediaFactory> */
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
            'media_type' => UnitMediaType::class,
            'sort_order' => 'integer',
        ];
    }

    protected static function newFactory(): UnitMediaFactory
    {
        return UnitMediaFactory::new();
    }
}
