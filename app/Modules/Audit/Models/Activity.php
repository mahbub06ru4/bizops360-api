<?php

declare(strict_types=1);

namespace App\Modules\Audit\Models;

use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * Tenant-scoped activity log (spec §10 — audit logs on key business tables).
 * `config('activitylog.activity_model')` points here instead of the package
 * default, so every logged activity is tenant-isolated the same way any other
 * business record is: {@see BelongsToTenant} auto-fills `tenant_id` from the
 * current {@see TenantContext} and scopes reads.
 *
 * @property int|null $tenant_id
 */
class Activity extends SpatieActivity
{
    use BelongsToTenant;
}
