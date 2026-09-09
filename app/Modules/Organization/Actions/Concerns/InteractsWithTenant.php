<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions\Concerns;

use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Shared tenant guards for Organization actions. The global scope is a safety net;
 * these checks are the authoritative guarantee that an action never touches or
 * references another tenant's row.
 */
trait InteractsWithTenant
{
    abstract protected function tenantContext(): TenantContext;

    protected function currentTenantId(): int
    {
        return (int) $this->tenantContext()->tenant()->getKey();
    }

    /**
     * Assert the given model belongs to the tenant bound to the current context.
     */
    protected function assertTenantOwns(Model $model): void
    {
        if ((int) $model->getAttribute('tenant_id') !== $this->currentTenantId()) {
            throw (new ModelNotFoundException)->setModel($model::class, [$model->getKey()]);
        }
    }
}
