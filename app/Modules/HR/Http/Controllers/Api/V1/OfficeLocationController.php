<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\HR\Actions\UpdateOfficeLocation;
use App\Modules\HR\Domain\WorkSchedule;
use App\Modules\HR\Http\Requests\OfficeLocationRequest;
use App\Modules\HR\Http\Resources\OfficeLocationResource;
use App\Modules\HR\Models\AttendanceSetting;
use Illuminate\Http\JsonResponse;

class OfficeLocationController extends Controller
{
    /**
     * The current tenant's geofenced office location (platform defaults until set).
     */
    public function show(): OfficeLocationResource
    {
        $this->authorize('view', AttendanceSetting::class);

        $setting = AttendanceSetting::query()->first() ?? tap(new AttendanceSetting, function (AttendanceSetting $model): void {
            $default = WorkSchedule::default();
            $model->label = 'Head Office';
            $model->radius_meters = 500;
            $model->work_starts_at = $default->startsAt;
            $model->work_ends_at = $default->endsAt;
        });

        return OfficeLocationResource::make($setting);
    }

    /**
     * Create or update the office location. Manager-only.
     */
    public function update(OfficeLocationRequest $request, UpdateOfficeLocation $action): JsonResponse
    {
        $this->authorize('manageLocation', AttendanceSetting::class);

        // PUT is an idempotent upsert — always 200, even on the first (creating) call.
        return OfficeLocationResource::make($action->handle($request->toData()))
            ->response()
            ->setStatusCode(200);
    }
}
