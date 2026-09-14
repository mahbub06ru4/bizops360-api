<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Database\Factories\PropertyRequirementFactory;
use App\Modules\Industry\RealEstate\Domain\RequirementPurpose;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * What a {@see Lead} says they are looking for — budget range, preferred
 * areas (free-form strings; no geo lookup in Phase 1), unit type, and minimum
 * bedrooms. Feeds the rule-based {@see \App\Modules\Industry\RealEstate\Actions\MatchRequirementToUnits}
 * scorer, never an AI/NLP model (that is roadmap Phase 4).
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $lead_id
 * @property string|null $budget_min
 * @property string|null $budget_max
 * @property string|null $preferred_locations
 * @property string|null $unit_type
 * @property int|null $bedrooms_min
 * @property RequirementPurpose $purpose
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['budget_min', 'budget_max', 'preferred_locations', 'unit_type', 'bedrooms_min', 'purpose', 'notes'])]
class PropertyRequirement extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<PropertyRequirementFactory> */
    use HasFactory;

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return HasMany<PropertyMatch, $this> */
    public function matches(): HasMany
    {
        return $this->hasMany(PropertyMatch::class, 'requirement_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'bedrooms_min' => 'integer',
            'purpose' => RequirementPurpose::class,
        ];
    }

    protected static function newFactory(): PropertyRequirementFactory
    {
        return PropertyRequirementFactory::new();
    }
}
