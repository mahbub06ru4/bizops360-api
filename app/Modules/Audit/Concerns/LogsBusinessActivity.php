<?php

declare(strict_types=1);

namespace App\Modules\Audit\Concerns;

use App\Modules\Audit\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Drop-in audit logging for a tenant-owned business model (spec §10 — "audit
 * logs on key business tables"). Logs every changed attribute on create/
 * update/delete, skips no-op saves, and tags the log with the model's own
 * table name so `GET /api/v1/activity?log_name=invoices` etc. just works.
 *
 * Add `use LogsBusinessActivity;` to the model — nothing else required. The
 * activity row itself is tenant-scoped by {@see Activity}.
 */
trait LogsBusinessActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontLogEmptyChanges()
            ->useLogName($this->getTable());
    }
}
