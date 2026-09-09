<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Data\InvoiceData;
use App\Modules\Finance\Domain\GenerateInvoiceNumber;
use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Raises a new draft invoice against a CRM customer, snapshotting the customer
 * name so the invoice still reads correctly if the customer is later removed.
 */
class CreateInvoice
{
    use InteractsWithTenant;

    public function __construct(
        private readonly TenantContext $context,
        private readonly GenerateInvoiceNumber $numbers,
    ) {}

    public function handle(InvoiceData $data, User $creator): Invoice
    {
        $this->assertReferenceOwned($data->customerId, Customer::class);

        $tenantId = $this->currentTenantId();
        $customer = $data->customerId !== null ? Customer::query()->find($data->customerId) : null;
        $customerName = $customer !== null ? $customer->name : 'Walk-in customer';

        return DB::transaction(function () use ($data, $creator, $tenantId, $customerName): Invoice {
            $invoice = new Invoice($data->toAttributes());
            $invoice->tenant_id = $tenantId;
            $invoice->created_by = $creator->getKey();
            $invoice->number = $this->numbers->handle($tenantId);
            $invoice->customer_name = $customerName;
            $invoice->status = InvoiceStatus::Draft;
            $invoice->amount_paid = '0.00';
            $invoice->amount_refunded = '0.00';
            $invoice->save();

            return $invoice->load('customer');
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
