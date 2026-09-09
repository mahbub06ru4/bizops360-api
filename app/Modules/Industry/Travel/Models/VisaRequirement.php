<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Models;

use App\Modules\Industry\Travel\Database\Factories\VisaRequirementFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One checklist line on a {@see VisaApplication} — a document the mission
 * requires and whether the agency has collected it yet.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $visa_application_id
 * @property string $name
 * @property bool $is_mandatory
 * @property bool $collected
 * @property Carbon|null $collected_on
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'is_mandatory', 'collected', 'collected_on', 'note'])]
class VisaRequirement extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<VisaRequirementFactory> */
    use HasFactory;

    /** @return BelongsTo<VisaApplication, $this> */
    public function visaApplication(): BelongsTo
    {
        return $this->belongsTo(VisaApplication::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'collected' => 'boolean',
            'collected_on' => 'date',
        ];
    }

    protected static function newFactory(): VisaRequirementFactory
    {
        return VisaRequirementFactory::new();
    }
}
