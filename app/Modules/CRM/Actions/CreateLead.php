<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Models\User;
use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\LeadData;
use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

class CreateLead
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(LeadData $data, User $creator): Lead
    {
        $this->assertReferenceOwned($data->ownerEmployeeId, Employee::class);

        $lead = new Lead($data->toAttributes());
        $lead->tenant_id = $this->currentTenantId();
        $lead->created_by = $creator->getKey();
        $lead->stage = LeadStage::New;
        $lead->save();

        return $lead->load('owner');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
