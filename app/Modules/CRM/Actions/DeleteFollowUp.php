<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\Tenant\Context\TenantContext;

class DeleteFollowUp
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(FollowUp $followUp): void
    {
        $this->assertTenantOwns($followUp);

        $followUp->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
