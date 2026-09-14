<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Actions\MatchRequirementToUnits;
use App\Modules\Industry\RealEstate\Database\Factories\PropertyMatchFactory;
use App\Modules\Industry\RealEstate\Domain\PropertyMatchStatus;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A unit suggested against a {@see PropertyRequirement} by the rule-based
 * scorer in {@see MatchRequirementToUnits}
 * — never AI/NLP (roadmap Phase 4 territory).
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $requirement_id
 * @property int|null $unit_id
 * @property string $match_score
 * @property PropertyMatchStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['requirement_id', 'unit_id', 'match_score', 'status'])]
class PropertyMatch extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<PropertyMatchFactory> */
    use HasFactory;

    /** @return BelongsTo<PropertyRequirement, $this> */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(PropertyRequirement::class, 'requirement_id');
    }

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
            'match_score' => 'decimal:2',
            'status' => PropertyMatchStatus::class,
        ];
    }

    protected static function newFactory(): PropertyMatchFactory
    {
        return PropertyMatchFactory::new();
    }
}
