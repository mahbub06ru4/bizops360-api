<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Data\ExpenseData;
use App\Modules\Finance\Models\Expense;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

class UpdateExpense
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Expense $expense, ExpenseData $data): Expense
    {
        $this->assertTenantOwns($expense);
        $this->assertReferenceOwned($data->employeeId, Employee::class);

        $expense->fill($data->toAttributes())->save();

        return $expense->refresh()->load('employee');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
