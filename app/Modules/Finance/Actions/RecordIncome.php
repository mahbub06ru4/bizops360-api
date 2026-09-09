<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Data\IncomeData;
use App\Modules\Finance\Models\Income;
use App\Modules\Tenant\Context\TenantContext;

class RecordIncome
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(IncomeData $data, User $recorder): Income
    {
        $this->assertReferenceOwned($data->customerId, Customer::class);

        $income = new Income($data->toAttributes());
        $income->tenant_id = $this->currentTenantId();
        $income->recorded_by = $recorder->getKey();
        $income->save();

        return $income->load('customer');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
