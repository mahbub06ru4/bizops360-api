<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Models\User;
use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Actions\Concerns\ResolvesWorkSchedule;
use App\Modules\HR\Data\RecordAttendanceData;
use App\Modules\HR\Models\Attendance;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;

/**
 * Creates or overrides an employee's attendance record for a day (manager entry).
 * When both punches are given, lateness / early-leave / worked minutes are
 * recomputed from the work schedule.
 */
class RecordAttendance
{
    use InteractsWithTenant;
    use ResolvesWorkSchedule;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RecordAttendanceData $data, User $recorder): Attendance
    {
        $this->assertReferenceOwned($data->employeeId, Employee::class);

        $checkIn = $data->checkInAt !== null ? Carbon::parse($data->checkInAt) : null;
        $checkOut = $data->checkOutAt !== null ? Carbon::parse($data->checkOutAt) : null;

        $attendance = Attendance::query()->firstOrNew([
            'employee_id' => $data->employeeId,
            'date' => $data->date,
        ]);

        $attendance->tenant_id = $this->currentTenantId();
        $attendance->status = $data->status;
        $attendance->check_in_at = $checkIn;
        $attendance->check_out_at = $checkOut;
        $attendance->note = $data->note;
        $attendance->recorded_by = $recorder->getKey();

        $schedule = $this->workSchedule();
        $attendance->is_late = $checkIn !== null && $schedule->isLate($checkIn);
        $attendance->is_early_leave = $checkOut !== null && $schedule->isEarlyLeave($checkOut);
        $attendance->worked_minutes = ($checkIn !== null && $checkOut !== null)
            ? $schedule->workedMinutes($checkIn, $checkOut)
            : null;

        $attendance->save();

        return $attendance->load(['employee', 'recorder']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
