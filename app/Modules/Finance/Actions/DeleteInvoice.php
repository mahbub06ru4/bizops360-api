<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Modules\Finance\Actions\Concerns\InteractsWithTenant;
use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

/**
 * Deletes an invoice. Only a draft with no payments may be deleted; anything
 * further along is voided instead to keep the audit trail.
 */
class DeleteInvoice
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Invoice $invoice): void
    {
        $this->assertTenantOwns($invoice);

        if ($invoice->status !== InvoiceStatus::Draft || $invoice->payments()->exists()) {
            throw ValidationException::withMessages([
                'invoice' => 'Only a draft invoice with no payments can be deleted; void it instead.',
            ]);
        }

        $invoice->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
