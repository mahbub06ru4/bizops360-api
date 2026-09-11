<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\User;
use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Data\ExpenseDecisionData;
use App\Modules\Finance\Domain\ExpenseStatus;
use App\Modules\Finance\Models\Expense;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RejectExpense
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Expense $expense, User $approver, ExpenseDecisionData $data): Expense
    {
        $this->assertTenantOwns($expense);

        if (! $expense->status->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only a pending expense can be rejected.',
            ]);
        }

        $expense->status = ExpenseStatus::Rejected;
        $expense->approved_by = $approver->getKey();
        $expense->approved_at = Carbon::now();
        $expense->decision_note = $data->note;
        $expense->save();

        return $expense->load(['employee', 'approver']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
