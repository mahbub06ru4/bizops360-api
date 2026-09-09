<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions\Concerns;

use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Shared tenant guards for Travel actions. The global scope is a safety net;
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
     * @param  class-string<Model>  $modelClass
     */
    protected function assertReferenceOwned(?int $id, string $modelClass): void
    {
        if ($id === null) {
            return;
        }

        $this->assertOwnedModel($id, $modelClass);
    }

    /**
     * Load a model by id and assert it belongs to the current tenant, returning
     * it for the caller to use.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @return TModel
     */
    protected function assertOwnedModel(int $id, string $modelClass): Model
    {
        $instance = new $modelClass;
        /** @var TModel|null $found */
        $found = $instance->newQuery()->find($id);

        if ($found === null) {
            throw (new ModelNotFoundException)->setModel($modelClass, [$id]);
        }

        $this->assertTenantOwns($found);

        return $found;
    }
}
