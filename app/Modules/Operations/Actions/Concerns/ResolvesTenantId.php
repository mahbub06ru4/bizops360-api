<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions\Concerns;

use App\Modules\Tenant\Context\TenantContext;

/**
 * Minimal tenant helper for read/aggregation actions that query at the
 * table level and therefore must filter by tenant explicitly.
 */
trait ResolvesTenantId
{
    abstract protected function tenantContext(): TenantContext;

    protected function currentTenantId(): int
    {
        return (int) $this->tenantContext()->tenant()->getKey();
    }
}
