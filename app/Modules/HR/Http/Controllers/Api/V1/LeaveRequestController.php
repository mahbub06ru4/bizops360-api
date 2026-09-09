<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\HR\Actions\ApproveLeaveRequest;
use App\Modules\HR\Actions\CancelLeaveRequest;
use App\Modules\HR\Actions\RejectLeaveRequest;
use App\Modules\HR\Actions\RequestLeave;
use App\Modules\HR\Http\Requests\LeaveDecisionRequest;
use App\Modules\HR\Http\Requests\LeaveRequestRequest;
use App\Modules\HR\Http\Resources\LeaveRequestResource;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\Organization\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeaveRequestController extends Controller
{
    /**
     * List leave requests. Users without `leave.approve` only see their own.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', LeaveRequest::class);

        /** @var User $user */
        $user = $request->user();

        $query = LeaveRequest::query()->with(['employee', 'leaveType'])->latest();

        if (! $user->can('leave.approve')) {
            $employeeId = Employee::query()->where('user_id', $user->getKey())->value('id');
            $query->where('employee_id', $employeeId);
        }

        return LeaveRequestResource::collection($query->paginate());
    }

    public function store(LeaveRequestRequest $request, RequestLeave $action): JsonResponse
    {
        $this->authorize('create', LeaveRequest::class);

        /** @var User $user */
        $user = $request->user();

        return LeaveRequestResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(LeaveRequest $leaveRequest): LeaveRequestResource
    {
        $this->authorize('view', $leaveRequest);

        return LeaveRequestResource::make($leaveRequest->load(['employee', 'leaveType', 'decider']));
    }

    public function approve(LeaveDecisionRequest $request, LeaveRequest $leaveRequest, ApproveLeaveRequest $action): LeaveRequestResource
    {
        $this->authorize('decide', $leaveRequest);

        /** @var User $user */
        $user = $request->user();

        return LeaveRequestResource::make($action->handle($leaveRequest, $user, $request->toData()));
    }

    public function reject(LeaveDecisionRequest $request, LeaveRequest $leaveRequest, RejectLeaveRequest $action): LeaveRequestResource
    {
        $this->authorize('decide', $leaveRequest);

        /** @var User $user */
        $user = $request->user();

        return LeaveRequestResource::make($action->handle($leaveRequest, $user, $request->toData()));
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest, CancelLeaveRequest $action): LeaveRequestResource
    {
        $this->authorize('cancel', $leaveRequest);

        /** @var User $user */
        $user = $request->user();

        return LeaveRequestResource::make($action->handle($leaveRequest, $user));
    }
}
