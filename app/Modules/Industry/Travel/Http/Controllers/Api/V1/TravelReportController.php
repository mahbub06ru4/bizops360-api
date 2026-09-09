<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Industry\Travel\Actions\BuildTravelDeskOverview;
use App\Modules\Industry\Travel\Http\Resources\TravelMetricResource;
use Illuminate\Http\Request;

/**
 * Read-only travel-desk dashboard for the current tenant. Requires
 * `travel.view_dashboard`.
 */
class TravelReportController extends Controller
{
    public function overview(Request $request, BuildTravelDeskOverview $action): TravelMetricResource
    {
        abort_unless((bool) $request->user()?->can('travel.view_dashboard'), 403);

        return TravelMetricResource::make($action->handle());
    }
}
