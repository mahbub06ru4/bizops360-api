<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Industry\RealEstate\Actions\CreateInstallmentPlan;
use App\Modules\Industry\RealEstate\Http\Requests\InstallmentPlanRequest;
use App\Modules\Industry\RealEstate\Http\Resources\InstallmentPlanResource;
use App\Modules\Industry\RealEstate\Models\InstallmentPlan;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use Illuminate\Http\JsonResponse;

class InstallmentPlanController extends Controller
{
    public function store(InstallmentPlanRequest $request, RealEstateBooking $booking, CreateInstallmentPlan $action): JsonResponse
    {
        $this->authorize('view', $booking);
        $this->authorize('create', InstallmentPlan::class);

        return InstallmentPlanResource::make($action->handle($booking, $request->toData()))
            ->response()->setStatusCode(201);
    }

    public function show(InstallmentPlan $installmentPlan): InstallmentPlanResource
    {
        $this->authorize('view', $installmentPlan);

        return InstallmentPlanResource::make($installmentPlan->load('installments'));
    }
}
