<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions\Concerns;

use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Shared tenant guards for Operations actions. The global scope is a safety net;
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

    protected function assertTenantOwns(Model $model): void
    {
        if ((int) $model->getAttribute('tenant_id') !== $this->currentTenantId()) {
            throw (new ModelNotFoundException)->setModel($model::class, [$model->getKey()]);
        }
    }

    /**
     * Load a referenced record by id and assert it belongs to the current tenant.
     * A null id is a no-op (the reference is optional).
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function assertReferenceOwned(?int $id, string $modelClass): void
    {
        if ($id === null) {
            return;
        }

        $instance = new $modelClass;
        /** @var Model|null $found */
        $found = $instance->newQuery()->find($id);

        if ($found === null) {
            throw (new ModelNotFoundException)->setModel($modelClass, [$id]);
        }

        $this->assertTenantOwns($found);
    }
}
