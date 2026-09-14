<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Industry\RealEstate\Actions\AddAmenity;
use App\Modules\Industry\RealEstate\Actions\DeleteAmenity;
use App\Modules\Industry\RealEstate\Http\Requests\AmenityRequest;
use App\Modules\Industry\RealEstate\Http\Resources\AmenityResource;
use App\Modules\Industry\RealEstate\Models\Amenity;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AmenityController extends Controller
{
    public function store(AmenityRequest $request, RealEstateProject $project, AddAmenity $action): JsonResponse
    {
        $this->authorize('update', $project);

        return AmenityResource::make($action->handle($project, $request->toData()))
            ->response()->setStatusCode(201);
    }

    public function destroy(Amenity $amenity, DeleteAmenity $action): Response
    {
        $this->authorize('delete', $amenity);

        $action->handle($amenity);

        return response()->noContent();
    }
}
