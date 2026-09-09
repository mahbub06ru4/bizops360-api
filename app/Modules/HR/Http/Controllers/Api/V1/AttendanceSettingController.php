<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\HR\Actions\UpdateAttendanceSetting;
use App\Modules\HR\Domain\WorkSchedule;
use App\Modules\HR\Http\Requests\AttendanceSettingRequest;
use App\Modules\HR\Http\Resources\AttendanceSettingResource;
use App\Modules\HR\Models\AttendanceSetting;
use Illuminate\Http\JsonResponse;

class AttendanceSettingController extends Controller
{
    /**
     * The current tenant's working-hours configuration (platform defaults until set).
     */
    public function show(): AttendanceSettingResource
    {
        $this->authorize('view', AttendanceSetting::class);

        $setting = AttendanceSetting::query()->first() ?? tap(new AttendanceSetting, function (AttendanceSetting $model): void {
            $default = WorkSchedule::default();
            $model->work_starts_at = $default->startsAt;
            $model->work_ends_at = $default->endsAt;
            $model->grace_minutes = $default->graceMinutes;
        });

        return AttendanceSettingResource::make($setting);
    }

    /**
     * Create or update the working-hours configuration.
     */
    public function update(AttendanceSettingRequest $request, UpdateAttendanceSetting $action): JsonResponse
    {
        $this->authorize('manage', AttendanceSetting::class);

        // PUT is an idempotent upsert — always 200, even on the first (creating) call.
        return AttendanceSettingResource::make($action->handle($request->toData()))
            ->response()
            ->setStatusCode(200);
    }
}
