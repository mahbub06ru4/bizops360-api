<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Models\Expense;
use App\Modules\Tenant\Context\TenantContext;

class DeleteExpense
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Expense $expense): void
    {
        $this->assertTenantOwns($expense);

        $expense->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
