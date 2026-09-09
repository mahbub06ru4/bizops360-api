<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Models\User;
use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\CustomerData;
use App\Modules\CRM\Models\Customer;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

class CreateCustomer
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(CustomerData $data, User $creator): Customer
    {
        $this->assertReferenceOwned($data->ownerEmployeeId, Employee::class);

        $customer = new Customer($data->toAttributes());
        $customer->tenant_id = $this->currentTenantId();
        $customer->created_by = $creator->getKey();
        $customer->save();

        return $customer->load('owner');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
