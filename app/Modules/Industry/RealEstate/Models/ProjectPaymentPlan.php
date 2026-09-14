<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\ProjectPaymentPlanFactory;
use App\Modules\Industry\RealEstate\Domain\PaymentPlanFrequency;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An installment template a buyer can choose on a {@see RealEstateProject}
 * (e.g. "20% down, 36 monthly installments"). Phase 1 will generate a buyer's
 * concrete installment schedule from one of these; this row is just the
 * template.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $project_id
 * @property string $name
 * @property string $down_payment_percent
 * @property int $installment_count
 * @property PaymentPlanFrequency $installment_frequency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'name', 'down_payment_percent', 'installment_count', 'installment_frequency'])]
class ProjectPaymentPlan extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ProjectPaymentPlanFactory> */
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
            'down_payment_percent' => 'decimal:2',
            'installment_count' => 'integer',
            'installment_frequency' => PaymentPlanFrequency::class,
        ];
    }

    protected static function newFactory(): ProjectPaymentPlanFactory
    {
        return ProjectPaymentPlanFactory::new();
    }
}
