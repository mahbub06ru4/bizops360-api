<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Domain;

use Illuminate\Support\Facades\DB;

/**
 * Allocates the next per-tenant booking reference (`BKG-000001`, …). Call inside
 * the same transaction as the insert; the `(tenant_id, reference)` unique index
 * is the backstop against a race.
 */
class GenerateBookingReference
{
    public function handle(int $tenantId): string
    {
        $count = DB::table('bookings')->where('tenant_id', $tenantId)->count();

        return 'BKG-'.str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
    }
}
