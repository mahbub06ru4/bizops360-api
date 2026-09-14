<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\PropertyRequirementData;
use App\Modules\Industry\RealEstate\Models\PropertyRequirement;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Records what a {@see Lead} is looking for — the entry point of the Phase 1
 * commercial loop (roadmap §7: Lead → Requirement → Property Match → ...).
 */
class CreatePropertyRequirement
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Lead $lead, PropertyRequirementData $data): PropertyRequirement
    {
        $this->assertTenantOwns($lead);

        $requirement = new PropertyRequirement($data->toAttributes());
        $requirement->tenant_id = (int) $lead->tenant_id;
        $requirement->lead_id = $lead->getKey();
        $requirement->save();

        return $requirement;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
