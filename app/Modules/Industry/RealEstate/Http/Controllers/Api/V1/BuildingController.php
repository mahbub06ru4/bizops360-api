<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Industry\RealEstate\Actions\AddBuilding;
use App\Modules\Industry\RealEstate\Actions\DeleteBuilding;
use App\Modules\Industry\RealEstate\Actions\UpdateBuilding;
use App\Modules\Industry\RealEstate\Http\Requests\BuildingRequest;
use App\Modules\Industry\RealEstate\Http\Resources\BuildingResource;
use App\Modules\Industry\RealEstate\Models\Building;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BuildingController extends Controller
{
    public function store(BuildingRequest $request, RealEstateProject $project, AddBuilding $action): JsonResponse
    {
        $this->authorize('update', $project);

        return BuildingResource::make($action->handle($project, $request->toData()))
            ->response()->setStatusCode(201);
    }

    public function show(Building $building): BuildingResource
    {
        $this->authorize('view', $building);

        return BuildingResource::make($building->load('units.media', 'units.prices'));
    }

    public function update(BuildingRequest $request, Building $building, UpdateBuilding $action): BuildingResource
    {
        $this->authorize('update', $building);

        return BuildingResource::make($action->handle($building, $request->toData()));
    }

    public function destroy(Building $building, DeleteBuilding $action): Response
    {
        $this->authorize('delete', $building);

        $action->handle($building);

        return response()->noContent();
    }
}
