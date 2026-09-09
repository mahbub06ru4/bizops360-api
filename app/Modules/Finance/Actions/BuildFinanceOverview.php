<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Domain\ProfitAndLoss;
use App\Modules\Finance\Models\Expense;
use App\Modules\Finance\Models\Income;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoicePayment;
use App\Modules\Finance\Models\InvoiceRefund;
use Illuminate\Support\Carbon;

/**
 * Tenant-wide finance headline figures. Every query is tenant-scoped by the
 * models' global scope.
 *
 * Total income = recorded incomes + invoice payments − invoice refunds, so an
 * invoice payment is never also counted as a standalone income row.
 */
class BuildFinanceOverview
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        $allTime = $this->figuresSince(null);
        $thisMonth = $this->figuresSince($monthStart);

        $outstanding = Invoice::query()
            ->whereIn('status', $this->outstandingStatuses())
            ->get(['amount', 'amount_paid', 'amount_refunded']);

        $receivable = Money::zero();
        foreach ($outstanding as $invoice) {
            $receivable = $receivable->add($invoice->balance()->due());
        }

        return [
            'all_time' => $allTime,
            'this_month' => $thisMonth,
            'receivables' => [
                'outstanding_invoices' => $outstanding->count(),
                'outstanding_amount' => $receivable->toDecimalString(),
            ],
            'counts' => [
                'incomes' => Income::query()->count(),
                'expenses' => Expense::query()->count(),
                'invoices' => Invoice::query()->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function figuresSince(?Carbon $since): array
    {
        $income = Money::fromDecimal((string) Income::query()
            ->when($since, fn ($q) => $q->where('received_on', '>=', $since))
            ->sum('amount'));

        $payments = Money::fromDecimal((string) InvoicePayment::query()
            ->when($since, fn ($q) => $q->where('paid_on', '>=', $since))
            ->sum('amount'));

        $refunds = Money::fromDecimal((string) InvoiceRefund::query()
            ->when($since, fn ($q) => $q->where('refunded_on', '>=', $since))
            ->sum('amount'));

        $expense = Money::fromDecimal((string) Expense::query()
            ->when($since, fn ($q) => $q->where('spent_on', '>=', $since))
            ->sum('amount'));

        $totalIncome = $income->add($payments)->subtract($refunds);

        return (new ProfitAndLoss($totalIncome, $expense))->toArray();
    }

    /**
     * @return list<string>
     */
    private function outstandingStatuses(): array
    {
        return array_values(array_filter(
            InvoiceStatus::values(),
            static fn (string $status): bool => InvoiceStatus::from($status)->isOutstanding(),
        ));
    }
}
