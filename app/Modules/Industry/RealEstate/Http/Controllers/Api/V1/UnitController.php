<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Industry\RealEstate\Actions\AddUnit;
use App\Modules\Industry\RealEstate\Actions\AddUnitMedia;
use App\Modules\Industry\RealEstate\Actions\DeleteUnit;
use App\Modules\Industry\RealEstate\Actions\SetUnitPrice;
use App\Modules\Industry\RealEstate\Actions\UpdateUnit;
use App\Modules\Industry\RealEstate\Http\Requests\UnitMediaRequest;
use App\Modules\Industry\RealEstate\Http\Requests\UnitPriceRequest;
use App\Modules\Industry\RealEstate\Http\Requests\UnitRequest;
use App\Modules\Industry\RealEstate\Http\Resources\UnitMediaResource;
use App\Modules\Industry\RealEstate\Http\Resources\UnitPriceResource;
use App\Modules\Industry\RealEstate\Http\Resources\UnitResource;
use App\Modules\Industry\RealEstate\Models\Building;
use App\Modules\Industry\RealEstate\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

class UnitController extends Controller
{
    public function store(UnitRequest $request, Building $building, AddUnit $action): JsonResponse
    {
        $this->authorize('update', $building);

        return UnitResource::make($action->handle($building, $request->toData()))
            ->response()->setStatusCode(201);
    }

    public function show(Unit $unit): UnitResource
    {
        $this->authorize('view', $unit);

        return UnitResource::make($unit->load('media', 'prices'));
    }

    public function update(UnitRequest $request, Unit $unit, UpdateUnit $action): UnitResource
    {
        $this->authorize('update', $unit);

        return UnitResource::make($action->handle($unit, $request->toData()));
    }

    public function destroy(Unit $unit, DeleteUnit $action): Response
    {
        $this->authorize('delete', $unit);

        $action->handle($unit);

        return response()->noContent();
    }

    public function addMedia(UnitMediaRequest $request, Unit $unit, AddUnitMedia $action): JsonResponse
    {
        $this->authorize('update', $unit);

        /** @var UploadedFile $file */
        $file = $request->file('file');

        return UnitMediaResource::make($action->handle($unit, $request->toData(), $file))
            ->response()->setStatusCode(201);
    }

    public function addPrice(UnitPriceRequest $request, Unit $unit, SetUnitPrice $action): JsonResponse
    {
        $this->authorize('update', $unit);

        return UnitPriceResource::make($action->handle($unit, $request->toData()))
            ->response()->setStatusCode(201);
    }
}
