<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Data\InvoiceData;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

/**
 * Edits an invoice's terms. Only permitted while it is still a draft or sent
 * and no money has moved against it.
 */
class UpdateInvoice
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Invoice $invoice, InvoiceData $data): Invoice
    {
        $this->assertTenantOwns($invoice);

        if (! $invoice->status->isEditable()) {
            throw ValidationException::withMessages([
                'invoice' => "An invoice with status [{$invoice->status->value}] can no longer be edited.",
            ]);
        }

        if ($invoice->payments()->exists() || $invoice->refunds()->exists()) {
            throw ValidationException::withMessages([
                'invoice' => 'An invoice with recorded payments can no longer be edited.',
            ]);
        }

        $this->assertReferenceOwned($data->customerId, Customer::class);

        $attributes = $data->toAttributes();

        if ($data->customerId !== null && $data->customerId !== $invoice->customer_id) {
            $customer = Customer::query()->find($data->customerId);
            $attributes['customer_name'] = $customer !== null ? $customer->name : $invoice->customer_name;
        }

        $invoice->fill($attributes)->save();

        return $invoice->refresh()->load('customer');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
