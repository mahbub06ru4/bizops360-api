<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Operations\Actions\BuildDepartmentPerformance;
use App\Modules\Operations\Actions\BuildEmployeeWorkload;
use App\Modules\Operations\Actions\BuildOperationsOverview;
use App\Modules\Operations\Http\Resources\MetricResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only manager dashboards for the current tenant. Requires
 * `operations.view_dashboard`.
 */
class OperationsOverviewController extends Controller
{
    /**
     * Tenant-wide task and project summary.
     */
    public function overview(Request $request, BuildOperationsOverview $action): MetricResource
    {
        $this->authorizeDashboard($request);

        return MetricResource::make($action->handle());
    }

    /**
     * Open / overdue / due-today task counts per assigned employee.
     */
    public function workload(Request $request, BuildEmployeeWorkload $action): AnonymousResourceCollection
    {
        $this->authorizeDashboard($request);

        return MetricResource::collection($action->handle());
    }

    /**
     * Project and task rollups per department.
     */
    public function departmentPerformance(Request $request, BuildDepartmentPerformance $action): AnonymousResourceCollection
    {
        $this->authorizeDashboard($request);

        return MetricResource::collection($action->handle());
    }

    private function authorizeDashboard(Request $request): void
    {
        abort_unless((bool) $request->user()?->can('operations.view_dashboard'), 403);
    }
}
