<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\User;
use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Data\ExpenseData;
use App\Modules\Finance\Domain\ExpenseStatus;
use App\Modules\Finance\Models\Expense;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

class RecordExpense
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(ExpenseData $data, User $recorder): Expense
    {
        $this->assertReferenceOwned($data->employeeId, Employee::class);

        $expense = new Expense($data->toAttributes());
        $expense->tenant_id = $this->currentTenantId();
        $expense->recorded_by = $recorder->getKey();
        $expense->status = ExpenseStatus::Pending;
        $expense->save();

        return $expense->load('employee');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
