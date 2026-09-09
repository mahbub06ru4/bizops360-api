<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

/**
 * Voids an invoice. Not permitted once money has been received against it —
 * refund the payments first.
 */
class VoidInvoice
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Invoice $invoice): Invoice
    {
        $this->assertTenantOwns($invoice);

        if ($invoice->status === InvoiceStatus::Void) {
            return $invoice;
        }

        if ($invoice->balance()->netPaid()->isPositive()) {
            throw ValidationException::withMessages([
                'invoice' => 'Refund the payments on this invoice before voiding it.',
            ]);
        }

        $invoice->status = InvoiceStatus::Void;
        $invoice->save();

        return $invoice->refresh()->load('customer');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
