<?php

declare(strict_types=1);

namespace App\Modules\Billing\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Actions\BuildPlatformAnalytics;
use Illuminate\Http\JsonResponse;

class PlatformAnalyticsController extends Controller
{
    public function index(BuildPlatformAnalytics $action): JsonResponse
    {
        return response()->json(['data' => $action->handle()]);
    }
}
