<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\CustomerData;
use App\Modules\CRM\Models\Customer;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

class UpdateCustomer
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Customer $customer, CustomerData $data): Customer
    {
        $this->assertTenantOwns($customer);
        $this->assertReferenceOwned($data->ownerEmployeeId, Employee::class);

        $customer->fill($data->toAttributes())->save();

        return $customer->refresh()->load('owner');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
