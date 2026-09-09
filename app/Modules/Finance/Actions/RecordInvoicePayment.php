<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\User;
use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Data\InvoicePaymentData;
use App\Modules\Finance\Domain\InvoiceBalance;
use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoicePayment;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Records a payment against an invoice, then recomputes its running paid total
 * and status. Rejects a payment that would take the invoice past its balance.
 */
class RecordInvoicePayment
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Invoice $invoice, InvoicePaymentData $data, User $recorder): InvoicePayment
    {
        $this->assertTenantOwns($invoice);

        if ($invoice->status->value === 'void') {
            throw ValidationException::withMessages(['invoice' => 'A voided invoice cannot take payments.']);
        }

        $payment = Money::fromDecimal($data->amount);

        if (! $payment->isPositive()) {
            throw ValidationException::withMessages(['amount' => 'A payment must be greater than zero.']);
        }

        return DB::transaction(function () use ($invoice, $data, $recorder, $payment): InvoicePayment {
            /** @var Invoice $locked */
            $locked = Invoice::query()->lockForUpdate()->findOrFail($invoice->getKey());

            $newPaid = Money::fromDecimal($locked->amount_paid)->add($payment);
            $balance = new InvoiceBalance(
                Money::fromDecimal($locked->amount),
                $newPaid,
                Money::fromDecimal($locked->amount_refunded),
            );

            if ($balance->netPaid()->greaterThan(Money::fromDecimal($locked->amount))) {
                throw ValidationException::withMessages([
                    'amount' => 'This payment exceeds the amount still due on the invoice.',
                ]);
            }

            $record = new InvoicePayment([
                'invoice_id' => $locked->getKey(),
                'customer_id' => $locked->customer_id,
                'amount' => $data->amount,
                'paid_on' => $data->paidOn,
                'method' => $data->method,
                'reference' => $data->reference,
                'note' => $data->note,
            ]);
            $record->tenant_id = (int) $locked->tenant_id;
            $record->recorded_by = $recorder->getKey();
            $record->save();

            $locked->amount_paid = $newPaid->toDecimalString();
            $locked->status = $balance->statusFrom($locked->status);
            $locked->save();

            return $record;
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
