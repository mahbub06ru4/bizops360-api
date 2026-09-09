<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

/**
 * Marks a draft invoice as sent to the customer.
 */
class SendInvoice
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Invoice $invoice): Invoice
    {
        $this->assertTenantOwns($invoice);

        if ($invoice->status !== InvoiceStatus::Draft) {
            throw ValidationException::withMessages([
                'invoice' => "Only a draft invoice can be sent; this one is [{$invoice->status->value}].",
            ]);
        }

        $invoice->status = InvoiceStatus::Sent;
        $invoice->save();

        return $invoice->refresh()->load('customer');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
