<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions\Concerns;

use App\Modules\HR\Domain\WorkSchedule;
use App\Modules\HR\Models\AttendanceSetting;

/**
 * Provides the current tenant's {@see WorkSchedule}, falling back to platform
 * defaults when the tenant has not configured one.
 */
trait ResolvesWorkSchedule
{
    protected function workSchedule(): WorkSchedule
    {
        return AttendanceSetting::query()->first()?->schedule() ?? WorkSchedule::default();
    }
}
