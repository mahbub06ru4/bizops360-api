<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Actions\CreatePropertyRequirement;
use App\Modules\Industry\RealEstate\Actions\MatchRequirementToUnits;
use App\Modules\Industry\RealEstate\Http\Requests\PropertyRequirementRequest;
use App\Modules\Industry\RealEstate\Http\Resources\PropertyMatchResource;
use App\Modules\Industry\RealEstate\Http\Resources\PropertyRequirementResource;
use App\Modules\Industry\RealEstate\Models\PropertyRequirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyRequirementController extends Controller
{
    public function index(Lead $lead): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PropertyRequirement::class);

        return PropertyRequirementResource::collection(
            PropertyRequirement::query()->with('lead')->where('lead_id', $lead->getKey())->latest()->get()
        );
    }

    public function store(PropertyRequirementRequest $request, Lead $lead, CreatePropertyRequirement $action): JsonResponse
    {
        $this->authorize('create', PropertyRequirement::class);

        $requirement = $action->handle($lead, $request->toData());

        return PropertyRequirementResource::make($requirement->load('lead'))
            ->response()->setStatusCode(201);
    }

    public function show(PropertyRequirement $requirement): PropertyRequirementResource
    {
        $this->authorize('view', $requirement);

        return PropertyRequirementResource::make($requirement->load(['lead', 'matches.unit.building.project']));
    }

    public function match(PropertyRequirement $requirement, MatchRequirementToUnits $action): AnonymousResourceCollection
    {
        $this->authorize('view', $requirement);

        return PropertyMatchResource::collection($action->handle($requirement));
    }
}
