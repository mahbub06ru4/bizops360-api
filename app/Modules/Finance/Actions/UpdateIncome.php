<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Data\IncomeData;
use App\Modules\Finance\Models\Income;
use App\Modules\Tenant\Context\TenantContext;

class UpdateIncome
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Income $income, IncomeData $data): Income
    {
        $this->assertTenantOwns($income);
        $this->assertReferenceOwned($data->customerId, Customer::class);

        $income->fill($data->toAttributes())->save();

        return $income->refresh()->load('customer');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
