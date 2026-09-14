<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Actions\CancelSiteVisit;
use App\Modules\Industry\RealEstate\Actions\CompleteSiteVisit;
use App\Modules\Industry\RealEstate\Actions\ScheduleSiteVisit;
use App\Modules\Industry\RealEstate\Http\Requests\CompleteSiteVisitRequest;
use App\Modules\Industry\RealEstate\Http\Requests\SiteVisitRequest;
use App\Modules\Industry\RealEstate\Http\Resources\SiteVisitResource;
use App\Modules\Industry\RealEstate\Models\SiteVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SiteVisitController extends Controller
{
    /** Eager-loaded on every response so {@see SiteVisitResource} can embed lead/unit/project display labels. */
    private const DISPLAY_RELATIONS = ['lead', 'unit.building.project', 'project'];

    /**
     * Tenant-wide site-visit queue (across every lead) — the pipeline
     * overview view. Scoped automatically by {@see \App\Modules\Tenant\Models\Concerns\BelongsToTenant}'s
     * global scope, same as every other tenant-owned index in this module.
     */
    public function all(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', SiteVisit::class);

        $query = SiteVisit::query()->with(self::DISPLAY_RELATIONS)->latest('scheduled_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return SiteVisitResource::collection($query->paginate());
    }

    public function index(Lead $lead): AnonymousResourceCollection
    {
        $this->authorize('viewAny', SiteVisit::class);

        return SiteVisitResource::collection(
            SiteVisit::query()->with(self::DISPLAY_RELATIONS)->where('lead_id', $lead->getKey())->latest('scheduled_at')->get()
        );
    }

    public function store(SiteVisitRequest $request, Lead $lead, ScheduleSiteVisit $action): JsonResponse
    {
        $this->authorize('create', SiteVisit::class);

        $visit = $action->handle($lead, $request->toData());

        return SiteVisitResource::make($visit->load(self::DISPLAY_RELATIONS))
            ->response()->setStatusCode(201);
    }

    public function show(SiteVisit $visit): SiteVisitResource
    {
        $this->authorize('view', $visit);

        return SiteVisitResource::make($visit->load(self::DISPLAY_RELATIONS));
    }

    public function complete(CompleteSiteVisitRequest $request, SiteVisit $visit, CompleteSiteVisit $action): SiteVisitResource
    {
        $this->authorize('update', $visit);

        return SiteVisitResource::make($action->handle($visit, $request->toData())->load(self::DISPLAY_RELATIONS));
    }

    public function cancel(SiteVisit $visit, CancelSiteVisit $action): SiteVisitResource
    {
        $this->authorize('update', $visit);

        return SiteVisitResource::make($action->handle($visit)->load(self::DISPLAY_RELATIONS));
    }
}
