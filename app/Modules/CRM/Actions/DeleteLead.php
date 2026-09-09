<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Models\Lead;
use App\Modules\Tenant\Context\TenantContext;

class DeleteLead
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Lead $lead): void
    {
        $this->assertTenantOwns($lead);

        $lead->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
