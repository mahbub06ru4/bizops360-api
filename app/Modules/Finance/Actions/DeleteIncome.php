<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Models\Income;
use App\Modules\Tenant\Context\TenantContext;

class DeleteIncome
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Income $income): void
    {
        $this->assertTenantOwns($income);

        $income->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
