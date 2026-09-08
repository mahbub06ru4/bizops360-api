<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Models\Concerns;

use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\Tenant\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply to every tenant-owned model.
 *
 * - adds the {@see TenantScope} global scope
 * - auto-fills `tenant_id` from the current context on create
 *
 * This is a safety net. The authoritative check lives in the Action + Policy.
 *
 * @property int $tenant_id
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            if ($model->getAttribute('tenant_id') !== null) {
                return;
            }

            $context = app(TenantContext::class);

            if ($context->has()) {
                $model->setAttribute('tenant_id', $context->id());
            }
        });
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
