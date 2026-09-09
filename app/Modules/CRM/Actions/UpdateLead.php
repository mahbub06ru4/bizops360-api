<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\LeadData;
use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

class UpdateLead
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Lead $lead, LeadData $data): Lead
    {
        $this->assertTenantOwns($lead);
        $this->assertReferenceOwned($data->ownerEmployeeId, Employee::class);

        $lead->fill($data->toAttributes())->save();

        return $lead->refresh()->load('owner');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
