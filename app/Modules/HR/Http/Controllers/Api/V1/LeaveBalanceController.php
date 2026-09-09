<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\HR\Actions\SetLeaveBalance;
use App\Modules\HR\Http\Requests\LeaveBalanceRequest;
use App\Modules\HR\Http\Resources\LeaveBalanceResource;
use App\Modules\HR\Models\LeaveBalance;
use App\Modules\Organization\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeaveBalanceController extends Controller
{
    /**
     * List leave balances. Users without `leave.manage_balance` only see their own.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', LeaveBalance::class);

        /** @var User $user */
        $user = $request->user();

        $query = LeaveBalance::query()->with(['employee', 'leaveType'])->orderByDesc('year');

        if (! $user->can('leave.manage_balance')) {
            $employeeId = Employee::query()->where('user_id', $user->getKey())->value('id');
            $query->where('employee_id', $employeeId);
        }

        return LeaveBalanceResource::collection($query->paginate());
    }

    /**
     * Create or update an employee's entitlement for a leave type and year.
     */
    public function upsert(LeaveBalanceRequest $request, SetLeaveBalance $action): LeaveBalanceResource
    {
        $this->authorize('manage', LeaveBalance::class);

        return LeaveBalanceResource::make($action->handle($request->toData()));
    }
}
