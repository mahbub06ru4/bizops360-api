<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain;

use Illuminate\Support\Facades\DB;

/**
 * Allocates the next per-tenant invoice number (`INV-000001`, `INV-000002`, …).
 * Call inside the same transaction as the insert; the `(tenant_id, number)`
 * unique index is the backstop against a race.
 */
class GenerateInvoiceNumber
{
    public function handle(int $tenantId): string
    {
        $count = DB::table('invoices')->where('tenant_id', $tenantId)->count();

        return 'INV-'.str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
    }
}
