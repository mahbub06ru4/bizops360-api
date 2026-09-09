<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\HR\Actions\CreateHoliday;
use App\Modules\HR\Actions\DeleteHoliday;
use App\Modules\HR\Actions\UpdateHoliday;
use App\Modules\HR\Http\Requests\HolidayRequest;
use App\Modules\HR\Http\Resources\HolidayResource;
use App\Modules\HR\Models\Holiday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class HolidayController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Holiday::class);

        return HolidayResource::collection(
            Holiday::query()->orderBy('date')->paginate(),
        );
    }

    public function store(HolidayRequest $request, CreateHoliday $action): JsonResponse
    {
        $this->authorize('create', Holiday::class);

        return HolidayResource::make($action->handle($request->toData()))
            ->response()->setStatusCode(201);
    }

    public function show(Holiday $holiday): HolidayResource
    {
        $this->authorize('view', $holiday);

        return HolidayResource::make($holiday);
    }

    public function update(HolidayRequest $request, Holiday $holiday, UpdateHoliday $action): HolidayResource
    {
        $this->authorize('update', $holiday);

        return HolidayResource::make($action->handle($holiday, $request->toData()));
    }

    public function destroy(Holiday $holiday, DeleteHoliday $action): Response
    {
        $this->authorize('delete', $holiday);

        $action->handle($holiday);

        return response()->noContent();
    }
}
