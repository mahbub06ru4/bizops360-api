<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Scopes;

use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that constrains every query on a tenant-owned model to the tenant
 * bound to the current context. When no tenant is bound (console, unauthenticated
 * requests) the scope is a no-op — Actions and policies remain the real guard.
 *
 * @implements Scope<Model>
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if (! $context->has()) {
            return;
        }

        $builder->where(
            $model->qualifyColumn('tenant_id'),
            $context->id(),
        );
    }
}
