<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Models\Invoice;

/**
 * Outstanding balance per customer — the sum of every unpaid invoice's due
 * amount, grouped by the customer it was raised against.
 */
class BuildCustomerDues
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $statuses = array_values(array_filter(
            InvoiceStatus::values(),
            static fn (string $status): bool => InvoiceStatus::from($status)->isOutstanding(),
        ));

        /** @var array<string, array{customer_id: int|null, customer_name: string, invoice_count: int, due: Money}> $groups */
        $groups = [];
        $total = Money::zero();

        foreach (Invoice::query()->whereIn('status', $statuses)->get() as $invoice) {
            $due = $invoice->balance()->due();

            if ($due->isZero()) {
                continue;
            }

            $key = $invoice->customer_id !== null ? (string) $invoice->customer_id : 'name:'.$invoice->customer_name;
            $groups[$key] ??= [
                'customer_id' => $invoice->customer_id,
                'customer_name' => $invoice->customer_name,
                'invoice_count' => 0,
                'due' => Money::zero(),
            ];
            $groups[$key]['invoice_count']++;
            $groups[$key]['due'] = $groups[$key]['due']->add($due);
            $total = $total->add($due);
        }

        $customers = array_map(static fn (array $group): array => [
            'customer_id' => $group['customer_id'],
            'customer_name' => $group['customer_name'],
            'invoice_count' => $group['invoice_count'],
            'amount_due' => $group['due']->toDecimalString(),
        ], array_values($groups));

        usort($customers, static fn (array $a, array $b): int => (float) $b['amount_due'] <=> (float) $a['amount_due']);

        return [
            'total_due' => $total->toDecimalString(),
            'customer_count' => count($customers),
            'customers' => $customers,
        ];
    }
}
