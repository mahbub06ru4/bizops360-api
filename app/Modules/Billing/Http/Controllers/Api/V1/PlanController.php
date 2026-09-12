<?php

declare(strict_types=1);

namespace App\Modules\Billing\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Http\Resources\PlanResource;
use App\Modules\Billing\Models\Plan;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlanController extends Controller
{
    /**
     * The public plan catalogue — any authenticated user may see prices.
     */
    public function index(): AnonymousResourceCollection
    {
        return PlanResource::collection(
            Plan::query()->where('is_active', true)->orderBy('price_amount')->get(),
        );
    }
}
