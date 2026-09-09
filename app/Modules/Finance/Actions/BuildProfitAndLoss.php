<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Domain\ExpenseCategory;
use App\Modules\Finance\Domain\IncomeCategory;
use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Domain\ProfitAndLoss;
use App\Modules\Finance\Models\Expense;
use App\Modules\Finance\Models\Income;
use App\Modules\Finance\Models\InvoicePayment;
use App\Modules\Finance\Models\InvoiceRefund;
use Illuminate\Support\Carbon;

/**
 * Profit-and-loss for a date range: income (recorded incomes + invoice payments
 * − refunds) against expenses, broken down by category.
 */
class BuildProfitAndLoss
{
    /**
     * @return array<string, mixed>
     */
    public function handle(Carbon $from, Carbon $to): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $recordedIncome = Money::fromDecimal((string) Income::query()
            ->whereBetween('received_on', [$from, $to])->sum('amount'));
        $payments = Money::fromDecimal((string) InvoicePayment::query()
            ->whereBetween('paid_on', [$from, $to])->sum('amount'));
        $refunds = Money::fromDecimal((string) InvoiceRefund::query()
            ->whereBetween('refunded_on', [$from, $to])->sum('amount'));

        $totalIncome = $recordedIncome->add($payments)->subtract($refunds);

        $expenseByCategory = [];
        $totalExpense = Money::zero();
        foreach (ExpenseCategory::values() as $category) {
            $sum = Money::fromDecimal((string) Expense::query()
                ->where('category', $category)
                ->whereBetween('spent_on', [$from, $to])->sum('amount'));
            $expenseByCategory[$category] = $sum->toDecimalString();
            $totalExpense = $totalExpense->add($sum);
        }

        $incomeByCategory = [];
        foreach (IncomeCategory::values() as $category) {
            $incomeByCategory[$category] = Money::fromDecimal((string) Income::query()
                ->where('category', $category)
                ->whereBetween('received_on', [$from, $to])->sum('amount'))->toDecimalString();
        }
        $incomeByCategory['invoice_payments'] = $payments->toDecimalString();
        $incomeByCategory['refunds'] = $refunds->toDecimalString();

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => (new ProfitAndLoss($totalIncome, $totalExpense))->toArray(),
            'income_by_category' => $incomeByCategory,
            'expense_by_category' => $expenseByCategory,
        ];
    }
}
