<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\User;
use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Data\InvoiceRefundData;
use App\Modules\Finance\Domain\InvoiceBalance;
use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceRefund;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Refunds money against an invoice, then recomputes its running refunded total
 * and status. A refund can never exceed the payments received net of prior
 * refunds.
 */
class RefundInvoice
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Invoice $invoice, InvoiceRefundData $data, User $recorder): InvoiceRefund
    {
        $this->assertTenantOwns($invoice);

        $refund = Money::fromDecimal($data->amount);

        if (! $refund->isPositive()) {
            throw ValidationException::withMessages(['amount' => 'A refund must be greater than zero.']);
        }

        return DB::transaction(function () use ($invoice, $data, $recorder, $refund): InvoiceRefund {
            /** @var Invoice $locked */
            $locked = Invoice::query()->lockForUpdate()->findOrFail($invoice->getKey());

            if ($data->paymentId !== null
                && ! $locked->payments()->whereKey($data->paymentId)->exists()) {
                throw ValidationException::withMessages([
                    'payment_id' => 'That payment does not belong to this invoice.',
                ]);
            }

            $currentNetPaid = Money::fromDecimal($locked->amount_paid)
                ->subtract(Money::fromDecimal($locked->amount_refunded));

            if ($refund->greaterThan($currentNetPaid)) {
                throw ValidationException::withMessages([
                    'amount' => 'This refund exceeds the amount paid on the invoice.',
                ]);
            }

            $newRefunded = Money::fromDecimal($locked->amount_refunded)->add($refund);

            $record = new InvoiceRefund([
                'invoice_id' => $locked->getKey(),
                'payment_id' => $data->paymentId,
                'amount' => $data->amount,
                'refunded_on' => $data->refundedOn,
                'method' => $data->method,
                'reason' => $data->reason,
            ]);
            $record->tenant_id = (int) $locked->tenant_id;
            $record->recorded_by = $recorder->getKey();
            $record->save();

            $balance = new InvoiceBalance(
                Money::fromDecimal($locked->amount),
                Money::fromDecimal($locked->amount_paid),
                $newRefunded,
            );

            $locked->amount_refunded = $newRefunded->toDecimalString();
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
