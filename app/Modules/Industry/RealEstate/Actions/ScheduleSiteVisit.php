<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\SiteVisitData;
use App\Modules\Industry\RealEstate\Domain\SiteVisitStatus;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Industry\RealEstate\Models\SiteVisit;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class ScheduleSiteVisit
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Lead $lead, SiteVisitData $data): SiteVisit
    {
        $this->assertTenantOwns($lead);
        $this->assertReferenceOwned($data->unitId, Unit::class);
        $this->assertReferenceOwned($data->projectId, RealEstateProject::class);
        $this->assertReferenceOwned($data->conductedByEmployeeId, Employee::class);

        if ($data->unitId === null && $data->projectId === null) {
            throw ValidationException::withMessages([
                'unit_id' => 'Provide a unit or a project to schedule the visit for.',
            ]);
        }

        $visit = new SiteVisit($data->toAttributes());
        $visit->tenant_id = (int) $lead->tenant_id;
        $visit->lead_id = $lead->getKey();
        $visit->status = SiteVisitStatus::Scheduled;
        $visit->save();

        return $visit;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
