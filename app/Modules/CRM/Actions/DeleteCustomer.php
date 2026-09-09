<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Models\Customer;
use App\Modules\Tenant\Context\TenantContext;

class DeleteCustomer
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Customer $customer): void
    {
        $this->assertTenantOwns($customer);

        $customer->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
