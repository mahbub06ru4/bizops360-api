<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\HR\Actions\CreateLeaveType;
use App\Modules\HR\Actions\DeleteLeaveType;
use App\Modules\HR\Actions\UpdateLeaveType;
use App\Modules\HR\Http\Requests\LeaveTypeRequest;
use App\Modules\HR\Http\Resources\LeaveTypeResource;
use App\Modules\HR\Models\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LeaveTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', LeaveType::class);

        return LeaveTypeResource::collection(
            LeaveType::query()->orderBy('name')->paginate(),
        );
    }

    public function store(LeaveTypeRequest $request, CreateLeaveType $action): JsonResponse
    {
        $this->authorize('create', LeaveType::class);

        return LeaveTypeResource::make($action->handle($request->toData()))
            ->response()->setStatusCode(201);
    }

    public function show(LeaveType $leaveType): LeaveTypeResource
    {
        $this->authorize('view', $leaveType);

        return LeaveTypeResource::make($leaveType);
    }

    public function update(LeaveTypeRequest $request, LeaveType $leaveType, UpdateLeaveType $action): LeaveTypeResource
    {
        $this->authorize('update', $leaveType);

        return LeaveTypeResource::make($action->handle($leaveType, $request->toData()));
    }

    public function destroy(LeaveType $leaveType, DeleteLeaveType $action): Response
    {
        $this->authorize('delete', $leaveType);

        $action->handle($leaveType);

        return response()->noContent();
    }
}
