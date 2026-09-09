<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Models\Invoice;
use Illuminate\Support\Carbon;

/**
 * Every invoice still owing money, each with its outstanding balance and an
 * ageing bucket derived from the due date (or issue date when none is set).
 */
class BuildOutstandingInvoices
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $today = Carbon::today();
        $statuses = array_values(array_filter(
            InvoiceStatus::values(),
            static fn (string $status): bool => InvoiceStatus::from($status)->isOutstanding(),
        ));

        $buckets = ['current' => Money::zero(), '1_30' => Money::zero(), '31_60' => Money::zero(), '61_90' => Money::zero(), 'over_90' => Money::zero()];
        $rows = [];
        $total = Money::zero();

        $invoices = Invoice::query()
            ->whereIn('status', $statuses)
            ->orderBy('due_date')
            ->orderBy('issue_date')
            ->get();

        foreach ($invoices as $invoice) {
            $due = $invoice->balance()->due();

            if ($due->isZero()) {
                continue;
            }

            $reference = $invoice->due_date ?? $invoice->issue_date;
            $daysOverdue = $reference->isFuture() ? 0 : $reference->diffInDays($today);
            $bucket = match (true) {
                $daysOverdue <= 0 => 'current',
                $daysOverdue <= 30 => '1_30',
                $daysOverdue <= 60 => '31_60',
                $daysOverdue <= 90 => '61_90',
                default => 'over_90',
            };

            $buckets[$bucket] = $buckets[$bucket]->add($due);
            $total = $total->add($due);

            $rows[] = [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'customer_id' => $invoice->customer_id,
                'customer_name' => $invoice->customer_name,
                'status' => $invoice->status->value,
                'issue_date' => $invoice->issue_date->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'amount' => Money::fromDecimal($invoice->amount)->toDecimalString(),
                'amount_due' => $due->toDecimalString(),
                'days_overdue' => (int) $daysOverdue,
                'ageing_bucket' => $bucket,
            ];
        }

        return [
            'total_outstanding' => $total->toDecimalString(),
            'invoice_count' => count($rows),
            'ageing' => array_map(static fn (Money $m): string => $m->toDecimalString(), $buckets),
            'invoices' => $rows,
        ];
    }
}
