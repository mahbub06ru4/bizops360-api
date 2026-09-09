<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Domain\ProfitAndLoss;
use App\Modules\Finance\Models\Expense;
use App\Modules\Finance\Models\Income;
use App\Modules\Finance\Models\InvoicePayment;
use App\Modules\Finance\Models\InvoiceRefund;
use Illuminate\Support\Carbon;

/**
 * Month-by-month income, expense and profit for a calendar year.
 */
class BuildMonthlyFinanceReport
{
    /**
     * @return array<string, mixed>
     */
    public function handle(int $year): array
    {
        $months = [];
        $yearIncome = Money::zero();
        $yearExpense = Money::zero();

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $income = Money::fromDecimal((string) Income::query()
                ->whereBetween('received_on', [$start, $end])->sum('amount'))
                ->add(Money::fromDecimal((string) InvoicePayment::query()
                    ->whereBetween('paid_on', [$start, $end])->sum('amount')))
                ->subtract(Money::fromDecimal((string) InvoiceRefund::query()
                    ->whereBetween('refunded_on', [$start, $end])->sum('amount')));

            $expense = Money::fromDecimal((string) Expense::query()
                ->whereBetween('spent_on', [$start, $end])->sum('amount'));

            $yearIncome = $yearIncome->add($income);
            $yearExpense = $yearExpense->add($expense);

            $months[] = [
                'month' => $month,
                'label' => $start->format('M'),
                ...(new ProfitAndLoss($income, $expense))->toArray(),
            ];
        }

        return [
            'year' => $year,
            'summary' => (new ProfitAndLoss($yearIncome, $yearExpense))->toArray(),
            'months' => $months,
        ];
    }
}
