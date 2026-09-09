<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Actions\BuildCrmOverview;
use App\Modules\CRM\Actions\BuildCrmSalesPerformance;
use App\Modules\CRM\Http\Resources\CrmMetricResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only CRM dashboards for the current tenant. Requires `crm.view_dashboard`.
 */
class CrmReportController extends Controller
{
    public function overview(Request $request, BuildCrmOverview $action): CrmMetricResource
    {
        $this->authorizeDashboard($request);

        return CrmMetricResource::make($action->handle());
    }

    public function salesPerformance(Request $request, BuildCrmSalesPerformance $action): AnonymousResourceCollection
    {
        $this->authorizeDashboard($request);

        return CrmMetricResource::collection($action->handle());
    }

    private function authorizeDashboard(Request $request): void
    {
        abort_unless((bool) $request->user()?->can('crm.view_dashboard'), 403);
    }
}
