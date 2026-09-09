<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\HR\Actions\CheckIn;
use App\Modules\HR\Actions\CheckOut;
use App\Modules\HR\Actions\RecordAttendance;
use App\Modules\HR\Http\Requests\RecordAttendanceRequest;
use App\Modules\HR\Http\Resources\AttendanceResource;
use App\Modules\HR\Http\Resources\AttendanceSummaryResource;
use App\Modules\HR\Models\Attendance;
use App\Modules\Organization\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    /**
     * List attendance records. Users without `attendance.view_all` see only their own.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Attendance::class);

        /** @var User $user */
        $user = $request->user();

        $query = Attendance::query()->with('employee')->orderByDesc('date');

        if ($user->can('attendance.view_all')) {
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->integer('employee_id'));
            }
        } else {
            $query->where('employee_id', $this->ownEmployeeId($user));
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->date('to'));
        }

        return AttendanceResource::collection($query->paginate());
    }

    /**
     * Record or override an employee's attendance for a day.
     */
    public function store(RecordAttendanceRequest $request, RecordAttendance $action): JsonResponse
    {
        $this->authorize('record', Attendance::class);

        /** @var User $user */
        $user = $request->user();

        return AttendanceResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Attendance $attendance): AttendanceResource
    {
        $this->authorize('view', $attendance);

        return AttendanceResource::make($attendance->load('employee'));
    }

    /**
     * Check the acting user in for today.
     */
    public function checkIn(Request $request, CheckIn $action): JsonResponse
    {
        $this->authorize('checkIn', Attendance::class);

        /** @var User $user */
        $user = $request->user();

        return AttendanceResource::make($action->handle($user))->response()->setStatusCode(201);
    }

    /**
     * Check the acting user out for today.
     */
    public function checkOut(Request $request, CheckOut $action): AttendanceResource
    {
        $this->authorize('checkIn', Attendance::class);

        /** @var User $user */
        $user = $request->user();

        return AttendanceResource::make($action->handle($user));
    }

    /**
     * Monthly attendance summary for one employee.
     */
    public function summary(Request $request): AttendanceSummaryResource
    {
        $this->authorize('viewAny', Attendance::class);

        /** @var User $user */
        $user = $request->user();

        $employeeId = $user->can('attendance.view_all') && $request->filled('employee_id')
            ? $request->integer('employee_id')
            : $this->ownEmployeeId($user);

        $month = Carbon::parse($request->string('month', Carbon::now()->format('Y-m')).'-01')->startOfMonth();

        $rows = Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->get();

        $byStatus = $rows->countBy(fn (Attendance $row): string => $row->status->value);

        return AttendanceSummaryResource::make([
            'month' => $month->format('Y-m'),
            'employee_id' => $employeeId,
            'days_recorded' => $rows->count(),
            'present' => $byStatus->get('present', 0),
            'late' => $byStatus->get('late', 0),
            'absent' => $byStatus->get('absent', 0),
            'half_day' => $byStatus->get('half_day', 0),
            'on_leave' => $byStatus->get('on_leave', 0),
            'holiday' => $byStatus->get('holiday', 0),
            'late_count' => $rows->where('is_late', true)->count(),
            'early_leave_count' => $rows->where('is_early_leave', true)->count(),
            'worked_minutes' => (int) $rows->sum('worked_minutes'),
        ]);
    }

    private function ownEmployeeId(User $user): int
    {
        $id = Employee::query()->where('user_id', $user->getKey())->value('id');

        if ($id === null) {
            throw ValidationException::withMessages([
                'employee' => 'Your account is not linked to an employee record.',
            ]);
        }

        return (int) $id;
    }
}
