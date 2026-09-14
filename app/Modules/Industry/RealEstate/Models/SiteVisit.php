<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Database\Factories\SiteVisitFactory;
use App\Modules\Industry\RealEstate\Domain\SiteVisitStatus;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A scheduled (or completed/cancelled) physical visit a lead makes to see a
 * {@see Unit} or a whole {@see RealEstateProject}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $lead_id
 * @property int|null $unit_id
 * @property int|null $project_id
 * @property Carbon $scheduled_at
 * @property SiteVisitStatus $status
 * @property int|null $conducted_by_employee_id
 * @property string|null $feedback
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['lead_id', 'unit_id', 'project_id', 'scheduled_at', 'conducted_by_employee_id', 'feedback'])]
class SiteVisit extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<SiteVisitFactory> */
    use HasFactory;

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** @return BelongsTo<RealEstateProject, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    /** @return BelongsTo<Employee, $this> */
    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'conducted_by_employee_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'status' => SiteVisitStatus::class,
        ];
    }

    protected static function newFactory(): SiteVisitFactory
    {
        return SiteVisitFactory::new();
    }
}
